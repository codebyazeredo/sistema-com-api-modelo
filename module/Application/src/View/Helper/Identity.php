<?php

declare(strict_types=1);

namespace Application\View\Helper;

use Laminas\Authentication\AuthenticationService;
use Laminas\View\Helper\AbstractHelper;

/**
 * Helper de view `$this->identity()` — devolve o identity da sessão atual
 * (ou null), sem cada controller precisar repassar isso manualmente.
 */
final class Identity extends AbstractHelper
{
    public function __construct(private readonly AuthenticationService $auth)
    {
    }

    public function __invoke(): mixed
    {
        return $this->auth->hasIdentity() ? $this->auth->getIdentity() : null;
    }
}
