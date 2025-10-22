<?php
// api/modules/modifier_patient.php
// Met à jour un patient existant (infos persos + assurance)

// Affichage des erreurs en dev (retirer en prod)
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

// ➋ Vérification des champs obligatoires
if (!isset(
        $_POST['id_patient'],
        $_POST['nom'],
        $_POST['prenom'],
        $_POST['telephone'],
        $_POST['sexe'],
        $_POST['assurance_statut']
    )
) {
    header('Location: ../../pages/patient.php');
    exit();
}

// ➌ Connexion MySQLi pour échappement initial
require __DIR__ . '/connect_db.php';
function esc($db, $str) {
    return $str === null
        ? null
        : mysqli_real_escape_string($db, htmlspecialchars(trim($str), ENT_QUOTES));
}

// ➍ Récupération et échappement des champs patient
$id                   = esc($db, $_POST['id_patient']);
$nom                  = esc($db, $_POST['nom']);
$prenom               = esc($db, $_POST['prenom']);
$telephone            = esc($db, $_POST['telephone']);
$sexe                 = esc($db, $_POST['sexe']);

$date_naissance       = !empty($_POST['date_naissance'])
    ? esc($db, $_POST['date_naissance'])
    : null;
$lieu_naissance       = !empty($_POST['lieu_naissance'])
    ? esc($db, $_POST['lieu_naissance'])
    : null;
$nationalite          = !empty($_POST['nationalite'])
    ? esc($db, $_POST['nationalite'])
    : null;
$etat_civil           = !empty($_POST['etat_civil'])
    ? esc($db, $_POST['etat_civil'])
    : null;
$adresse              = !empty($_POST['adresse'])
    ? esc($db, $_POST['adresse'])
    : null;
$ville                = !empty($_POST['ville'])
    ? esc($db, $_POST['ville'])
    : null;
$quartier             = !empty($_POST['quartier'])
    ? esc($db, $_POST['quartier'])
    : null;
$telephone_secondaire = !empty($_POST['telephone_secondaire'])
    ? esc($db, $_POST['telephone_secondaire'])
    : null;
$email                = !empty($_POST['email'])
    ? esc($db, $_POST['email'])
    : null;
$profession           = !empty($_POST['profession'])
    ? esc($db, $_POST['profession'])
    : null;
$groupe_sanguin       = !empty($_POST['groupe_sanguin'])
    ? esc($db, $_POST['groupe_sanguin'])
    : null;
$situation_handicap   = isset($_POST['situation_handicap'])
    ? (int) $_POST['situation_handicap']
    : 0;
$allergie             = !empty($_POST['allergie'])
    ? esc($db, $_POST['allergie'])
    : null;
$antecedents_medicaux = !empty($_POST['antecedents_medicaux'])
    ? esc($db, $_POST['antecedents_medicaux'])
    : null;
$personne_contact_nom = !empty($_POST['personne_contact_nom'])
    ? esc($db, $_POST['personne_contact_nom'])
    : null;
$personne_contact_lien = !empty($_POST['personne_contact_lien'])
    ? esc($db, $_POST['personne_contact_lien'])
    : null;
$personne_contact_tel  = !empty($_POST['personne_contact_tel'])
    ? esc($db, $_POST['personne_contact_tel'])
    : null;

// Poids & tension
$poids                = isset($_POST['poids']) && $_POST['poids'] !== ''
    ? esc($db, $_POST['poids'])
    : null;
$tension_arterielle   = isset($_POST['tension']) && $_POST['tension'] !== ''
    ? esc($db, $_POST['tension'])
    : null;

// ➎ Gestion upload photo
$photo_new = null;
if (!empty($_FILES['photo']['tmp_name'])
    && $_FILES['photo']['error'] === UPLOAD_ERR_OK
) {
    $ext   = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    $allow = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($ext, $allow, true)) {
        $filePhoto = uniqid('pat_', true) . '.' . $ext;
        $dest      = __DIR__ . '/../../uploads/patients/' . $filePhoto;
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
            $photo_new = $filePhoto;
            // Suppression de l’ancienne photo
            $resOld = mysqli_query($db, "SELECT photo FROM patient WHERE id = $id");
            if ($resOld && $rowOld = mysqli_fetch_assoc($resOld)) {
                $old = $rowOld['photo'];
                $pathOld = __DIR__ . '/../../uploads/patients/' . $old;
                if (!empty($old) && file_exists($pathOld)) {
                    unlink($pathOld);
                }
            }
        }
    }
}

// ➏ Gestion des données assurance
$assurance_statut      = esc($db, $_POST['assurance_statut']);
if ($assurance_statut === 'Assuré') {
    $assurance_compagnie   = !empty($_POST['assurance_compagnie'])
        ? esc($db, $_POST['assurance_compagnie'])
        : null;
    $numero_police         = !empty($_POST['numero_police'])
        ? esc($db, $_POST['numero_police'])
        : null;
    $taux_couverture       = isset($_POST['taux_couverture'])
        && $_POST['taux_couverture'] !== ''
        ? esc($db, $_POST['taux_couverture'])
        : null;
    $type_couverture       = !empty($_POST['type_couverture'])
        ? esc($db, $_POST['type_couverture'])
        : null;
    $date_debut_couverture = !empty($_POST['date_debut_couverture'])
        ? esc($db, $_POST['date_debut_couverture'])
        : null;
    $date_fin_couverture   = !empty($_POST['date_fin_couverture'])
        ? esc($db, $_POST['date_fin_couverture'])
        : null;

    // Upload justificatif assurance
    $justif_new = null;
    if (!empty($_FILES['justificatif_assurance']['tmp_name'])
        && $_FILES['justificatif_assurance']['error'] === UPLOAD_ERR_OK
    ) {
        $ext2   = strtolower(pathinfo($_FILES['justificatif_assurance']['name'], PATHINFO_EXTENSION));
        $allow2 = ['jpg', 'jpeg', 'png', 'pdf'];
        if (in_array($ext2, $allow2, true)) {
            $fileJust = uniqid('asur_', true) . '.' . $ext2;
            $dest2     = __DIR__ . '/../../uploads/assurances/' . $fileJust;
            if (move_uploaded_file($_FILES['justificatif_assurance']['tmp_name'], $dest2)) {
                $justif_new = $fileJust;
                // Suppression de l’ancien justificatif
                $resJ = mysqli_query($db, "SELECT justificatif_assurance FROM patient WHERE id = $id");
                if ($resJ && $rowJ = mysqli_fetch_assoc($resJ)) {
                    $oldJ = $rowJ['justificatif_assurance'];
                    $pathJ = __DIR__ . '/../../uploads/assurances/' . $oldJ;
                    if (!empty($oldJ) && file_exists($pathJ)) {
                        unlink($pathJ);
                    }
                }
            }
        }
    }
} else {
    $assurance_compagnie   = null;
    $numero_police         = null;
    $taux_couverture       = null;
    $type_couverture       = null;
    $date_debut_couverture = null;
    $date_fin_couverture   = null;
    $justif_new            = null;
}

// ➐ Passage à PDO pour l’UPDATE
require __DIR__ . '/connect_db_pdo.php';

try {
    // Récupération des anciennes données pour historique
    $stmtOld = $bdd->prepare("SELECT * FROM patient WHERE id = ?");
    $stmtOld->execute([$id]);
    $oldData = $stmtOld->fetch(PDO::FETCH_ASSOC);

    // Construction de la requête UPDATE
    $sql = "UPDATE patient SET
        nom=?, prenom=?, sexe=?, date_naissance=?, lieu_naissance=?, nationalite=?, etat_civil=?,
        adresse=?, ville=?, quartier=?, telephone=?, telephone_secondaire=?, email=?, profession=?,
        groupe_sanguin=?, poids=?, tension_arterielle=?, situation_handicap=?, allergie=?, antecedents_medicaux=?,
        personne_contact_nom=?, personne_contact_lien=?, personne_contact_tel=?,
        assurance_statut=?, assurance_compagnie=?, numero_police=?, taux_couverture=?, type_couverture=?,
        date_debut_couverture=?, date_fin_couverture=?";
    $params = [
        $nom, $prenom, $sexe, $date_naissance, $lieu_naissance, $nationalite, $etat_civil,
        $adresse, $ville, $quartier, $telephone, $telephone_secondaire, $email, $profession,
        $groupe_sanguin, $poids, $tension_arterielle, $situation_handicap, $allergie, $antecedents_medicaux,
        $personne_contact_nom, $personne_contact_lien, $personne_contact_tel,
        $assurance_statut, $assurance_compagnie, $numero_police, $taux_couverture, $type_couverture,
        $date_debut_couverture, $date_fin_couverture
    ];

    if ($photo_new !== null) {
        $sql .= ", photo=?";
        $params[] = $photo_new;
    }
    if ($justif_new !== null) {
        $sql .= ", justificatif_assurance=?";
        $params[] = $justif_new;
    }
    $sql .= " WHERE id=?";
    $params[] = $id;

    // Exécution de l'UPDATE
    $stmt = $bdd->prepare($sql);
    $stmt->execute($params);

    // Historique de modification
    $newData = [
        'nom'               => $nom,
        'prenom'            => $prenom,
        'telephone'         => $telephone,
        'sexe'              => $sexe,
        'assurance_statut'  => $assurance_statut,
        'assurance_compagnie' => $assurance_compagnie
    ];
    $hist = $bdd->prepare("
        INSERT INTO historique_action (
            id_utilisateur, nom_action, nom_table, id_concerne,
            ancienne_valeur, nouvelle_valeur, adresse_ip
        ) VALUES (?, 'Modification patient', 'patient', ?, ?, ?, ?)
    ");
    $hist->execute([
        $_SESSION['id'],
        $id,
        json_encode($oldData, JSON_UNESCAPED_UNICODE),
        json_encode($newData, JSON_UNESCAPED_UNICODE),
        $_SERVER['REMOTE_ADDR']
    ]);

    $_SESSION['mod'] = 1;
    header('Location: ../../pages/patient.php');
    exit();

} catch (PDOException $e) {
    $_SESSION['imp'] = 1;
    $_SESSION['message_erreur'] = $e->getMessage();
    header('Location: ../../pages/patient.php');
    exit();

} finally {
    if (isset($db))  mysqli_close($db);
    if (isset($bdd)) $bdd = null;
}
