<?php

declare(strict_types=1);

namespace Pessoa\Controller;

use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Validator\Csrf as CsrfValidator;
use Laminas\View\Model\ViewModel;
use Pessoa\Form\PessoaForm;
use Pessoa\Service\PessoaService;
use RuntimeException;

/**
 * CRUD de exemplo COMPLETO (com upload de foto) — mostra o padrão a seguir
 * pra qualquer cadastro que também precise aparecer no app: a regra de
 * negócio fica em Pessoa\Service\PessoaService, reaproveitada por este
 * controller (dashboard) e por Api\Controller\PessoaController (app).
 */
final class PessoaController extends AbstractActionController
{
    public function __construct(private readonly PessoaService $pessoaService)
    {
    }

    public function indexAction(): ViewModel
    {
        return new ViewModel(['itens' => $this->pessoaService->listarTodos()]);
    }

    public function addAction(): ViewModel|Response
    {
        $form = new PessoaForm();
        $erro = null;

        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $arquivo = $this->params()->fromFiles('foto');

                try {
                    $this->pessoaService->criar($form->getData(), is_array($arquivo) ? $arquivo : null);

                    return $this->redirect()->toRoute('pessoa');
                } catch (RuntimeException $e) {
                    $erro = $e->getMessage();
                }
            }
        }

        return new ViewModel(['form' => $form, 'modo' => 'add', 'erro' => $erro]);
    }

    public function editAction(): ViewModel|Response
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        $pessoa = $this->pessoaService->encontrar($id);

        if ($pessoa === null) {
            return $this->redirect()->toRoute('pessoa');
        }

        $form = new PessoaForm();
        $form->setData([
            'nome' => $pessoa['nome'],
            'documento' => $pessoa['documento'],
            'email' => $pessoa['email'],
            'telefone' => $pessoa['telefone'],
        ]);

        $erro = null;

        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $arquivo = $this->params()->fromFiles('foto');

                try {
                    $atualizado = $this->pessoaService->atualizar($id, $form->getData(), is_array($arquivo) ? $arquivo : null);

                    if ($atualizado !== null) {
                        return $this->redirect()->toRoute('pessoa');
                    }
                } catch (RuntimeException $e) {
                    $erro = $e->getMessage();
                }
            }
        }

        return new ViewModel(['form' => $form, 'modo' => 'edit', 'item' => $pessoa, 'erro' => $erro]);
    }

    public function deleteAction(): Response
    {
        if ($this->getRequest()->isPost()) {
            $csrfValidator = new CsrfValidator(['name' => 'delete_csrf']);
            $token = (string) $this->params()->fromPost('delete_csrf', '');

            if ($csrfValidator->isValid($token)) {
                $this->pessoaService->remover((int) $this->params()->fromRoute('id', 0));
            }
        }

        return $this->redirect()->toRoute('pessoa');
    }
}
