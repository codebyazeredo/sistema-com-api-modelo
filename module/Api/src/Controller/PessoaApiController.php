<?php

declare(strict_types=1);

namespace Api\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Pessoa\Service\PessoaService;
use RuntimeException;

/**
 * Exemplo de endpoint da API reaproveitando o Service do módulo Pessoa (o
 * mesmo cadastro do dashboard web, em Pessoa\Controller\PessoaController) —
 * prova o padrão "um domínio, duas portas de entrada": o que é cadastrado
 * aqui aparece no dashboard, e vice-versa. Nome de classe deliberadamente
 * diferente do controller web (mesmo em namespaces distintos) pra manter as
 * responsabilidades — e o que cada um trava em termos de acesso/segurança —
 * bem separadas.
 *
 * Este controller NUNCA importa/toca `Pessoa\Entity\Pessoa` — só trabalha
 * com os arrays que `PessoaService` devolve. Ver o comentário no topo de
 * `PessoaService` pra entender por quê.
 *
 * `create`/`update` aceitam multipart/form-data (nome, documento, email,
 * telefone, foto) — é assim que o app deve mandar quando a foto vier da
 * câmera: um multipart normal, igual um upload de formulário web, NÃO JSON
 * (diferente de /auth/login, que é JSON puro).
 */
final class PessoaApiController extends AbstractActionController
{
    public function __construct(private readonly PessoaService $pessoaService)
    {
    }

    public function indexAction(): JsonModel
    {
        return new JsonModel(['pessoas' => $this->pessoaService->listarTodos()]);
    }

    public function showAction(): JsonModel
    {
        $pessoa = $this->pessoaService->encontrar((int) $this->params()->fromRoute('id', 0));

        if ($pessoa === null) {
            $this->getResponse()->setStatusCode(404);

            return new JsonModel(['message' => 'Pessoa não encontrada.']);
        }

        return new JsonModel(['pessoa' => $pessoa]);
    }

    public function createAction(): JsonModel
    {
        if (! $this->getRequest()->isPost()) {
            return $this->erro(405, 'Método não permitido.');
        }

        $dados = $this->params()->fromPost();
        if (empty($dados['nome']) || empty($dados['documento'])) {
            return $this->erro(422, 'Informe nome e documento.');
        }

        $arquivo = $this->params()->fromFiles('foto');

        try {
            $pessoa = $this->pessoaService->criar($dados, is_array($arquivo) ? $arquivo : null);
        } catch (RuntimeException $e) {
            return $this->erro(422, $e->getMessage());
        }

        $this->getResponse()->setStatusCode(201);

        return new JsonModel(['pessoa' => $pessoa]);
    }

    public function updateAction(): JsonModel
    {
        if (! $this->getRequest()->isPost()) {
            return $this->erro(405, 'Método não permitido.');
        }

        $dados = $this->params()->fromPost();
        if (empty($dados['nome']) || empty($dados['documento'])) {
            return $this->erro(422, 'Informe nome e documento.');
        }

        $arquivo = $this->params()->fromFiles('foto');
        $id = (int) $this->params()->fromRoute('id', 0);

        try {
            $pessoa = $this->pessoaService->atualizar($id, $dados, is_array($arquivo) ? $arquivo : null);
        } catch (RuntimeException $e) {
            return $this->erro(422, $e->getMessage());
        }

        if ($pessoa === null) {
            return $this->erro(404, 'Pessoa não encontrada.');
        }

        return new JsonModel(['pessoa' => $pessoa]);
    }

    public function deleteAction(): JsonModel
    {
        if (! $this->getRequest()->isPost()) {
            return $this->erro(405, 'Método não permitido.');
        }

        $this->pessoaService->remover((int) $this->params()->fromRoute('id', 0));

        return new JsonModel(['message' => 'Removido.']);
    }

    private function erro(int $status, string $mensagem): JsonModel
    {
        $this->getResponse()->setStatusCode($status);

        return new JsonModel(['message' => $mensagem]);
    }
}
