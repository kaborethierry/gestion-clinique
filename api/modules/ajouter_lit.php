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


if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['id_chambre'], $_POST['numero_lit'], $_POST['statut'])) {

    // 1) Vérification en MySQLi pour éviter doublon (lit déjà existant dans la même chambre)
    include __DIR__ . '/connect_db.php'; // crée $db (mysqli)

    $id_chambre = (int) $_POST['id_chambre'];
    $numero_lit = mysqli_real_escape_string(
        $db,
        htmlspecialchars(trim($_POST['numero_lit']), ENT_QUOTES)
    );
    $statut = mysqli_real_escape_string(
        $db,
        htmlspecialchars($_POST['statut'], ENT_QUOTES)
    );

    $sqlCheck = "
        SELECT COUNT(*) AS cnt
          FROM lit
         WHERE id_chambre = $id_chambre
           AND numero_lit = '$numero_lit'
           AND supprimer = 'NON'
    ";
    $resCheck = mysqli_query($db, $sqlCheck);
    $row = mysqli_fetch_assoc($resCheck);
    $exists = (int) $row['cnt'];

    if ($exists === 0) {
        // 2) Insertion via PDO
        include __DIR__ . '/connect_db_pdo.php'; // crée $bdd (PDO)

        $insert = $bdd->prepare("
            INSERT INTO lit (id_chambre, numero_lit, statut)
            VALUES (?, ?, ?)
        ");
        $insert->execute([$id_chambre, $numero_lit, $statut]);

        mysqli_close($db);
        $bdd = null;

        $_SESSION['ajout_lit'] = 1;
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
