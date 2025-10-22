<?php
// api/modules/stats_patients_gender.php
session_start();
if (empty($_SESSION['id'])) exit();

include __DIR__.'/connect_db_pdo.php';
$sql = "
  SELECT sexe AS label, COUNT(*) AS cnt
    FROM patient
   WHERE supprimer='NON'
   GROUP BY sexe
";
$stmt = $bdd->query($sql);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labels = array_column($data, 'label');
$values = array_column($data, 'cnt');

header('Content-Type: application/json');
echo json_encode(['labels' => $labels, 'values' => $values]);
