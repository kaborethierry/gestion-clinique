<?php
// api/modules/rendez_vous_data.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Autoriser SuperAdmin, Secrétaire et Médecin
$allowedRoles = ['Super Administrateur','Secretaire','Medecin'];
if (empty($_SESSION['id'])
    || ! in_array($_SESSION['type_compte'], $allowedRoles, true)
) {
    header('HTTP/1.1 403 Forbidden');
    exit();
}

$table = <<<EOT
(
    SELECT
        ROW_NUMBER() OVER (ORDER BY rv.id DESC)         AS num_row,
        CONCAT(p.nom,' ',p.prenom)                    AS patient,
        CONCAT(u.nom,' ',u.prenom)                    AS medecin,
        DATE_FORMAT(rv.date_rdv,'%d/%m/%Y')            AS date_rdv,
        TIME_FORMAT(rv.heure_rdv,'%H:%i')              AS heure_rdv,
        rv.motif                                        AS motif,
        rv.statut                                       AS statut,
        rv.note                                         AS note,
        rv.id                                           AS id,
        rv.id_patient                                   AS id_patient,
        rv.id_medecin                                   AS id_medecin
    FROM rendez_vous rv
    JOIN patient    p ON p.id              = rv.id_patient
    JOIN utilisateur u ON u.id_utilisateur = rv.id_medecin
) AS temp_rdv
EOT;

$primaryKey = 'id';
$columns = [
    ['db'=>'num_row',    'dt'=>0],
    ['db'=>'patient',    'dt'=>1],
    ['db'=>'medecin',    'dt'=>2],
    ['db'=>'date_rdv',   'dt'=>3],
    ['db'=>'heure_rdv',  'dt'=>4],
    ['db'=>'motif',      'dt'=>5],
    ['db'=>'statut',     'dt'=>6],
    ['db'=>'note',       'dt'=>7],
    ['db'=>'id',         'dt'=>8],
    ['db'=>'id_patient', 'dt'=>9],
    ['db'=>'id_medecin', 'dt'=>10],
];

include __DIR__ . '/connect_db_data.php';
require __DIR__ . '/DataTables/examples/server_side/scripts/ssp.class.php';

echo json_encode(
    SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns)
);
