<?php

declare(strict_types=1);

namespace Pessoa\Service;

use Doctrine\ORM\EntityManagerInterface;
use Pessoa\Entity\Pessoa;
use Pessoa\Repository\PessoaRepository;
use RuntimeException;

/**
 * Regra de negócio do cadastro de pessoa — chamada tanto pelo Controller web
 * (module/Pessoa) quanto pelo Controller da API (module/Api), pra não
 * duplicar validação/gravação de foto em dois lugares. Isso é o padrão a
 * seguir sempre que houver lógica além de um CRUD trivial (ver módulo
 * `Modelo`, que por ser só um exemplo mínimo não tem essa camada).
 *
 * IMPORTANTE: nenhum método público devolve a Entity `Pessoa` — só `array`
 * (via `Pessoa::toArray()`, chamado exclusivamente aqui dentro). Os
 * controllers NUNCA devem receber/manipular a Entity: um `json_encode` ou
 * cast `(array)` descuidado em cima dela no futuro vazaria propriedades
 * internas do Doctrine (proxies, campos nunca pensados pra sair pra fora).
 * Por isso os métodos recebem `int $id` em vez de `Pessoa $pessoa`.
 */
final class PessoaService
{
    private const FOTO_MAX_BYTES = 5 * 1024 * 1024; // 5MB

    /** @var array<string, string> */
    private const FOTO_MIME_EXTENSAO = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly string $uploadsDir,
    ) {
    }

    /** @return list<array{id:int|null,nome:string,documento:string,email:string|null,telefone:string|null,foto_url:string|null}> */
    public function listarTodos(): array
    {
        /** @var PessoaRepository $repositorio */
        $repositorio = $this->entityManager->getRepository(Pessoa::class);

        return array_map(
            static fn (Pessoa $pessoa): array => $pessoa->toArray(),
            $repositorio->listarTodos(),
        );
    }

    /** @return array{id:int|null,nome:string,documento:string,email:string|null,telefone:string|null,foto_url:string|null}|null */
    public function encontrar(int $id): ?array
    {
        return $this->entityManager->find(Pessoa::class, $id)?->toArray();
    }

    /**
     * @param array{nome?: mixed, documento?: mixed, email?: mixed, telefone?: mixed} $dados
     * @param array{name: string, type: string, tmp_name: string, error: int, size: int}|null $arquivoFoto
     * @return array{id:int|null,nome:string,documento:string,email:string|null,telefone:string|null,foto_url:string|null}
     */
    public function criar(array $dados, ?array $arquivoFoto = null): array
    {
        $pessoa = new Pessoa((string) ($dados['nome'] ?? ''), (string) ($dados['documento'] ?? ''));
        $pessoa->setEmail($this->stringOuNulo($dados['email'] ?? null));
        $pessoa->setTelefone($this->stringOuNulo($dados['telefone'] ?? null));

        if ($this->arquivoEnviado($arquivoFoto)) {
            $pessoa->setFotoPath($this->salvarFoto($arquivoFoto));
        }

        $this->entityManager->persist($pessoa);
        $this->entityManager->flush();

        return $pessoa->toArray();
    }

    /**
     * @param array{nome?: mixed, documento?: mixed, email?: mixed, telefone?: mixed} $dados
     * @param array{name: string, type: string, tmp_name: string, error: int, size: int}|null $arquivoFoto
     * @return array{id:int|null,nome:string,documento:string,email:string|null,telefone:string|null,foto_url:string|null}|null
     *         null quando `$id` não existe.
     */
    public function atualizar(int $id, array $dados, ?array $arquivoFoto = null): ?array
    {
        $pessoa = $this->entityManager->find(Pessoa::class, $id);
        if ($pessoa === null) {
            return null;
        }

        $pessoa->setNome((string) ($dados['nome'] ?? $pessoa->getNome()));
        $pessoa->setDocumento((string) ($dados['documento'] ?? $pessoa->getDocumento()));
        $pessoa->setEmail($this->stringOuNulo($dados['email'] ?? null));
        $pessoa->setTelefone($this->stringOuNulo($dados['telefone'] ?? null));

        if ($this->arquivoEnviado($arquivoFoto)) {
            $fotoAntiga = $pessoa->getFotoPath();
            $pessoa->setFotoPath($this->salvarFoto($arquivoFoto));
            if ($fotoAntiga !== null) {
                $this->removerArquivoFoto($fotoAntiga);
            }
        }

        $this->entityManager->flush();

        return $pessoa->toArray();
    }

    /** @return bool false quando `$id` não existe (nada a remover). */
    public function remover(int $id): bool
    {
        $pessoa = $this->entityManager->find(Pessoa::class, $id);
        if ($pessoa === null) {
            return false;
        }

        if ($pessoa->getFotoPath() !== null) {
            $this->removerArquivoFoto($pessoa->getFotoPath());
        }

        $this->entityManager->remove($pessoa);
        $this->entityManager->flush();

        return true;
    }

    /** @param mixed $arquivoFoto */
    private function arquivoEnviado($arquivoFoto): bool
    {
        return is_array($arquivoFoto) && ($arquivoFoto['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    }

    private function stringOuNulo(mixed $valor): ?string
    {
        $valor = is_string($valor) ? trim($valor) : '';

        return $valor === '' ? null : $valor;
    }

    /**
     * Valida e move o upload para dentro de public/uploads/pessoas/ com nome
     * ALEATÓRIO (nunca o nome original do arquivo) e extensão derivada do
     * MIME real do CONTEÚDO (nunca do que o cliente informou no header) —
     * evita path traversal, sobrescrita de arquivo e upload de script
     * disfarçado de imagem.
     *
     * @param array{name: string, type: string, tmp_name: string, error: int, size: int} $arquivo
     */
    private function salvarFoto(array $arquivo): string
    {
        if ($arquivo['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Falha no upload da foto.');
        }

        if ($arquivo['size'] > self::FOTO_MAX_BYTES) {
            throw new RuntimeException('Foto maior que o limite de 5MB.');
        }

        if (! is_uploaded_file($arquivo['tmp_name'])) {
            throw new RuntimeException('Upload inválido.');
        }

        $mime = (string) mime_content_type($arquivo['tmp_name']);
        $extensao = self::FOTO_MIME_EXTENSAO[$mime] ?? null;

        if ($extensao === null) {
            throw new RuntimeException('Formato de imagem não suportado (use JPEG, PNG ou WebP).');
        }

        if (! is_dir($this->uploadsDir) && ! mkdir($this->uploadsDir, 0755, true) && ! is_dir($this->uploadsDir)) {
            throw new RuntimeException('Não foi possível preparar o diretório de uploads.');
        }

        $nomeArquivo = bin2hex(random_bytes(16)) . '.' . $extensao;
        $destino = $this->uploadsDir . '/' . $nomeArquivo;

        if (! move_uploaded_file($arquivo['tmp_name'], $destino)) {
            throw new RuntimeException('Não foi possível salvar a foto.');
        }

        return $nomeArquivo;
    }

    private function removerArquivoFoto(string $nomeArquivo): void
    {
        $caminho = $this->uploadsDir . '/' . basename($nomeArquivo);
        if (is_file($caminho)) {
            unlink($caminho);
        }
    }
}
