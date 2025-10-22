<?php
// api/modules/ajouter_hospitalisation.php

session_start();

// ➊ Contrôle d’accès : Super Administrateur, Secretaire et Medecin
$allowedRoles = ['Super Administrateur', 'Secretaire', 'Medecin'];
if (empty($_SESSION['id'])
    || ! in_array($_SESSION['type_compte'], $allowedRoles, true)
) {
    session_unset();
    session_destroy();
    header('Location: ../index.php?erreur=3');
    exit();
}

// … le reste de votre traitement d’ajout d’hospitalisation …

// Vérifier que tous les champs obligatoires sont présents
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['id_patient'], $_POST['id_medecin'], $_POST['id_lit'], $_POST['date_entree'], $_POST['motif'])) {

    // 1) Connexion MySQLi pour sécurisation (connect_db.php doit définir $db)
    include __DIR__ . '/connect_db.php';

    $id_patient  = (int) $_POST['id_patient'];
    $id_medecin  = (int) $_POST['id_medecin'];
    $id_lit      = (int) $_POST['id_lit'];

    // Nettoyage et échappement
    $date_entree   = mysqli_real_escape_string($db, htmlspecialchars($_POST['date_entree'], ENT_QUOTES));
    $motif         = mysqli_real_escape_string($db, htmlspecialchars(trim($_POST['motif']), ENT_QUOTES));
    $observations  = isset($_POST['observations'])
                     ? mysqli_real_escape_string($db, htmlspecialchars(trim($_POST['observations']), ENT_QUOTES))
                     : "";

    // Conversion des retours à la ligne en <br>
    $motif        = str_ireplace(["\r\n","\r","\n"], '<br>', $motif);
    $observations = str_ireplace(["\r\n","\r","\n"], '<br>', $observations);

    // 2) Insertion via PDO (connect_db_pdo.php doit définir $bdd)
    include __DIR__ . '/connect_db_pdo.php';

    try {
        $bdd->beginTransaction();

        // Insère l’hospitalisation
        $stmt = $bdd->prepare("
          INSERT INTO hospitalisation
            (id_patient, id_medecin, date_entree, id_lit, motif, observations, statut)
          VALUES (?, ?, ?, ?, ?, ?, 'En cours')
        ");
        $stmt->execute([$id_patient, $id_medecin, $date_entree, $id_lit, $motif, $observations]);

        // Récupère l’ID généré
        $newId = $bdd->lastInsertId();

        // Marquer le lit comme occupé
        $upd = $bdd->prepare("
          UPDATE lit
             SET statut = 'Occupé', date_modification = NOW()
           WHERE id_lit = ?
        ");
        $upd->execute([$id_lit]);

        // Historique de l’action
        $ancienne_valeur = null;
        $nouvelle_valeur = json_encode([
          'id_patient'   => $id_patient,
          'id_medecin'   => $id_medecin,
          'id_lit'       => $id_lit,
          'date_entree'  => $date_entree,
          'motif'        => $motif,
          'observations' => $observations,
          'statut'       => 'En cours'
        ]);
        $stmtHist = $bdd->prepare("
          INSERT INTO historique_action
            (id_utilisateur, nom_action, nom_table, id_concerne,
             ancienne_valeur, nouvelle_valeur, adresse_ip)
          VALUES (?, 'Ajout hospitalisation', 'hospitalisation', ?, ?, ?, ?)
        ");
        $stmtHist->execute([
          $_SESSION['id'],
          $newId,
          $ancienne_valeur,
          $nouvelle_valeur,
          $_SERVER['REMOTE_ADDR']
        ]);

        $bdd->commit();
        $_SESSION['ajout_hosp'] = 1;

    } catch (Exception $e) {
        $bdd->rollBack();
        $_SESSION['imp_hosp'] = 1;
    }

    // Fermer connexions
    mysqli_close($db);
    $bdd = null;

} else {
    // Accès direct ou données manquantes
    $_SESSION['imp_hosp'] = 1;
}

header('Location: ../../pages/hospitalisation.php');
exit();
