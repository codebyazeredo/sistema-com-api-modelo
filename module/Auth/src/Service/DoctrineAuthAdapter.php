<?php

declare(strict_types=1);

namespace Auth\Service;

use Application\Service\PasswordHasher;
use Auth\Entity\Usuario;
use Auth\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Authentication\Adapter\AdapterInterface;
use Laminas\Authentication\Result;

/**
 * Adapter genérico de autenticação: login/e-mail + senha contra a tabela
 * `usuario`, verificação via Argon2id (Application\Service\PasswordHasher).
 * Usado tanto pelo login web (sessão) quanto pelo login da API (JWT) —
 * cada um decide o que fazer com o Result (sessão vs. emitir token).
 */
final class DoctrineAuthAdapter implements AdapterInterface
{
    private string $login = '';
    private string $senha = '';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PasswordHasher $hasher,
    ) {
    }

    public function setCredentials(string $login, string $senha): void
    {
        $this->login = trim($login);
        $this->senha = $senha;
    }

    public function authenticate(): Result
    {
        // Mensagem sempre genérica: nunca revelar se o usuário existe ou não.
        $mensagemErro = 'Credenciais inválidas.';

        /** @var UsuarioRepository $repositorio */
        $repositorio = $this->entityManager->getRepository(Usuario::class);
        $usuario = $repositorio->encontrarAtivoPorLoginOuEmail($this->login);

        if ($usuario === null) {
            return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, null, [$mensagemErro]);
        }

        if (! $this->hasher->verify($this->senha, $usuario->getSenha())) {
            return new Result(Result::FAILURE_CREDENTIAL_INVALID, null, [$mensagemErro]);
        }

        if ($this->hasher->needsRehash($usuario->getSenha())) {
            $usuario->setSenha($this->hasher->hash($this->senha));
            $this->entityManager->flush();
        }

        return new Result(Result::SUCCESS, $usuario->toArray(), ['Autenticado com sucesso.']);
    }
}
