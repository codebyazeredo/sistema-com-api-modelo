<?php

declare(strict_types=1);

/**
 * Cria um usuário (ou atualiza a senha, se o login já existir) — útil pra
 * logar no dashboard/API durante o desenvolvimento, sem precisar escrever
 * um script pontual toda vez.
 *
 * Uso:
 *   php bin/create-user.php [login] [senha] [nome] [email]
 *   composer user:create                          (usa os valores padrão)
 *   composer user:create -- teste teste123 "Fulano" teste@exemplo.com
 *
 * Padrão: login=admin senha=senha123 nome="Administrador" email=admin@exemplo.com
 */

require __DIR__ . '/../vendor/autoload.php';

use Application\Service\PasswordHasher;
use Auth\Entity\Usuario;
use Auth\Repository\UsuarioRepository;

$login = $argv[1] ?? 'admin';
$senha = $argv[2] ?? 'senha123';
$nome = $argv[3] ?? 'Administrador';
$email = $argv[4] ?? 'admin@exemplo.com';

$container = require __DIR__ . '/../config/container.php';

/** @var \Doctrine\ORM\EntityManagerInterface $entityManager */
$entityManager = $container->get('doctrine.entitymanager.orm_default');
/** @var PasswordHasher $hasher */
$hasher = $container->get(PasswordHasher::class);

/** @var UsuarioRepository $repositorio */
$repositorio = $entityManager->getRepository(Usuario::class);
$existente = $repositorio->encontrarAtivoPorLoginOuEmail($login);

if ($existente !== null) {
    $existente->setSenha($hasher->hash($senha));
    $entityManager->flush();
    echo "Usuário '{$login}' já existia — senha atualizada para '{$senha}'.\n";
    exit(0);
}

$usuario = new Usuario($login, $email, $hasher->hash($senha), $nome);
$entityManager->persist($usuario);
$entityManager->flush();

echo "Usuário criado: login={$login} senha={$senha} nome=\"{$nome}\" (id={$usuario->getId()})\n";
