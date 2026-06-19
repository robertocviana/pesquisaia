#!/usr/bin/env php
<?php
/**
 * migrate.php — Executa todas as migrations e seeds via PDO.
 * Uso: lando php database/migrate.php [--seed]
 */

require __DIR__ . '/../app/bootstrap.php';

use App\Database;

$pdo    = Database::pdo();
$runSeed = in_array('--seed', $argv ?? [], true);

$migrations = glob(__DIR__ . '/migrations/*.sql');
sort($migrations);

echo "=== PesquisaIA Migrations ===\n";
foreach ($migrations as $file) {
    $sql = file_get_contents($file);
    try {
        $pdo->exec($sql);
        echo "  ✓ " . basename($file) . "\n";
    } catch (\PDOException $e) {
        echo "  ✗ " . basename($file) . " — " . $e->getMessage() . "\n";
    }
}

if ($runSeed) {
    echo "\n=== Seeds ===\n";
    $seeds = glob(__DIR__ . '/seeds/*.sql');
    sort($seeds);
    foreach ($seeds as $file) {
        $sql = file_get_contents($file);
        try {
            $pdo->exec($sql);
            echo "  ✓ " . basename($file) . "\n";
        } catch (\PDOException $e) {
            echo "  ✗ " . basename($file) . " — " . $e->getMessage() . "\n";
        }
    }
}

echo "\nConcluído.\n";
