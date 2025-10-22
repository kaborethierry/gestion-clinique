<?php
// api/modules/stats_payment_methods.php

session_start();
if (empty($_SESSION['id'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit();
}

require __DIR__ . '/connect_db_pdo.php';

$sql = "
  SELECT moyen_paiement AS label, COUNT(*) AS value
    FROM facturation
   WHERE supprimer = 'NON'
   GROUP BY moyen_paiement
";

$stmt = $bdd->query($sql);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labels = array_column($data, 'label');
$values = array_column($data, 'value');

header('Content-Type: application/json');
echo json_encode(['labels' => $labels, 'values' => $values]);
