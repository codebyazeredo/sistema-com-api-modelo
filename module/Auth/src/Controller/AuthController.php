<?php

declare(strict_types=1);

namespace Auth\Controller;

use Auth\Form\LoginForm;
use Auth\Service\DoctrineAuthAdapter;
use Auth\Service\LoginThrottleService;
use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\Result;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Session\SessionManager;
use Laminas\Validator\Csrf as CsrfValidator;
use Laminas\View\Model\ViewModel;

final class AuthController extends AbstractActionController
{
    public function __construct(
        private readonly AuthenticationService $authService,
        private readonly DoctrineAuthAdapter $adapter,
        private readonly LoginThrottleService $throttle,
        private readonly SessionManager $sessionManager,
        private readonly LoginForm $form,
    ) {
    }

    public function loginAction(): ViewModel|Response
    {
        if ($this->authService->hasIdentity()) {
            return $this->redirect()->toRoute('home');
        }

        $this->layout('layout/login');

        $form = $this->form;
        $erro = null;

        if ($this->getRequest()->isPost()) {
            $ip = (string) $this->getRequest()->getServer()->get('REMOTE_ADDR', '0.0.0.0');
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $data = $form->getData();
                $login = (string) $data['login'];

                if ($this->throttle->estaBloqueado($login)) {
                    $erro = 'Muitas tentativas. Aguarde alguns minutos e tente novamente.';
                } else {
                    $this->adapter->setCredentials($login, (string) $data['senha']);
                    $result = $this->authService->authenticate($this->adapter);

                    if ($result->getCode() === Result::SUCCESS) {
                        $this->throttle->registrarSucesso($login, $ip);
                        // Regenera o ID de sessão no login — evita fixação de sessão.
                        $this->sessionManager->regenerateId(true);

                        return $this->redirect()->toRoute('home');
                    }

                    $this->throttle->registrarFalha($login, $ip);
                    $erro = 'Credenciais inválidas.';
                }
            } else {
                $erro = 'Preencha usuário e senha.';
            }
        }

        return new ViewModel(['form' => $form, 'erro' => $erro]);
    }

    public function logoutAction(): Response
    {
        if ($this->getRequest()->isPost()) {
            $csrfValidator = new CsrfValidator(['name' => 'logout_csrf']);
            $token = (string) $this->params()->fromPost('logout_csrf', '');

            if ($csrfValidator->isValid($token)) {
                $this->authService->clearIdentity();
                $this->sessionManager->getStorage()->clear();
                $this->sessionManager->destroy();
            }
        }

        return $this->redirect()->toRoute('login');
    }
}
