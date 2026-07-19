<?php

declare(strict_types=1);

namespace Modelo\Controller;

use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Validator\Csrf as CsrfValidator;
use Laminas\View\Model\ViewModel;
use Modelo\Form\ModeloItemForm;
use Modelo\Service\ModeloItemService;

/**
 * CRUD de EXEMPLO — copie este padrão (Controller + Service + Entity +
 * Repository + Form + View) ao adicionar a regra de negócio real do
 * sistema. Protegido pelo mesmo guard de sessão do dashboard
 * (Application\Listener\AuthGuardListener).
 *
 * Nunca importa `Modelo\Entity\ModeloItem` — só o array devolvido por
 * `ModeloItemService`.
 */
final class ModeloController extends AbstractActionController
{
    public function __construct(private readonly ModeloItemService $modeloItemService)
    {
    }

    public function indexAction(): ViewModel
    {
        return new ViewModel(['itens' => $this->modeloItemService->listarTodos()]);
    }

    public function addAction(): ViewModel|Response
    {
        $form = new ModeloItemForm();

        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $data = $form->getData();
                $descricao = (string) $data['descricao'];

                $this->modeloItemService->criar((string) $data['titulo'], $descricao !== '' ? $descricao : null);

                return $this->redirect()->toRoute('modelo');
            }
        }

        return new ViewModel(['form' => $form, 'modo' => 'add']);
    }

    public function editAction(): ViewModel|Response
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        $item = $this->modeloItemService->encontrar($id);

        if ($item === null) {
            return $this->redirect()->toRoute('modelo');
        }

        $form = new ModeloItemForm();
        $form->setData(['titulo' => $item['titulo'], 'descricao' => $item['descricao']]);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $data = $form->getData();
                $descricao = (string) $data['descricao'];

                $atualizado = $this->modeloItemService->atualizar($id, (string) $data['titulo'], $descricao !== '' ? $descricao : null);

                if ($atualizado !== null) {
                    return $this->redirect()->toRoute('modelo');
                }
            }
        }

        return new ViewModel(['form' => $form, 'modo' => 'edit', 'item' => $item]);
    }

    public function deleteAction(): Response
    {
        if ($this->getRequest()->isPost()) {
            $csrfValidator = new CsrfValidator(['name' => 'delete_csrf']);
            $token = (string) $this->params()->fromPost('delete_csrf', '');

            if ($csrfValidator->isValid($token)) {
                $this->modeloItemService->remover((int) $this->params()->fromRoute('id', 0));
            }
        }

        return $this->redirect()->toRoute('modelo');
    }
}
