<?php

declare(strict_types=1);

/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

use Laminas\Session\Storage\SessionArrayStorage;

return [
    // Sessão do dashboard web (login por cookie). A API (módulo Api) usa
    // JWT/Bearer e não depende desta sessão.
    'session_config' => [
        'cookie_lifetime' => 0, // expira ao fechar o navegador
        'gc_maxlifetime' => 60 * 60 * 8,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        // Só marca o cookie como Secure quando a requisição já chegou via
        // HTTPS — em dev local (http://localhost) o cookie continua
        // funcionando; atrás de um proxy/load balancer, configure-o para
        // repassar o header X-Forwarded-Proto antes de colocar em produção.
        'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ],
    'session_storage' => [
        'type' => SessionArrayStorage::class,
    ],

    // Doctrine: só a parte não-sensível (driver/charset). Credenciais de
    // conexão (host/usuário/senha/banco) ficam em config/autoload/local.php.
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'driverClass' => 'Doctrine\DBAL\Driver\PDO\MySQL\Driver',
                'params' => [
                    'charset' => 'utf8mb4',
                ],
            ],
        ],
    ],

    // Cabeçalhos de segurança aplicados a toda resposta (SecurityHeadersListener,
    // module/Application/src/Listener/SecurityHeadersListener.php).
    'security_headers' => [
        // 'unsafe-eval' em script-src é exigido pelo Alpine.js "padrão" (ele
        // avalia as expressões dos atributos x-data/x-on via Function()).
        // Para CSP sem 'unsafe-eval', troque o pacote "alpinejs" por
        // "@alpinejs/csp" (build restrito, sem eval) — exige registrar os
        // componentes via Alpine.data() em vez de objetos inline nos
        // atributos; documentado no README > Segurança.
        'content_security_policy' => "default-src 'self'; script-src 'self' 'unsafe-eval'; style-src 'self'; "
            . "img-src 'self' data:; font-src 'self'; connect-src 'self'; frame-ancestors 'none'; "
            . "base-uri 'self'; form-action 'self'",
        'x_frame_options' => 'DENY',
        'x_content_type_options' => 'nosniff',
        'referrer_policy' => 'strict-origin-when-cross-origin',
    ],
];
