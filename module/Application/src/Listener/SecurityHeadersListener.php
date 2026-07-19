<?php

declare(strict_types=1);

namespace Application\Listener;

use Laminas\Mvc\MvcEvent;

/**
 * Aplica cabeçalhos de segurança em toda resposta (web e API). Configurável
 * via config/autoload/global.php ("security_headers") — não é segredo, só
 * não deve mudar por ambiente.
 */
final class SecurityHeadersListener
{
    /** @param array<string, string> $config */
    public function __construct(private readonly array $config)
    {
    }

    public function __invoke(MvcEvent $event): void
    {
        $response = $event->getResponse();
        if (! $response instanceof \Laminas\Http\Response) {
            return;
        }

        $headers = $response->getHeaders();
        $headers->addHeaderLine('X-Content-Type-Options', $this->config['x_content_type_options'] ?? 'nosniff');
        $headers->addHeaderLine('X-Frame-Options', $this->config['x_frame_options'] ?? 'DENY');
        $headers->addHeaderLine('Referrer-Policy', $this->config['referrer_policy'] ?? 'strict-origin-when-cross-origin');

        if (! empty($this->config['content_security_policy'])) {
            $headers->addHeaderLine('Content-Security-Policy', $this->config['content_security_policy']);
        }

        // HSTS só faz sentido (e só é seguro anunciar) quando a requisição
        // já veio por HTTPS.
        $request = $event->getRequest();
        $isHttps = $request instanceof \Laminas\Http\Request && $request->getUri()->getScheme() === 'https';
        if ($isHttps) {
            $headers->addHeaderLine('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }
    }
}
