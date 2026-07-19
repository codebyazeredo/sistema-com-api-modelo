<?php

declare(strict_types=1);

namespace Api\Controller;

use Api\Service\JwtService;
use Api\Service\RefreshTokenService;
use Auth\Entity\Usuario;
use Auth\Service\DoctrineAuthAdapter;
use Auth\Service\LoginThrottleService;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Authentication\Result;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;

/**
 * Login/refresh/logout da API consumida pelo app (contrato = app-modelo):
 *   POST /auth/login   { login, senha }    -> { token, refresh_token, user }
 *   POST /auth/refresh { refresh_token }   -> { token, refresh_token, user }
 *   POST /auth/logout  (Authorization: Bearer <token>) -> revoga os refresh
 *   tokens do usuário (o access token em si expira sozinho, é stateless).
 */
final class AuthController extends AbstractActionController
{
    public function __construct(
        private readonly DoctrineAuthAdapter $adapter,
        private readonly LoginThrottleService $throttle,
        private readonly JwtService $jwt,
        private readonly RefreshTokenService $refreshTokens,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function loginAction(): JsonModel
    {
        if (! $this->getRequest()->isPost()) {
            return $this->jsonError('Método não permitido.', 405);
        }

        $body = $this->parseJsonBody();
        $login = trim((string) ($body['login'] ?? ''));
        $senha = (string) ($body['senha'] ?? '');
        $ip = (string) $this->getRequest()->getServer()->get('REMOTE_ADDR', '0.0.0.0');

        if ($login === '' || $senha === '') {
            return $this->jsonError('Informe login e senha.', 422);
        }

        if ($this->throttle->estaBloqueado($login)) {
            return $this->jsonError('Muitas tentativas. Aguarde alguns minutos e tente novamente.', 429);
        }

        $this->adapter->setCredentials($login, $senha);
        $result = $this->adapter->authenticate();

        if ($result->getCode() !== Result::SUCCESS) {
            $this->throttle->registrarFalha($login, $ip);

            return $this->jsonError('Credenciais inválidas.', 401);
        }

        $this->throttle->registrarSucesso($login, $ip);

        /** @var array{id:int,login:string,email:string,nome:string,role:string} $usuarioArray */
        $usuarioArray = $result->getIdentity();
        $usuarioEntity = $this->entityManager->getRepository(Usuario::class)->find($usuarioArray['id']);

        return $this->emitirTokens($usuarioEntity, $usuarioArray);
    }

    public function refreshAction(): JsonModel
    {
        if (! $this->getRequest()->isPost()) {
            return $this->jsonError('Método não permitido.', 405);
        }

        $body = $this->parseJsonBody();
        $refreshToken = (string) ($body['refresh_token'] ?? '');

        if ($refreshToken === '') {
            return $this->jsonError('Informe refresh_token.', 422);
        }

        $resultado = $this->refreshTokens->rotacionar($refreshToken);
        if ($resultado === null) {
            return $this->jsonError('Sessão expirada. Faça login novamente.', 401);
        }

        $usuario = $resultado['usuario'];
        $accessToken = $this->jwt->issue(['sub' => $usuario->getId(), 'role' => $usuario->getRole()]);

        return new JsonModel([
            'token' => $accessToken,
            'refresh_token' => $resultado['token'],
            'user' => $usuario->toArray(),
        ]);
    }

    public function logoutAction(): JsonModel
    {
        if (! $this->getRequest()->isPost()) {
            return $this->jsonError('Método não permitido.', 405);
        }

        $claims = $this->params()->fromRoute('jwt_claims');
        $usuarioId = is_array($claims) ? (int) ($claims['sub'] ?? 0) : 0;
        $usuario = $usuarioId > 0 ? $this->entityManager->getRepository(Usuario::class)->find($usuarioId) : null;

        if ($usuario !== null) {
            $this->refreshTokens->revogarTodosDoUsuario($usuario);
        }

        return new JsonModel(['message' => 'Sessão encerrada.']);
    }

    /** @param array{id:int,login:string,email:string,nome:string,role:string} $usuarioArray */
    private function emitirTokens(?Usuario $usuarioEntity, array $usuarioArray): JsonModel
    {
        if ($usuarioEntity === null) {
            return $this->jsonError('Usuário não encontrado.', 500);
        }

        $accessToken = $this->jwt->issue(['sub' => $usuarioArray['id'], 'role' => $usuarioArray['role']]);
        $refresh = $this->refreshTokens->emitir($usuarioEntity);

        return new JsonModel([
            'token' => $accessToken,
            'refresh_token' => $refresh['token'],
            'user' => $usuarioArray,
        ]);
    }

    /** @return array<string, mixed> */
    private function parseJsonBody(): array
    {
        $raw = (string) $this->getRequest()->getContent();
        if ($raw === '') {
            return [];
        }

        $data = json_decode($raw, true);

        return is_array($data) ? $data : [];
    }

    private function jsonError(string $message, int $status): JsonModel
    {
        $this->getResponse()->setStatusCode($status);

        return new JsonModel(['message' => $message]);
    }
}
