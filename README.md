# Sistema Modelo

Esqueleto Laminas (PHP 8.3) genérico e reutilizável: dashboard web (Tailwind +
Alpine.js) com login por sessão, e uma API JWT no mesmo projeto — pronto para
servir de base tanto a sistemas web novos quanto ao backend consumido por
apps gerados a partir do [`app-modelo`](../app-modelo) (Expo). Juntos, os
dois esqueletos formam o início de um ecossistema reutilizável.

## Como rodar

```bash
composer install
npm install && npm run build   
# gera public/css/app.css e public/js/alpine.min.js

cp config/autoload/local.php.dist config/autoload/local.php
# edite config/autoload/local.php: credenciais do banco e jwt.secret
# gere um segredo forte com: php -r "echo bin2hex(random_bytes(32));"
composer clear-config-cache   # sempre que mexer em config/autoload/local.php

composer database:create   # cria o banco (ver "Banco de dados" abaixo)
composer database:update   # cria as tabelas
php -S 0.0.0.0:8080 -t public
```

### Como abrir

O último comando acima (`php -S 0.0.0.0:8080 -t public`) sobe o servidor
embutido do PHP servindo a pasta `public/` — depois é só abrir
`http://localhost:8080/login` no navegador. Para parar, `Ctrl+C` no terminal
onde ele está rodando.

Durante o desenvolvimento do CSS, use `npm run watch:css` em vez de
`build:css` (recompila a cada mudança nos `.phtml`).

> **Cache de config**: o Laminas cacheia a config mesclada em
> `data/cache/*.php` (`config_cache_enabled` em `config/application.config.php`).
> Se você editar `config/autoload/local.php` (ou qualquer `config/autoload/*.php`)
> e as mudanças parecerem não fazer efeito, rode `composer clear-config-cache`.

## Banco de dados

O projeto usa Doctrine ORM + MySQL (configurado em `config/autoload/local.php`).
Comandos disponíveis (aliases de composer):

| Comando | O que faz |
|---|---|
| `composer database:create` | Cria o banco (`CREATE DATABASE IF NOT EXISTS`) se ele ainda não existir — o Doctrine só cria tabelas, não o banco em si (`bin/create-database.php`). |
| `composer database:init` | **Destrutivo.** `database:create` + apaga e recria o schema inteiro do zero. Use em dev quando quiser começar limpo. |
| `composer database:update` | Limpa os caches do Doctrine e aplica as mudanças de schema pendentes (aditivo/seguro) — rode depois de criar/alterar uma Entity. |
| `composer database:check` | Valida se o mapeamento das Entities bate com o schema atual do banco. |
| `composer database:sql` | Mostra o SQL que `database:update` executaria, sem aplicar nada (dry-run). |
| `composer database:clear` | Só limpa os caches de metadata/result/query do Doctrine. |
| `composer database:enum` | **Placeholder** — ponto de extensão pra sincronizar tabelas de enum/lookup do seu domínio (`bin/database-enum.php`); não faz nada até você implementar, já que o esqueleto não define nenhum enum de domínio. |
| `composer test:local` | `database:enum` + `database:update` + `phpunit` — fluxo rápido antes de rodar os testes localmente. |

Fluxo típico do zero:

```bash
composer database:create
composer database:update    # ou: vendor/bin/doctrine-module orm:schema-tool:create
```

Não há usuário nenhum por padrão — crie um usando
`Application\Service\PasswordHasher::hash()` para gerar o hash da senha
(nunca insira senha em texto puro na tabela `usuario`), por exemplo com um
script pontual em `bin/` que persista uma `Auth\Entity\Usuario` via o
`EntityManager` (`config/container.php`).

## Estrutura

```
module/
  Application/   camada compartilhada: layout do dashboard (navbar+sidebar),
                  cabeçalhos de segurança, PasswordHasher, guard de sessão
  Auth/           login web por SESSÃO (cookie) — Usuario, throttle de login
  Api/            API JWT consumida pelo app — login/refresh/logout, /me
  Modelo/         MÓDULO DE EXEMPLO (CRUD) — copie este padrão pro real

resources/css/app.css   fonte do Tailwind (não editar public/css/app.css à mão)
tailwind.config.js      content aponta para module/**/view/**/*.phtml
```

Cada módulo segue `config/module.config.php` + `src/{Controller,Entity,
Repository,Form,Service,Middleware}` + `view/`. Doctrine: cada módulo declara
seu próprio driver de mapeamento (atributos PHP 8, não annotations) em
`doctrine.driver` no seu `module.config.php` — não é preciso editar nada
centralizado ao adicionar um módulo novo.

## Como adicionar um módulo novo

1. Copie a pasta `module/Modelo` (Controller + Entity + Repository + Form +
   View) e renomeie para o domínio real.
2. Ajuste o namespace (`Modelo\...` → `SeuModulo\...`), as rotas e o driver
   Doctrine (`'SeuModulo\Entity' => 'seumodulo_entities'`) no
   `module.config.php` copiado.
3. Registre o autoload PSR-4 no `composer.json` (`"SeuModulo\\": "module/SeuModulo/src/"`)
   e rode `composer dump-autoload`.
4. Adicione o nome do módulo em `config/modules.config.php`.
5. Se as telas ficam no dashboard, adicione um link em
   `module/Application/view/layout/layout.phtml`.

## Autenticação — duas superfícies, um usuário

- **Dashboard web** (`module/Auth`): sessão/cookie tradicional do Laminas
  (`httpOnly`, `SameSite=Lax`, regenера ID no login). `GET/POST /login`,
  `POST /logout`. Protegido por `Application\Listener\AuthGuardListener`
  (qualquer rota que não seja `login` nem comece com `api-` exige sessão).
- **API do app** (`module/Api`): stateless, `Authorization: Bearer <token>`.
  Contrato idêntico ao já documentado no `app-modelo`:
  - `POST /auth/login` `{ login, senha }` → `{ token, refresh_token, user }`
  - `POST /auth/refresh` `{ refresh_token }` → `{ token, refresh_token, user }`
  - `POST /auth/logout` (`Authorization: Bearer <token>`) → revoga os
    refresh tokens do usuário (o access token em si expira sozinho em 1h —
    ver "Limitações conhecidas" abaixo)
  - `GET /me` (`Authorization: Bearer <token>`) — endpoint protegido de
    EXEMPLO, prova que o Bearer/JWT funciona fim-a-fim

Ambas as superfícies autenticam contra a mesma tabela `usuario`
(`Auth\Entity\Usuario`) via `Auth\Service\DoctrineAuthAdapter` — um único
usuário, dois jeitos de provar identidade.

> **Nota:** o `app-modelo` hoje só usa o `token` de acesso (não chama
> `/auth/refresh`) — então a sessão do app expira em 1h até o client ser
> atualizado para adotar o fluxo de refresh. Isso é intencional: o backend
> já está pronto para refresh token, falta o app adotar.

## Segurança

- **Hash de senha**: Argon2id (`Application\Service\PasswordHasher`), nunca
  texto puro nem MD5/SHA1.
- **CSRF** em todo formulário/POST que muda estado no dashboard web (login,
  logout, criar/editar/excluir no módulo Modelo) via `Laminas\Form\Element\Csrf`
  / `Laminas\Validator\Csrf`.
- **Sessão**: cookie `httpOnly`, `SameSite=Lax`, `Secure` automático quando
  a requisição já é HTTPS, ID de sessão regenerado no login (evita fixação
  de sessão).
- **Login throttle** (`Auth\Service\LoginThrottleService`): bloqueia por
  alguns minutos após várias falhas seguidas para o mesmo login, usado no
  login web e no da API. Mensagem de erro sempre genérica — nunca revela se
  o usuário existe.
- **HTTPS obrigatório**: `security_headers` inclui HSTS quando a requisição
  já chega via HTTPS; force TLS no seu proxy/servidor em produção (o app não
  bloqueia HTTP sozinho — isso é responsabilidade do servidor web/proxy).
- **JWT de curta duração (1h) + refresh token rotativo**
  (`Api\Service\JwtService` / `Api\Service\RefreshTokenService`): refresh
  token é opaco (256 bits aleatórios), só o hash SHA-256 fica no banco: cada
  uso troca por um novo (rotação) e revoga o antigo; reapresentar um token
  já revogado é tratado como possível roubo e revoga TODOS os tokens
  daquele usuário.
- **CORS por allow-list explícita** (`config/autoload/local.php` →
  `cors.allowed_origins`) — nunca reflete Origin arbitrária, e a API não
  envia `Access-Control-Allow-Credentials` (Bearer não usa cookie, então
  não precisa). Apps mobile não enviam Origin, então não são afetados.
- **Cabeçalhos de segurança** em toda resposta (`security_headers` em
  `config/autoload/global.php`): CSP, `X-Content-Type-Options`,
  `X-Frame-Options: DENY`, `Referrer-Policy`.
- **Segredos fora do git**: `config/autoload/local.php` (DB, `jwt.secret`,
  CORS) segue o padrão `local.php.dist` — nunca commitado. A aplicação
  **recusa iniciar** se `jwt.secret` estiver ausente ou tiver menos de 32
  caracteres (`Api\Service\Factory\JwtServiceFactory`).
- **Doctrine ORM em tudo** (sem SQL cru/concatenado).

### Limitações conhecidas (decisão consciente para manter o esqueleto enxuto)

- **Access token JWT não é revogável antes de expirar** — é stateless por
  design. `POST /auth/logout` revoga os *refresh tokens*, mas um access
  token já emitido continua válido até a própria expiração (1h). Para
  revogação imediata de access token, seria necessário um blocklist
  (Redis/cache) — deixado de fora do esqueleto; adicione se o projeto real
  precisar.
- **CSP libera `'unsafe-eval'`** em `script-src` porque o Alpine.js
  "padrão" avalia expressões via `Function()`. Para CSP sem `unsafe-eval`,
  troque a dependência `alpinejs` por `@alpinejs/csp` (build restrito) e
  registre os componentes via `Alpine.data()` em vez de objetos inline nos
  atributos `x-data`.
- **Rate limiting geral da API** (além do throttle de login) e **envio de
  e-mail** (recuperação de senha, notificações) não estão implementados —
  ficam a cargo de cada projeto (proxy/gateway para rate limit; PHPMailer/
  transacional para e-mail, como no `novo_educacao`).
- **MFA e RBAC granular por ação** (como no sistema legado `novo_educacao`)
  não fazem parte do esqueleto — adicione por projeto quando o caso de uso
  pedir.

## QA

```bash
composer cs-check          # phpcs
composer test               # phpunit
composer static-analysis    # psalm
```

## Docker

```bash
docker-compose up -d --build
```

Sobe em `http://localhost:8080`. O `Dockerfile` já habilita `pdo_mysql`
(necessário para o Doctrine conforme configurado em `config/autoload/global.php`).

---

Desenvolvido e mantido por: [@codebyazeredo](https://github.com/codebyazeredo)
