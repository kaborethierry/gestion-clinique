<?php
session_start();
if (empty($_SESSION['id']) || ($_SESSION['type_compte'] != "Super Administrateur")) {
    session_unset();
    session_destroy();
    header('Location:./../index.php?erreur=3');
    exit();
} else {
    // Vérifier que l'ID de l'utilisateur à supprimer est bien passé dans l'URL
    if (!isset($_GET['id_utilisateur'])) {
        header('Location:../../pages/utilisateur.php');
        exit();
    }

    include('connect_db_pdo.php');

    $id_utilisateur = $_GET['id_utilisateur'];

    // Récupération des informations de l'utilisateur avant suppression pour en garder une trace
    $stmtSelect = $bdd->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = ?");
    $stmtSelect->execute(array($id_utilisateur));
    $old_user = $stmtSelect->fetch(PDO::FETCH_ASSOC);

    if ($old_user) {
        // Suppression de l'utilisateur
        $stmtDelete = $bdd->prepare("DELETE FROM utilisateur WHERE id_utilisateur = ?");
        $stmtDelete->execute(array($id_utilisateur));

        // Préparation des informations pour l'historique
        $nom_action      = "Suppression utilisateur";
        $nom_table       = "utilisateur";
        $id_concerne     = $id_utilisateur;
        $ancienne_valeur = json_encode($old_user);
        $nouvelle_valeur = null;
        $adresse_ip      = $_SERVER['REMOTE_ADDR'];

        // Insertion dans la table historique_action avec le champ supprimer
        $stmtHist = $bdd->prepare("INSERT INTO historique_action (
            id_utilisateur,
            nom_action,
            nom_table,
            id_concerne,
            ancienne_valeur,
            nouvelle_valeur,
            adresse_ip,
            supprimer
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        $stmtHist->execute(array(
            $_SESSION['id'],
            $nom_action,
            $nom_table,
            $id_concerne,
            $ancienne_valeur,
            $nouvelle_valeur,
            $adresse_ip,
            'NON'
        ));
    }

    $bdd = null;
    $_SESSION['supr'] = 1;

    // Redirection selon le type de compte
    switch ($_SESSION['type_compte']) {
        case "Super Administrateur":
            header('Location:../../pages/utilisateur.php');
            break;
        case "Administrateur ACF":
            header('Location:../../pages/utilisateur_acf.php');
            break;
        case "Utilisateur AFD":
            header('Location:../../pages/utilisateur_afd.php');
            break;
        case "Utilisateur partenaire":
            header('Location:../../pages/utilisateur_partenaire.php');
            break;
    }
    exit();
}
?>
