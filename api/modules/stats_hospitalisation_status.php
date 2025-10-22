<?php
// api/modules/stats_hospitalisation_status.php

session_start();
if (empty($_SESSION['id'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit();
}

require __DIR__ . '/connect_db_pdo.php';

$sql = "
  SELECT statut AS label, COUNT(*) AS value
    FROM hospitalisation
   WHERE supprimer = 'NON'
   GROUP BY statut
";

$stmt = $bdd->query($sql);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labels = array_column($data, 'label');
$values = array_column($data, 'value');

header('Content-Type: application/json');
echo json_encode(['labels' => $labels, 'values' => $values]);
