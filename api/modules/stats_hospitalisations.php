<?php
// api/modules/stats_hospitalisations.php

session_start();
if (empty($_SESSION['id'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit();
}

// Affichage des erreurs en dev (à retirer en production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/connect_db_pdo.php';

$sql = "
  SELECT 'Hospitalisations' AS label,
         COUNT(*)             AS value
    FROM hospitalisation
   WHERE supprimer = 'NON'
";

$stmt = $bdd->query($sql);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labels = array_column($data, 'label');
$values = array_column($data, 'value');

header('Content-Type: application/json; charset=utf-8');
echo json_encode(
    ['labels' => $labels, 'values' => $values],
    JSON_UNESCAPED_UNICODE
);
