<?php

declare(strict_types=1);

/**
 * PONTO DE EXTENSÃO: sincroniza tabelas de "enum"/lookup do domínio real
 * (ex.: popular uma tabela `status_pedido` a partir de um enum PHP, ou
 * garantir que valores fixos existam antes de rodar a aplicação).
 *
 * O esqueleto não define nenhum enum de domínio ainda, então este script é
 * só um placeholder — implemente aqui quando o projeto real precisar,
 * seguindo o padrão de `create-database.php` (carregar config, usar o
 * EntityManager via config/container.php, persistir/atualizar as linhas).
 *
 * Uso: composer database:enum
 */

echo "database:enum é um placeholder — nada para sincronizar ainda.\n";
echo "Edite bin/database-enum.php para popular as tabelas de enum/lookup do seu domínio.\n";
