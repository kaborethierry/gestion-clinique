<?php
// api/modules/hospitalisation_data.php

// Affichage des erreurs en dev (désactiver en prod)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// ➊ Contrôle d’accès : Super Administrateur, Secretaire et Medecin
$allowedRoles = ['Super Administrateur', 'Secretaire', 'Medecin'];
if (empty($_SESSION['id'])
    || ! in_array($_SESSION['type_compte'], $allowedRoles, true)
) {
    header('HTTP/1.1 403 Forbidden');
    exit();
}

// ➋ Sous-requête SQL pour DataTables (hospitalisations)
$table = <<<SQL
(
    SELECT
      ROW_NUMBER() OVER (ORDER BY h.id_hosp DESC)           AS num_row,
      CONCAT(p.numero_dossier,' – ',p.nom,' ',p.prenom)    AS patient,
      CONCAT(m.nom,' ',m.prenom)                           AS medecin,
      CONCAT(c.numero_chambre,'/',l.numero_lit)            AS chambre_lit,
      DATE_FORMAT(h.date_entree,'%Y-%m-%dT%H:%i')          AS date_entree,
      IFNULL(DATE_FORMAT(h.date_sortie,'%Y-%m-%dT%H:%i'),'')AS date_sortie,
      h.statut                                             AS statut,
      h.motif                                              AS motif,
      h.observations                                       AS observations,
      h.id_hosp                                            AS id_hosp,
      h.id_patient                                         AS id_patient,
      h.id_medecin                                         AS id_medecin,
      h.id_lit                                             AS id_lit
    FROM hospitalisation h
    JOIN patient p     ON h.id_patient  = p.id
    JOIN utilisateur m ON h.id_medecin  = m.id_utilisateur
    JOIN lit l         ON h.id_lit      = l.id_lit
    JOIN chambre c     ON l.id_chambre  = c.id_chambre
    WHERE h.supprimer = 'NON'
) AS tmp
SQL;

// ➌ Clé primaire pour DataTables
$primaryKey = 'id_hosp';

// ➍ Mapping colonnes DB → DataTables
$columns = [
  ['db'=>'num_row',      'dt'=>0],
  ['db'=>'patient',      'dt'=>1],
  ['db'=>'medecin',      'dt'=>2],
  ['db'=>'chambre_lit',  'dt'=>3],
  ['db'=>'date_entree',  'dt'=>4],
  ['db'=>'date_sortie',  'dt'=>5],
  ['db'=>'statut',       'dt'=>6],
  ['db'=>'motif',        'dt'=>7],
  ['db'=>'observations', 'dt'=>8],
  ['db'=>'id_hosp',      'dt'=>9],
  ['db'=>'id_patient',   'dt'=>10],
  ['db'=>'id_medecin',   'dt'=>11],
  ['db'=>'id_lit',       'dt'=>12]
];

// ➎ Chargement configuration et class SSP
require __DIR__ . '/connect_db_data.php';
require __DIR__ . '/DataTables/examples/server_side/scripts/ssp.class.php';

// ➏ Envoi de la réponse JSON à DataTables
echo json_encode(
  SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns)
);
