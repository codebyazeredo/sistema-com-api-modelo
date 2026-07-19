<?php

declare(strict_types=1);

namespace Application\Controller;

use Laminas\Authentication\AuthenticationService;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

/**
 * Tela inicial de exemplo do dashboard. Substitua o conteúdo real pela
 * regra de negócio do sistema — o layout (navbar + sidebar) fica em
 * view/layout/layout.phtml e é compartilhado por todas as telas.
 */
final class DashboardController extends AbstractActionController
{
    public function __construct(private readonly AuthenticationService $auth)
    {
    }

    public function indexAction(): ViewModel
    {
        return new ViewModel([
            'usuario' => $this->auth->getIdentity(),
        ]);
    }
}
