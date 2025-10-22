<?php
session_start();
if (empty($_SESSION['id']) || ($_SESSION['type_compte'] != "Super Administrateur")) {
    session_unset();
    session_destroy();
    header('Location:./../index.php?erreur=3');
    exit();
} else {
    if (isset($_POST['nom']) && isset($_POST['prenom']) && isset($_POST['email']) && isset($_POST['username']) && isset($_POST['passworde']) && isset($_POST['type_compte']) && isset($_POST['statut'])) {
        include('connect_db.php');
        
        // Sécurisation des données
        $nom         = mysqli_real_escape_string($db, htmlspecialchars($_POST['nom'], ENT_QUOTES));
        $prenom      = mysqli_real_escape_string($db, htmlspecialchars($_POST['prenom'], ENT_QUOTES));
        $email       = mysqli_real_escape_string($db, htmlspecialchars($_POST['email'], ENT_QUOTES));
        $username    = mysqli_real_escape_string($db, htmlspecialchars($_POST['username'], ENT_QUOTES));
        $passworde   = mysqli_real_escape_string($db, htmlspecialchars($_POST['passworde'], ENT_QUOTES));
        $type_compte = mysqli_real_escape_string($db, htmlspecialchars($_POST['type_compte'], ENT_QUOTES));
        $statut      = mysqli_real_escape_string($db, htmlspecialchars($_POST['statut'], ENT_QUOTES));
        
        $poste       = isset($_POST['poste']) ? mysqli_real_escape_string($db, htmlspecialchars($_POST['poste'], ENT_QUOTES)) : "";
        $telephone   = isset($_POST['telephone']) ? mysqli_real_escape_string($db, htmlspecialchars($_POST['telephone'], ENT_QUOTES)) : "";
        $adresse     = isset($_POST['adresse']) ? mysqli_real_escape_string($db, htmlspecialchars($_POST['adresse'], ENT_QUOTES)) : "";
        
        // Remplacement des retours à la ligne
        $nom       = str_ireplace(array("\r\n", "\r", "\n"), '<br>', $nom);
        $prenom    = str_ireplace(array("\r\n", "\r", "\n"), '<br>', $prenom);
        $email     = str_ireplace(array("\r\n", "\r", "\n"), '<br>', $email);
        $username  = str_ireplace(array("\r\n", "\r", "\n"), '<br>', $username);
        $poste     = str_ireplace(array("\r\n", "\r", "\n"), '<br>', $poste);
        $telephone = str_ireplace(array("\r\n", "\r", "\n"), '<br>', $telephone);
        $adresse   = str_ireplace(array("\r\n", "\r", "\n"), '<br>', $adresse);
        
        // Vérification de l'unicité du username
        $requete = "SELECT count(*) FROM utilisateur WHERE username = '$username'";
        $exec_requete = mysqli_query($db, $requete);
        $reponse = mysqli_fetch_array($exec_requete);
        $count = $reponse['count(*)'];
        
        if ($count == 0) {
            // Utilisation de PDO pour l'insertion de l'utilisateur
            include('connect_db_pdo.php');
            $hash = password_hash($passworde, PASSWORD_DEFAULT);
            $requete = $bdd->prepare('INSERT INTO utilisateur (nom, prenom, poste, telephone, email, adresse, username, passworde, type_compte, date_inscription, statut) VALUES (?,?,?,?,?,?,?,?,?, NOW(), ?)');
            $requete->execute(array($nom, $prenom, $poste, $telephone, $email, $adresse, $username, $hash, $type_compte, $statut));
            
            // Récupère l'ID du nouvel enregistrement
            $id_new = $bdd->lastInsertId();
            
            // Enregistrement de l'action dans l'historique
            $nom_action    = "Ajout utilisateur"; // Action réalisée
            $nom_table     = "utilisateur";
            $id_concerne   = $id_new;
            $ancienne_valeur = null; // Rien à enregistrer en cas d'ajout
            // Enregistre quelques informations sur le nouvel utilisateur en JSON
            $nouvelle_valeur = json_encode(array(
                "nom" => $nom,
                "prenom" => $prenom,
                "poste" => $poste,
                "telephone" => $telephone,
                "email" => $email,
                "adresse" => $adresse,
                "username" => $username,
                "type_compte" => $type_compte,
                "statut" => $statut
            ));
            $adresse_ip    = $_SERVER['REMOTE_ADDR'];
            
            // Insertion dans la table historique_action
            $requeteHist = $bdd->prepare("INSERT INTO historique_action (id_utilisateur, nom_action, nom_table, id_concerne, ancienne_valeur, nouvelle_valeur, adresse_ip) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $requeteHist->execute(array($_SESSION['id'], $nom_action, $nom_table, $id_concerne, $ancienne_valeur, $nouvelle_valeur, $adresse_ip));
            
            mysqli_close($db);
            $bdd = null;
            $_SESSION['ajout'] = 1;
           
            header('Location:../../pages/utilisateur.php');
                    
        } else {
            mysqli_close($db);
            $_SESSION['imp'] = 1;
            header('Location:../../pages/utilisateur.php');
        }
    } else {
        header('Location:../../pages/utilisateur.php');
    }
}
?>
