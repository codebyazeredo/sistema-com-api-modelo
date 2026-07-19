<?php

declare(strict_types=1);

namespace Modelo\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Validator\Csrf as CsrfValidator;
use Laminas\View\Model\ViewModel;
use Modelo\Entity\ModeloItem;
use Modelo\Form\ModeloItemForm;
use Modelo\Repository\ModeloItemRepository;

/**
 * CRUD de EXEMPLO — copie este padrão (Controller + Entity + Repository +
 * Form + View) ao adicionar a regra de negócio real do sistema. Protegido
 * pelo mesmo guard de sessão do dashboard (Application\Listener\AuthGuardListener).
 */
final class ModeloController extends AbstractActionController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function indexAction(): ViewModel
    {
        /** @var ModeloItemRepository $repositorio */
        $repositorio = $this->entityManager->getRepository(ModeloItem::class);

        return new ViewModel(['itens' => $repositorio->listarTodos()]);
    }

    public function addAction(): ViewModel|Response
    {
        $form = new ModeloItemForm();

        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $data = $form->getData();
                $descricao = (string) $data['descricao'];

                $item = new ModeloItem((string) $data['titulo'], $descricao !== '' ? $descricao : null);
                $this->entityManager->persist($item);
                $this->entityManager->flush();

                return $this->redirect()->toRoute('modelo');
            }
        }

        return new ViewModel(['form' => $form, 'modo' => 'add']);
    }

    public function editAction(): ViewModel|Response
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        $item = $this->entityManager->find(ModeloItem::class, $id);

        if ($item === null) {
            return $this->redirect()->toRoute('modelo');
        }

        $form = new ModeloItemForm();
        $form->setData(['titulo' => $item->getTitulo(), 'descricao' => $item->getDescricao()]);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $data = $form->getData();
                $descricao = (string) $data['descricao'];

                $item->setTitulo((string) $data['titulo']);
                $item->setDescricao($descricao !== '' ? $descricao : null);
                $this->entityManager->flush();

                return $this->redirect()->toRoute('modelo');
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
                $id = (int) $this->params()->fromRoute('id', 0);
                $item = $this->entityManager->find(ModeloItem::class, $id);

                if ($item !== null) {
                    $this->entityManager->remove($item);
                    $this->entityManager->flush();
                }
            }
        }

        return $this->redirect()->toRoute('modelo');
    }
}
