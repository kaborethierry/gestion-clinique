<?php
// api/modules/patient_data.php

// Afficher les erreurs en dev (désactiver en production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1) Démarrage de session et contrôle d’accès
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Autoriser Super Administrateur, Secretaire et Medecin
$allowedRoles = ['Super Administrateur', 'Secretaire', 'Medecin'];
if (empty($_SESSION['id'])
    || ! in_array($_SESSION['type_compte'], $allowedRoles, true)
) {
    header('HTTP/1.1 403 Forbidden');
    exit();
}

// 2) Sous-requête SQL pour DataTables (patients + assurance)
$table = <<<EOT
(
    SELECT
        ROW_NUMBER() OVER (ORDER BY p.id)                     AS num_row,
        p.numero_dossier,
        p.nom,
        p.prenom,
        p.sexe,
        DATE_FORMAT(p.date_naissance, '%d/%m/%Y')            AS date_naissance,
        p.telephone,
        p.telephone_secondaire,
        p.email,
        p.ville,
        p.quartier,
        p.groupe_sanguin,
        p.poids,
        p.tension_arterielle,
        DATE_FORMAT(p.date_enregistrement, '%d/%m/%Y %H:%i') AS date_enregistrement,
        p.statut,
        p.id,
        p.assurance_statut,
        p.assurance_compagnie,
        p.numero_police,
        p.taux_couverture,
        p.type_couverture,
        DATE_FORMAT(p.date_debut_couverture, '%Y-%m-%d')     AS date_debut_couverture,
        DATE_FORMAT(p.date_fin_couverture,   '%Y-%m-%d')     AS date_fin_couverture,
        p.lieu_naissance,
        p.nationalite,
        p.etat_civil,
        p.profession,
        p.adresse,
        p.situation_handicap,
        p.allergie,
        p.antecedents_medicaux,
        p.personne_contact_nom,
        p.personne_contact_lien,
        p.personne_contact_tel
    FROM patient AS p
    WHERE p.supprimer = 'NON'
) AS tmp
EOT;

// 3) Clé primaire
$primaryKey = 'id';

// 4) Mapping des colonnes (dt = index côté JS)
$columns = [
    [ 'db' => 'num_row',               'dt' => 0 ],
    [ 'db' => 'numero_dossier',        'dt' => 1 ],
    [ 'db' => 'nom',                   'dt' => 2 ],
    [ 'db' => 'prenom',                'dt' => 3 ],
    [ 'db' => 'sexe',                  'dt' => 4 ],
    [ 'db' => 'date_naissance',        'dt' => 5 ],
    [ 'db' => 'telephone',             'dt' => 6 ],
    [ 'db' => 'telephone_secondaire',  'dt' => 7 ],
    [ 'db' => 'email',                 'dt' => 8 ],
    [ 'db' => 'ville',                 'dt' => 9 ],
    [ 'db' => 'quartier',              'dt' => 10],
    [ 'db' => 'groupe_sanguin',        'dt' => 11],
    [ 'db' => 'poids',                 'dt' => 12],
    [ 'db' => 'tension_arterielle',    'dt' => 13],
    [ 'db' => 'date_enregistrement',   'dt' => 14],
    [ 'db' => 'statut',                'dt' => 15],
    [ 'db' => 'id',                    'dt' => 16],
    [ 'db' => 'assurance_statut',      'dt' => 17],
    [ 'db' => 'assurance_compagnie',   'dt' => 18],
    [ 'db' => 'numero_police',         'dt' => 19],
    [ 'db' => 'taux_couverture',       'dt' => 20],
    [ 'db' => 'type_couverture',       'dt' => 21],
    [ 'db' => 'date_debut_couverture', 'dt' => 22],
    [ 'db' => 'date_fin_couverture',   'dt' => 23],
    [ 'db' => 'lieu_naissance',        'dt' => 24],
    [ 'db' => 'nationalite',           'dt' => 25],
    [ 'db' => 'etat_civil',            'dt' => 26],
    [ 'db' => 'profession',            'dt' => 27],
    [ 'db' => 'adresse',               'dt' => 28],
    [ 'db' => 'situation_handicap',    'dt' => 29],
    [ 'db' => 'allergie',              'dt' => 30],
    [ 'db' => 'antecedents_medicaux',  'dt' => 31],
    [ 'db' => 'personne_contact_nom',  'dt' => 32],
    [ 'db' => 'personne_contact_lien', 'dt' => 33],
    [ 'db' => 'personne_contact_tel',  'dt' => 34]
];

// 5) Connexion et inclusion de la classe SSP
include __DIR__ . '/connect_db_data.php';
require __DIR__ . '/DataTables/examples/server_side/scripts/ssp.class.php';

// 6) Exécution et retour JSON
echo json_encode(
    SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns)
);
