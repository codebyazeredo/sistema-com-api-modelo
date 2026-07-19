<?php

declare(strict_types=1);

/**
 * Cria o banco configurado em config/autoload/local.php se ele ainda não
 * existir. O Doctrine só sabe trabalhar com um banco que já existe (o
 * schema-tool cria as TABELAS, não o banco em si) — este script cobre esse
 * primeiro passo.
 *
 * Uso: composer database:create
 */

require __DIR__ . '/../vendor/autoload.php';

use Laminas\Stdlib\ArrayUtils;

$config = require __DIR__ . '/../config/autoload/global.php';

$localPath = __DIR__ . '/../config/autoload/local.php';
if (file_exists($localPath)) {
    $config = ArrayUtils::merge($config, require $localPath);
}

$params = $config['doctrine']['connection']['orm_default']['params'] ?? [];
$host = $params['host'] ?? '127.0.0.1';
$port = $params['port'] ?? '3306';
$user = $params['user'] ?? 'root';
$password = $params['password'] ?? '';
$dbname = $params['dbname'] ?? null;
$charset = $params['charset'] ?? 'utf8mb4';

if (empty($dbname)) {
    fwrite(STDERR, "doctrine.connection.orm_default.params.dbname não configurado. Copie config/autoload/local.php.dist para local.php e ajuste.\n");
    exit(1);
}

try {
    $pdo = new PDO("mysql:host={$host};port={$port};charset={$charset}", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $pdo->exec(sprintf(
        'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET %s COLLATE %s_unicode_ci',
        str_replace('`', '', $dbname),
        $charset,
        $charset,
    ));
    echo "Banco '{$dbname}' pronto (criado agora ou já existia) em {$host}:{$port}.\n";
} catch (PDOException $e) {
    fwrite(STDERR, 'Erro ao criar o banco: ' . $e->getMessage() . "\n");
    exit(1);
}
