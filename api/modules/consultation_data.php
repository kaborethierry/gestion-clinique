<?php
// /api/modules/consultation_data.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require __DIR__ . '/connect_db_pdo.php';

header('Content-Type: application/json');
// Autoriser SuperAdmin, Secrétaire et Médecin
$allowedRoles = ['Super Administrateur','Medecin'];
if (empty($_SESSION['id'])
    || ! in_array($_SESSION['type_compte'], $allowedRoles, true)
) {
    header('HTTP/1.1 403 Forbidden');
    exit();
}

// -----------------------
// 1) Préremplissage modal
// -----------------------
if (isset($_GET['id_consultation'])) {
    $id = (int) $_GET['id_consultation'];

    // 1a) Consultation + constantes + ordonnance (type & instructions)
    $sql = "
      SELECT
        c.id_consultation,
        c.id_patient,
        c.id_medecin,
        c.date_consultation,
        c.motif,
        c.symptomes,
        c.diagnostic,
        c.observations,

        /* Constantes vitales */
        (SELECT cc.valeur FROM constante_consultation cc
           WHERE cc.id_consultation=c.id_consultation
             AND cc.nom='Tension artérielle'
             AND cc.supprimer='NON'
           ORDER BY cc.date_saisie DESC
           LIMIT 1) AS tension_arterielle,

        (SELECT cc.valeur FROM constante_consultation cc
           WHERE cc.id_consultation=c.id_consultation
             AND cc.nom='Température'
             AND cc.supprimer='NON'
           ORDER BY cc.date_saisie DESC
           LIMIT 1) AS temperature,

        (SELECT cc.valeur FROM constante_consultation cc
           WHERE cc.id_consultation=c.id_consultation
             AND cc.nom='Poids'
             AND cc.supprimer='NON'
           ORDER BY cc.date_saisie DESC
           LIMIT 1) AS poids,

        (SELECT cc.valeur FROM constante_consultation cc
           WHERE cc.id_consultation=c.id_consultation
             AND cc.nom='Taille'
             AND cc.supprimer='NON'
           ORDER BY cc.date_saisie DESC
           LIMIT 1) AS taille,

        (SELECT cc.valeur FROM constante_consultation cc
           WHERE cc.id_consultation=c.id_consultation
             AND cc.nom='Fréquence cardiaque'
             AND cc.supprimer='NON'
           ORDER BY cc.date_saisie DESC
           LIMIT 1) AS frequence_cardiaque,

        (SELECT cc.valeur FROM constante_consultation cc
           WHERE cc.id_consultation=c.id_consultation
             AND cc.nom='IMC'
             AND cc.supprimer='NON'
           ORDER BY cc.date_saisie DESC
           LIMIT 1) AS imc,

        (SELECT cc.valeur FROM constante_consultation cc
           WHERE cc.id_consultation=c.id_consultation
             AND cc.nom='Glycémie'
             AND cc.supprimer='NON'
           ORDER BY cc.date_saisie DESC
           LIMIT 1) AS glycemie,

        (SELECT cc.valeur FROM constante_consultation cc
           WHERE cc.id_consultation=c.id_consultation
             AND cc.nom='Fréquence respiratoire'
             AND cc.supprimer='NON'
           ORDER BY cc.date_saisie DESC
           LIMIT 1) AS frequence_respiratoire,

        (SELECT cc.valeur FROM constante_consultation cc
           WHERE cc.id_consultation=c.id_consultation
             AND cc.nom='Saturation oxygène'
             AND cc.supprimer='NON'
           ORDER BY cc.date_saisie DESC
           LIMIT 1) AS saturation_oxygene,

        /* Ordonnance simple */
        (SELECT o.type_ordonnance FROM ordonnance o
           WHERE o.id_consultation=c.id_consultation
             AND o.supprimer='NON'
           ORDER BY o.date_creation DESC
           LIMIT 1) AS type_ordonnance,

        (SELECT o.instructions FROM ordonnance o
           WHERE o.id_consultation=c.id_consultation
             AND o.supprimer='NON'
           ORDER BY o.date_creation DESC
           LIMIT 1) AS instructions,

        /* Patient & Médecin noms */
        p.nom  AS p_nom,
        p.prenom AS p_prenom,
        u.nom  AS m_nom,
        u.prenom AS m_prenom

      FROM consultation c
      JOIN patient     p ON c.id_patient = p.id
      JOIN utilisateur u ON c.id_medecin  = u.id_utilisateur
     WHERE c.id_consultation = :id
       AND c.supprimer        = 'NON'
    ";
    $stmt = $bdd->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // Formater date pour datetime-local
        $row['date_consultation'] = (new DateTime($row['date_consultation']))
                                      ->format('Y-m-d\TH:i');
        // Construire patient et médecin
        $row['patient']  = "{$row['p_nom']} {$row['p_prenom']}";
        $row['medecin']  = "{$row['m_nom']} {$row['m_prenom']}";
    }

    // 1b) Examens prescrits
    $examStmt = $bdd->prepare("
      SELECT id_examen, type_examen, motif, est_urgent
        FROM examen_labo
       WHERE id_consultation = :id
         AND supprimer       = 'NON'
    ");
    $examStmt->bindValue(':id', $id, PDO::PARAM_INT);
    $examStmt->execute();
    $examens = $examStmt->fetchAll(PDO::FETCH_ASSOC);

    // 1c) Médicaments ordonnance
    $ordId = null;
    $oStmt = $bdd->prepare("
      SELECT id_ordonnance
        FROM ordonnance
       WHERE id_consultation = :id
         AND supprimer       = 'NON'
       ORDER BY date_creation DESC
       LIMIT 1
    ");
    $oStmt->bindValue(':id', $id, PDO::PARAM_INT);
    $oStmt->execute();
    $ordId = $oStmt->fetchColumn();

    $medicaments = [];
    if ($ordId) {
        $mStmt = $bdd->prepare("
          SELECT medicament, posologie, duree
            FROM ordonnance_medicament
           WHERE id_ordonnance = :o
             AND supprimer     = 'NON'
        ");
        $mStmt->bindValue(':o', $ordId, PDO::PARAM_INT);
        $mStmt->execute();
        $medicaments = $mStmt->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode([
      'data'         => $row ? [$row] : [],
      'examens'      => $examens,
      'medicaments'  => $medicaments
    ]);
    exit;
}

// --------------------------------
// 2) DataTables server‐side (POST)
// --------------------------------
$draw        = isset($_POST['draw'])   ? (int) $_POST['draw']   : 0;
$start       = isset($_POST['start'])  ? (int) $_POST['start']  : 0;
$length      = isset($_POST['length']) ? (int) $_POST['length'] : 10;
$searchValue = isset($_POST['search']['value'])
             ? trim($_POST['search']['value'])
             : '';

// total records
$recordsTotal = (int) $bdd
  ->query("SELECT COUNT(*) FROM consultation WHERE supprimer = 'NON'")
  ->fetchColumn();

// filter
$where  = "WHERE c.supprimer = 'NON'";
$params = [];
if ($searchValue !== '') {
    $where .= " AND (
        p.nom        LIKE :s OR
        p.prenom     LIKE :s OR
        u.nom        LIKE :s OR
        u.prenom     LIKE :s OR
        c.motif      LIKE :s OR
        c.symptomes  LIKE :s OR
        c.diagnostic LIKE :s OR
        c.observations LIKE :s
    )";
    $params[':s'] = "%{$searchValue}%";
}

// filtered count
$filteredStmt = $bdd->prepare("
    SELECT COUNT(*)
      FROM consultation c
      JOIN patient     p ON c.id_patient = p.id
      JOIN utilisateur u ON c.id_medecin  = u.id_utilisateur
    {$where}
");
$filteredStmt->execute($params);
$recordsFiltered = (int) $filteredStmt->fetchColumn();

// order & limit
$orderColumns = [
    0 => 'c.id_consultation',
    1 => 'patient',
    2 => 'medecin',
    3 => 'c.date_consultation',
    4 => 'c.motif',
    5 => 'c.symptomes',
    6 => 'c.diagnostic',
    7 => 'c.observations'
];
$colIndex    = $_POST['order'][0]['column'] ?? 3;
$dir         = (strtolower($_POST['order'][0]['dir'] ?? '') === 'asc') ? 'ASC' : 'DESC';
$orderClause = $orderColumns[$colIndex] 
             ? "ORDER BY {$orderColumns[$colIndex]} $dir" 
             : "ORDER BY c.date_consultation DESC";
$limitClause = "LIMIT :start, :length";

// main query
$sql = "
    SELECT
      c.id_consultation,
      CONCAT(p.nom,' ',p.prenom) AS patient,
      CONCAT(u.nom,' ',u.prenom) AS medecin,
      DATE_FORMAT(c.date_consultation,'%d/%m/%Y %H:%i') AS date_consultation,
      c.motif,
      c.symptomes,
      c.diagnostic,
      c.observations,

      (SELECT valeur FROM constante_consultation cc
         WHERE cc.id_consultation=c.id_consultation
           AND cc.nom='Tension artérielle'
           AND cc.supprimer='NON'
         ORDER BY cc.date_saisie DESC LIMIT 1
      ) AS tension_arterielle,

      (SELECT valeur FROM constante_consultation cc
         WHERE cc.id_consultation=c.id_consultation
           AND cc.nom='Température'
           AND cc.supprimer='NON'
         ORDER BY cc.date_saisie DESC LIMIT 1
      ) AS temperature,

      (SELECT valeur FROM constante_consultation cc
         WHERE cc.id_consultation=c.id_consultation
           AND cc.nom='Poids'
           AND cc.supprimer='NON'
         ORDER BY cc.date_saisie DESC LIMIT 1
      ) AS poids,

      (SELECT valeur FROM constante_consultation cc
         WHERE cc.id_consultation=c.id_consultation
           AND cc.nom='Taille'
           AND cc.supprimer='NON'
         ORDER BY cc.date_saisie DESC LIMIT 1
      ) AS taille,

      (SELECT valeur FROM constante_consultation cc
         WHERE cc.id_consultation=c.id_consultation
           AND cc.nom='Fréquence cardiaque'
           AND cc.supprimer='NON'
         ORDER BY cc.date_saisie DESC LIMIT 1
      ) AS frequence_cardiaque,

      (SELECT valeur FROM constante_consultation cc
         WHERE cc.id_consultation=c.id_consultation
           AND cc.nom='IMC'
           AND cc.supprimer='NON'
         ORDER BY cc.date_saisie DESC LIMIT 1
      ) AS imc,

      (SELECT valeur FROM constante_consultation cc
         WHERE cc.id_consultation=c.id_consultation
           AND cc.nom='Glycémie'
           AND cc.supprimer='NON'
         ORDER BY cc.date_saisie DESC LIMIT 1
      ) AS glycemie,

      (SELECT valeur FROM constante_consultation cc
         WHERE cc.id_consultation=c.id_consultation
           AND cc.nom='Fréquence respiratoire'
           AND cc.supprimer='NON'
         ORDER BY cc.date_saisie DESC LIMIT 1
      ) AS frequence_respiratoire,

      (SELECT valeur FROM constante_consultation cc
         WHERE cc.id_consultation=c.id_consultation
           AND cc.nom='Saturation oxygène'
           AND cc.supprimer='NON'
         ORDER BY cc.date_saisie DESC LIMIT 1
      ) AS saturation_oxygene,

      (SELECT o.type_ordonnance FROM ordonnance o
         WHERE o.id_consultation=c.id_consultation
           AND o.supprimer='NON'
         ORDER BY o.date_creation DESC LIMIT 1
      ) AS type_ordonnance,

      (SELECT o.instructions FROM ordonnance o
         WHERE o.id_consultation=c.id_consultation
           AND o.supprimer='NON'
         ORDER BY o.date_creation DESC LIMIT 1
      ) AS instructions

    FROM consultation c
    JOIN patient     p ON c.id_patient = p.id
    JOIN utilisateur u ON c.id_medecin  = u.id_utilisateur
    {$where}
    {$orderClause}
    {$limitClause}
";

$stmt = $bdd->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v, PDO::PARAM_STR);
}
$stmt->bindValue(':start',  $start,  PDO::PARAM_INT);
$stmt->bindValue(':length', $length, PDO::PARAM_INT);
$stmt->execute();

// build output
$data  = [];
$index = $start + 1;
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $data[] = [
        'DT_RowIndex'           => $index++,
        'id_consultation'       => $r['id_consultation'],
        'patient'               => $r['patient'],
        'medecin'               => $r['medecin'],
        'date_consultation'     => $r['date_consultation'],
        'motif'                 => $r['motif'],
        'symptomes'             => $r['symptomes']          ?? '',
        'diagnostic'            => $r['diagnostic'],
        'observations'          => $r['observations']       ?? '',
        'tension_arterielle'    => $r['tension_arterielle'] ?? 'N/A',
        'temperature'           => $r['temperature'] !== null
                                   ? $r['temperature']." °C"
                                   : 'N/A',
        'poids'                 => $r['poids'] !== null
                                   ? $r['poids']." kg"
                                   : 'N/A',
        'taille'                => $r['taille'] !== null
                                   ? $r['taille']." cm"
                                   : 'N/A',
        'frequence_cardiaque'   => $r['frequence_cardiaque'] !== null
                                   ? $r['frequence_cardiaque']." bpm"
                                   : 'N/A',
        'imc'                   => $r['imc'] !== null
                                   ? $r['imc']." kg/m²"
                                   : 'N/A',
        'glycemie'              => $r['glycemie'] !== null
                                   ? $r['glycemie']." g/L"
                                   : 'N/A',
        'frequence_respiratoire'=> $r['frequence_respiratoire'] !== null
                                   ? $r['frequence_respiratoire']." cpm"
                                   : 'N/A',
        'saturation_oxygene'    => $r['saturation_oxygene'] !== null
                                   ? $r['saturation_oxygene']." %"
                                   : 'N/A',
        'type_ordonnance'       => $r['type_ordonnance']     ?? '',
        'instructions'          => $r['instructions']        ?? ''
    ];
}

echo json_encode([
    'draw'            => $draw,
    'recordsTotal'    => $recordsTotal,
    'recordsFiltered' => $recordsFiltered,
    'data'            => $data
]);
exit;
