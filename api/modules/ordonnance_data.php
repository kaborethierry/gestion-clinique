<?php
// /api/modules/ordonnance_data.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require __DIR__ . '/connect_db_pdo.php';
header('Content-Type: application/json');

// 0) GET single ordonnance + lignes (pour le modal)
if (isset($_GET['id_ordonnance'])) {
    $stmt = $bdd->prepare("
        SELECT o.*,
               p.id   AS id_patient,
               CONCAT(p.nom,' ',p.prenom) AS patient,
               o.id_consultation,
               o.instructions
        FROM ordonnance AS o
        JOIN patient      AS p ON o.id_patient = p.id
        WHERE o.id_ordonnance = :id
          AND o.supprimer = 'NON'
    ");
    $stmt->execute([':id' => (int)$_GET['id_ordonnance']]);
    $ord = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ord) {
        $lstmt = $bdd->prepare("
            SELECT medicament, posologie, duree
              FROM ordonnance_medicament
             WHERE id_ordonnance = :id
               AND supprimer = 'NON'
        ");
        $lstmt->execute([':id' => $ord['id_ordonnance']]);
        $ord['lignes'] = $lstmt->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode(['data' => $ord ? [$ord] : []]);
    exit();
}

// 1) DataTables params
$draw   = intval($_POST['draw']   ?? 0);
$start  = intval($_POST['start']  ?? 0);
$length = intval($_POST['length'] ?? 10);
$search = trim($_POST['search']['value'] ?? '');

// 2) Total avant filtrage
$total = (int)$bdd
    ->query("SELECT COUNT(*) FROM ordonnance WHERE supprimer = 'NON'")
    ->fetchColumn();

// 3) Filtrage
$where  = "WHERE o.supprimer = 'NON'";
$params = [];
if ($search !== '') {
    $where .= " AND (
        o.type_ordonnance               LIKE :s OR
        p.nom                            LIKE :s OR
        p.prenom                         LIKE :s OR
        CONCAT('C-', o.id_consultation)  LIKE :s
    )";
    $params[':s'] = "%{$search}%";
}

// 4) Total après filtrage
$filtered = $bdd->prepare("
    SELECT COUNT(*) 
      FROM ordonnance AS o
      JOIN patient      AS p ON o.id_patient      = p.id
      JOIN consultation AS c ON o.id_consultation = c.id_consultation
    {$where}
");
$filtered->execute($params);
$recordsFiltered = (int)$filtered->fetchColumn();

// 5) Tri & pagination
$orderCols = [
    0 => 'o.id_ordonnance',
    1 => 'o.type_ordonnance',
    2 => 'patient',
    3 => 'consult',
    4 => 'o.date_creation'
];
$orderIndex = intval($_POST['order'][0]['column'] ?? 4);
$orderDir   = (($_POST['order'][0]['dir'] ?? 'desc') === 'asc') ? 'ASC' : 'DESC';
$orderSQL   = isset($orderCols[$orderIndex])
            ? "ORDER BY {$orderCols[$orderIndex]} $orderDir"
            : 'ORDER BY o.date_creation DESC';
$limitSQL = "LIMIT {$start}, {$length}";

// 6) Requête principale
$sql = "
    SELECT
        o.id_ordonnance,
        o.type_ordonnance,
        CONCAT(p.nom,' ',p.prenom) AS patient,
        CONCAT('C-', o.id_consultation) AS consult,
        DATE_FORMAT(o.date_creation, '%d/%m/%Y %H:%i') AS date_creation
    FROM ordonnance AS o
    JOIN patient      AS p ON o.id_patient      = p.id
    JOIN consultation AS c ON o.id_consultation = c.id_consultation
    {$where}
    {$orderSQL}
    {$limitSQL}
";
$stmt = $bdd->prepare($sql);
$stmt->execute($params);

// 7) Construction JSON
$data  = [];
$index = $start + 1;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $data[] = [
        'DT_RowIndex'     => $index++,
        'id_ordonnance'   => $row['id_ordonnance'],
        'type_ordonnance' => $row['type_ordonnance'],
        'patient'         => $row['patient'],
        'consult'         => $row['consult'],
        'date_creation'   => $row['date_creation']
    ];
}

echo json_encode([
    'draw'            => $draw,
    'recordsTotal'    => $total,
    'recordsFiltered' => $recordsFiltered,
    'data'            => $data
]);
