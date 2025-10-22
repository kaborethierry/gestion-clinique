<?php
// api/modules/ajouter_patient.php

// Affichage des erreurs pour le debug (désactiver en production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// ➊ Contrôle d’accès : Super Administrateur, Secrétaire et Médecin
$allowedRoles = ['Super Administrateur', 'Secretaire', 'Medecin'];
if (empty($_SESSION['id']) 
    || ! in_array($_SESSION['type_compte'], $allowedRoles, true)
) {
    session_unset();
    session_destroy();
    header('Location: ../index.php?erreur=3');
    exit();
}

// ➋ Validation des champs obligatoires
$required = ['nom', 'prenom', 'telephone', 'sexe', 'assurance_statut'];
foreach ($required as $f) {
    if (!isset($_POST[$f]) || trim($_POST[$f]) === '') {
        $_SESSION['error'] = "Le champ $f est obligatoire";
        header('Location: ../../pages/patient.php');
        exit();
    }
}

// ➌ Connexion MySQLi pour l’échappement
require __DIR__ . '/connect_db.php';

// Fonction d’échappement et nettoyage
function esc($db, $val) {
    if ($val === null || $val === '') {
        return null;
    }
    return mysqli_real_escape_string($db, htmlspecialchars(trim($val), ENT_QUOTES));
}

// ➍ Récupération et sécurisation des champs de base
$nom    = esc($db, $_POST['nom']);
$prenom = esc($db, $_POST['prenom']);
$tel    = esc($db, $_POST['telephone']);
$sexe   = esc($db, $_POST['sexe']);

// ➎ Champs optionnels
$optional = [
    'date_naissance','lieu_naissance','nationalite','etat_civil','adresse',
    'ville','quartier','telephone_secondaire','email','profession',
    'groupe_sanguin','allergie','antecedents_medicaux',
    'personne_contact_nom','personne_contact_lien','personne_contact_tel',
    'tension_arterielle','poids'
];

$data = [];
foreach ($optional as $field) {
    $data[$field] = !empty($_POST[$field])
        ? esc($db, $_POST[$field])
        : null;
}
$data['poids'] = is_numeric($data['poids']) ? (float)$data['poids'] : null;
$situation_handicap = isset($_POST['situation_handicap'])
    ? (int) $_POST['situation_handicap']
    : 0;

// ➏ Gestion du fichier photo
$photo = null;
if (!empty($_FILES['photo']['tmp_name'])) {
    $types = ['image/jpeg','image/png','image/gif'];
    $max   = 2 * 1024 * 1024;
    if (in_array($_FILES['photo']['type'], $types, true)
        && $_FILES['photo']['size'] <= $max
    ) {
        $ext  = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $file = 'pat_' . bin2hex(random_bytes(8)) . '.' . strtolower($ext);
        $dest = __DIR__ . '/../../uploads/patients/' . $file;
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
            $photo = $file;
        }
    }
}

// ➐ Données assurance
$ass_statut = esc($db, $_POST['assurance_statut']);
$assuranceData = [
    'compagnie'   => null,
    'numero'      => null,
    'taux'        => null,
    'type'        => null,
    'debut'       => null,
    'fin'         => null,
    'justificatif'=> null
];

if ($ass_statut === 'Assuré') {
    $map = [
        'compagnie' => 'assurance_compagnie',
        'numero'    => 'numero_police',
        'taux'      => 'taux_couverture',
        'type'      => 'type_couverture',
        'debut'     => 'date_debut_couverture',
        'fin'       => 'date_fin_couverture'
    ];
    foreach ($map as $k => $f) {
        if (!empty($_POST[$f])) {
            $assuranceData[$k] = esc($db, $_POST[$f]);
        }
    }
    if (!empty($_FILES['justificatif_assurance']['tmp_name'])) {
        $docTypes = ['application/pdf','image/jpeg','image/png'];
        $maxDoc   = 5 * 1024 * 1024;
        if (in_array($_FILES['justificatif_assurance']['type'],$docTypes, true)
            && $_FILES['justificatif_assurance']['size'] <= $maxDoc
        ) {
            $ext  = pathinfo($_FILES['justificatif_assurance']['name'], PATHINFO_EXTENSION);
            $doc  = 'asr_' . bin2hex(random_bytes(8)) . '.' . strtolower($ext);
            $path = __DIR__ . '/../../uploads/assurances/' . $doc;
            if (move_uploaded_file($_FILES['justificatif_assurance']['tmp_name'], $path)) {
                $assuranceData['justificatif'] = $doc;
            }
        }
    }
}

// ➑ Génération du numéro de dossier
$numero_dossier = 'PAT-' . strtoupper(substr($nom, 0, 3)) . '-' . date('YmdHis');

// ➒ Insertion en base via PDO
require __DIR__ . '/connect_db_pdo.php';

$sql = "INSERT INTO patient (
    nom, prenom, sexe, date_naissance, lieu_naissance, nationalite,
    etat_civil, adresse, ville, quartier, telephone,
    telephone_secondaire, email, profession, groupe_sanguin,
    poids, tension_arterielle, situation_handicap, allergie,
    antecedents_medicaux, numero_dossier,
    personne_contact_nom, personne_contact_lien, personne_contact_tel,
    photo, assurance_statut, assurance_compagnie, numero_police,
    taux_couverture, type_couverture, date_debut_couverture,
    date_fin_couverture, justificatif_assurance
) VALUES (
    :nom, :prenom, :sexe, :date_naissance, :lieu_naissance, :nationalite,
    :etat_civil, :adresse, :ville, :quartier, :telephone,
    :telephone_secondaire, :email, :profession, :groupe_sanguin,
    :poids, :tension_arterielle, :handicap, :allergie,
    :antecedents_medicaux, :dossier,
    :contact_nom, :contact_lien, :contact_tel,
    :photo, :assurance_statut, :compagnie, :numero_police,
    :taux_couverture, :type_couverture, :debut, :fin, :justificatif
)";

try {
    $stmt = $bdd->prepare($sql);
    $params = [
        ':nom'                  => $nom,
        ':prenom'               => $prenom,
        ':sexe'                 => $sexe,
        ':date_naissance'       => $data['date_naissance'],
        ':lieu_naissance'       => $data['lieu_naissance'],
        ':nationalite'          => $data['nationalite'],
        ':etat_civil'           => $data['etat_civil'],
        ':adresse'              => $data['adresse'],
        ':ville'                => $data['ville'],
        ':quartier'             => $data['quartier'],
        ':telephone'            => $tel,
        ':telephone_secondaire' => $data['telephone_secondaire'],
        ':email'                => $data['email'],
        ':profession'           => $data['profession'],
        ':groupe_sanguin'       => $data['groupe_sanguin'],
        ':poids'                => $data['poids'],
        ':tension_arterielle'   => $data['tension_arterielle'],
        ':handicap'             => $situation_handicap,
        ':allergie'             => $data['allergie'],
        ':antecedents_medicaux' => $data['antecedents_medicaux'],
        ':dossier'              => $numero_dossier,
        ':contact_nom'          => $data['personne_contact_nom'],
        ':contact_lien'         => $data['personne_contact_lien'],
        ':contact_tel'          => $data['personne_contact_tel'],
        ':photo'                => $photo,
        ':assurance_statut'     => $ass_statut,
        ':compagnie'            => $assuranceData['compagnie'],
        ':numero_police'        => $assuranceData['numero'],
        ':taux_couverture'      => $assuranceData['taux'],
        ':type_couverture'      => $assuranceData['type'],
        ':debut'                => $assuranceData['debut'],
        ':fin'                  => $assuranceData['fin'],
        ':justificatif'         => $assuranceData['justificatif']
    ];

    if (!$stmt->execute($params)) {
        throw new PDOException("Échec de l’exécution de la requête d’insertion");
    }

    // ➓ Journalisation dans historique_action
    $lastId = $bdd->lastInsertId();
    $hist = "
      INSERT INTO historique_action
        (id_utilisateur, nom_action, nom_table, id_concerne,
         ancienne_valeur, nouvelle_valeur, adresse_ip)
      VALUES (?, 'Ajout patient', 'patient', ?, ?, ?, ?)
    ";
    $hstmt = $bdd->prepare($hist);
    $hstmt->execute([
        $_SESSION['id'],
        $lastId,
        null,
        json_encode([
            'nom'    => $nom,
            'prenom' => $prenom,
            'telephone' => $tel,
            'dossier'   => $numero_dossier
        ], JSON_UNESCAPED_UNICODE),
        $_SERVER['REMOTE_ADDR']
    ]);

    $_SESSION['success'] = "Patient ajouté avec succès";
    header('Location: ../../pages/patient.php');
    exit();

} catch (PDOException $e) {
    error_log("PDO Error: " . $e->getMessage());
    $_SESSION['error'] = "Erreur lors de l'enregistrement du patient";
    header('Location: ../../pages/patient.php');
    exit();
} finally {
    if (isset($db))    mysqli_close($db);
    if (isset($bdd))   $bdd = null;
}
