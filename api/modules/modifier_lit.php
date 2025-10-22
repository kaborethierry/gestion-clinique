<?php
// api/modules/ajouter_lit.php

session_start();

// ➊ Contrôle d’accès : Super Administrateur, Secretaire et Medecin
if (empty($_SESSION['id'])
    || ! in_array($_SESSION['type_compte'], [
         "Super Administrateur",
         "Secretaire",
         "Medecin"
       ], true)
) {
    session_unset();
    session_destroy();
    header('Location: ../index.php?erreur=3');
    exit();
}

// … le reste de votre traitement d’ajout de lit …


if ($_SERVER['REQUEST_METHOD']==='POST'
    && isset($_POST['id_lit'], $_POST['id_chambre_modif'], $_POST['numero_lit_modif'], $_POST['statut_modif'])) {

    // 1) Vérification d’unicité en MySQLi
    include __DIR__ . '/connect_db.php'; // fournit $db (mysqli)

    $id_lit      = (int) $_POST['id_lit'];
    $id_chambre  = (int) $_POST['id_chambre_modif'];
    $numero_lit  = mysqli_real_escape_string(
        $db,
        htmlspecialchars(trim($_POST['numero_lit_modif']), ENT_QUOTES)
    );
    $statut      = mysqli_real_escape_string(
        $db,
        htmlspecialchars($_POST['statut_modif'], ENT_QUOTES)
    );

    $sqlCheck = "
      SELECT COUNT(*) AS cnt
        FROM lit
       WHERE id_chambre = $id_chambre
         AND numero_lit = '$numero_lit'
         AND supprimer   = 'NON'
         AND id_lit     != $id_lit
    ";
    $resCheck = mysqli_query($db, $sqlCheck);
    $row      = mysqli_fetch_assoc($resCheck);
    $exists   = (int) $row['cnt'];

    if ($exists === 0) {
        // 2) Mise à jour via PDO + historique
        include __DIR__ . '/connect_db_pdo.php'; // fournit $bdd (PDO)

        // Récupérer les anciennes valeurs pour l'historique
        $stmtOld = $bdd->prepare("
          SELECT id_chambre, numero_lit, statut
            FROM lit
           WHERE id_lit = ?
        ");
        $stmtOld->execute([$id_lit]);
        $oldData = $stmtOld->fetch(PDO::FETCH_ASSOC);

        // Mettre à jour le lit
        $stmtUpd = $bdd->prepare("
          UPDATE lit
             SET id_chambre       = ?,
                 numero_lit       = ?,
                 statut           = ?,
                 date_modification = NOW()
           WHERE id_lit = ?
        ");
        $stmtUpd->execute([$id_chambre, $numero_lit, $statut, $id_lit]);

        // Préparer les nouvelles valeurs pour l'historique
        $newData = [
            'id_chambre'  => $id_chambre,
            'numero_lit'  => $numero_lit,
            'statut'      => $statut
        ];

        // Inscrire l'action dans historique_action
        $ancienne_valeur  = json_encode($oldData);
        $nouvelle_valeur  = json_encode($newData);
        $nom_action       = "Modification lit";
        $nom_table        = "lit";
        $id_concerne      = $id_lit;
        $adresse_ip       = $_SERVER['REMOTE_ADDR'];

        $stmtHist = $bdd->prepare("
          INSERT INTO historique_action
            (id_utilisateur, nom_action, nom_table, id_concerne,
             ancienne_valeur, nouvelle_valeur, adresse_ip)
          VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmtHist->execute([
            $_SESSION['id'],
            $nom_action,
            $nom_table,
            $id_concerne,
            $ancienne_valeur,
            $nouvelle_valeur,
            $adresse_ip
        ]);

        mysqli_close($db);
        $bdd = null;

        $_SESSION['mod_lit'] = 1;

    } else {
        // Doublon détecté
        mysqli_close($db);
        $_SESSION['imp_lit'] = 1;
    }
} else {
    // Accès direct ou données manquantes
    $_SESSION['imp_lit'] = 1;
}

header('Location: ../../pages/lit.php');
exit();
