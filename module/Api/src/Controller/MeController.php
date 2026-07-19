<?php

declare(strict_types=1);

namespace Api\Controller;

use Auth\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;

/**
 * Endpoint protegido de EXEMPLO — prova que o Bearer/JWT funciona fim-a-fim
 * (JwtAuthListener já validou o token antes de chegar aqui). Sirva de
 * modelo para os endpoints reais da API.
 */
final class MeController extends AbstractActionController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function indexAction(): JsonModel
    {
        $claims = $this->params()->fromRoute('jwt_claims');
        $usuarioId = is_array($claims) ? (int) ($claims['sub'] ?? 0) : 0;
        $usuario = $usuarioId > 0 ? $this->entityManager->getRepository(Usuario::class)->find($usuarioId) : null;

        if ($usuario === null) {
            $this->getResponse()->setStatusCode(404);

            return new JsonModel(['message' => 'Usuário não encontrado.']);
        }

        return new JsonModel(['user' => $usuario->toArray()]);
    }
}
