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


if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset(
        $_POST['id_hosp'],
        $_POST['id_patient_mod'],
        $_POST['id_medecin_mod'],
        $_POST['id_lit_mod'],
        $_POST['date_entree_mod'],
        $_POST['motif_mod'],
        $_POST['observations_mod'],
        $_POST['date_sortie_mod'],
        $_POST['statut_mod']
    )) {

    // 1) Sécurisation en MySQLi
    include __DIR__ . '/connect_db.php'; // fournit $db (mysqli)

    $id_hosp          = (int) $_POST['id_hosp'];
    $id_patient_mod   = (int) $_POST['id_patient_mod'];
    $id_medecin_mod   = (int) $_POST['id_medecin_mod'];
    $id_lit_mod       = (int) $_POST['id_lit_mod'];
    $date_entree_mod  = mysqli_real_escape_string(
        $db,
        htmlspecialchars($_POST['date_entree_mod'], ENT_QUOTES)
    );
    $motif_mod        = mysqli_real_escape_string(
        $db,
        htmlspecialchars(trim($_POST['motif_mod']), ENT_QUOTES)
    );
    $observations_mod = mysqli_real_escape_string(
        $db,
        htmlspecialchars(trim($_POST['observations_mod']), ENT_QUOTES)
    );
    $date_sortie_mod  = mysqli_real_escape_string(
        $db,
        htmlspecialchars($_POST['date_sortie_mod'], ENT_QUOTES)
    );
    $statut_mod       = mysqli_real_escape_string(
        $db,
        htmlspecialchars($_POST['statut_mod'], ENT_QUOTES)
    );

    // Remplacement des retours à la ligne par <br>
    $motif_mod        = str_ireplace(["\r\n","\r","\n"], '<br>', $motif_mod);
    $observations_mod = str_ireplace(["\r\n","\r","\n"], '<br>', $observations_mod);

    // 2) Mise à jour via PDO et historique
    include __DIR__ . '/connect_db_pdo.php'; // fournit $bdd (PDO)

    // Récupérer l’enregistrement avant modification
    $stmtOld = $bdd->prepare("
        SELECT id_patient, id_medecin, id_lit, date_entree, motif,
               observations, date_sortie, statut
          FROM hospitalisation
         WHERE id_hosp = ?
    ");
    $stmtOld->execute([$id_hosp]);
    $oldData = $stmtOld->fetch(PDO::FETCH_ASSOC);

    // Mettre à jour le dossier d’hospitalisation
    $stmtUpd = $bdd->prepare("
        UPDATE hospitalisation
           SET id_patient       = ?,
               id_medecin       = ?,
               id_lit           = ?,
               date_entree      = ?,
               motif            = ?,
               observations     = ?,
               date_sortie      = ?,
               statut           = ?,
               date_modification = NOW()
         WHERE id_hosp = ?
    ");
    $stmtUpd->execute([
        $id_patient_mod,
        $id_medecin_mod,
        $id_lit_mod,
        $date_entree_mod,
        $motif_mod,
        $observations_mod,
        $date_sortie_mod ?: null,
        $statut_mod,
        $id_hosp
    ]);

    // 3) Gérer le statut des lits
    // Si le lit a changé, libère l’ancien et occupe le nouveau
    if ($oldData['id_lit'] != $id_lit_mod) {
        $freeOld = $bdd->prepare("
            UPDATE lit
               SET statut = 'Libre', date_modification = NOW()
             WHERE id_lit = ?
        ");
        $freeOld->execute([$oldData['id_lit']]);

        $occupyNew = $bdd->prepare("
            UPDATE lit
               SET statut = 'Occupé', date_modification = NOW()
             WHERE id_lit = ?
        ");
        $occupyNew->execute([$id_lit_mod]);
    }
    // Si on clôture l’hospitalisation, libère le lit
    elseif ($statut_mod === 'Terminé') {
        $free = $bdd->prepare("
            UPDATE lit
               SET statut = 'Libre', date_modification = NOW()
             WHERE id_lit = ?
        ");
        $free->execute([$id_lit_mod]);
    }

    // 4) Enregistrer l’action dans l’historique
    $ancienne_valeur = json_encode($oldData);
    $nouvelle_valeur = json_encode([
        'id_patient'   => $id_patient_mod,
        'id_medecin'   => $id_medecin_mod,
        'id_lit'       => $id_lit_mod,
        'date_entree'  => $date_entree_mod,
        'motif'        => $motif_mod,
        'observations' => $observations_mod,
        'date_sortie'  => $date_sortie_mod ?: null,
        'statut'       => $statut_mod
    ]);

    $stmtHist = $bdd->prepare("
        INSERT INTO historique_action
          (id_utilisateur, nom_action, nom_table, id_concerne,
           ancienne_valeur, nouvelle_valeur, adresse_ip)
        VALUES (?, 'Modification hospitalisation', 'hospitalisation', ?, ?, ?, ?)
    ");
    $stmtHist->execute([
        $_SESSION['id'],
        $id_hosp,
        $ancienne_valeur,
        $nouvelle_valeur,
        $_SERVER['REMOTE_ADDR']
    ]);

    // 5) Nettoyage et redirection
    mysqli_close($db);
    $bdd = null;
    $_SESSION['mod_hosp'] = 1;
} else {
    // Accès direct ou données manquantes
    $_SESSION['imp_hosp'] = 1;
}

header('Location: ../../pages/hospitalisation.php');
exit();
