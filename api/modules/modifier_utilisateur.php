<?php
session_start();
if (empty($_SESSION['id']) || ($_SESSION['type_compte'] != "Super Administrateur")) {
    session_unset();
    session_destroy();
    header('Location:./../index.php?erreur=3');
    exit();
} else {
    // Vérification que les données nécessaires sont présentes
    if (isset($_POST['id_utilisateur']) && isset($_POST['nom']) && isset($_POST['prenom']) && isset($_POST['email']) && isset($_POST['username']) && isset($_POST['type_compte']) && isset($_POST['statut'])) {
        
        // Connexion MySQLi pour vérification d'unicité et sécurisation des données
        include('connect_db.php');
        
        $id          = mysqli_real_escape_string($db, htmlspecialchars($_POST['id_utilisateur'], ENT_QUOTES));
        $nom         = mysqli_real_escape_string($db, htmlspecialchars($_POST['nom'], ENT_QUOTES));
        $prenom      = mysqli_real_escape_string($db, htmlspecialchars($_POST['prenom'], ENT_QUOTES));
        $email       = mysqli_real_escape_string($db, htmlspecialchars($_POST['email'], ENT_QUOTES));
        $username    = mysqli_real_escape_string($db, htmlspecialchars($_POST['username'], ENT_QUOTES));
        $type_compte = mysqli_real_escape_string($db, htmlspecialchars($_POST['type_compte'], ENT_QUOTES));
        $statut      = mysqli_real_escape_string($db, htmlspecialchars($_POST['statut'], ENT_QUOTES));
        
        $poste       = isset($_POST['poste']) ? mysqli_real_escape_string($db, htmlspecialchars($_POST['poste'], ENT_QUOTES)) : "";
        $telephone   = isset($_POST['telephone']) ? mysqli_real_escape_string($db, htmlspecialchars($_POST['telephone'], ENT_QUOTES)) : "";
        $adresse     = isset($_POST['adresse']) ? mysqli_real_escape_string($db, htmlspecialchars($_POST['adresse'], ENT_QUOTES)) : "";
        
        // Remplacement des retours à la ligne par des balises HTML <br>
        $nom       = str_ireplace(array("\r\n", "\r", "\n"), '<br>', $nom);
        $prenom    = str_ireplace(array("\r\n", "\r", "\n"), '<br>', $prenom);
        $email     = str_ireplace(array("\r\n", "\r", "\n"), '<br>', $email);
        $username  = str_ireplace(array("\r\n", "\r", "\n"), '<br>', $username);
        $poste     = str_ireplace(array("\r\n", "\r", "\n"), '<br>', $poste);
        $telephone = str_ireplace(array("\r\n", "\r", "\n"), '<br>', $telephone);
        $adresse   = str_ireplace(array("\r\n", "\r", "\n"), '<br>', $adresse);
        
        // Vérification de l'unicité du nom d'utilisateur pour les autres utilisateurs
        $requete = "SELECT count(*) FROM utilisateur WHERE username = '$username' AND id_utilisateur != '$id'";
        $exec_requete = mysqli_query($db, $requete);
        $reponse = mysqli_fetch_array($exec_requete);
        $count = $reponse['count(*)'];
        
        if ($count == 0) {
            // Passage en PDO pour les opérations de mise à jour et d'historique
            include('connect_db_pdo.php');
            
            // Récupération des anciennes données avant update pour l’historique.
            $stmt_old = $bdd->prepare("SELECT nom, prenom, email, telephone, adresse, poste, username, type_compte, statut FROM utilisateur WHERE id_utilisateur = ?");
            $stmt_old->execute(array($id));
            $old_data = $stmt_old->fetch(PDO::FETCH_ASSOC);
            
            // Préparation de la mise à jour selon que le mot de passe est modifié ou non.
            $new_password = trim($_POST['passworde']);
            if (!empty($new_password)) {
                // Si un nouveau mot de passe est fourni, on le met à jour
                $new_password = mysqli_real_escape_string($db, htmlspecialchars($_POST['passworde'], ENT_QUOTES));
                $hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $bdd->prepare('UPDATE utilisateur SET nom = ?, prenom = ?, email = ?, telephone = ?, adresse = ?, poste = ?, username = ?, type_compte = ?, statut = ?, passworde = ? WHERE id_utilisateur = ?');
                $stmt->execute(array($nom, $prenom, $email, $telephone, $adresse, $poste, $username, $type_compte, $statut, $hash, $id));
            } else {
                // Sinon, on met à jour sans modifier le mot de passe
                $stmt = $bdd->prepare('UPDATE utilisateur SET nom = ?, prenom = ?, email = ?, telephone = ?, adresse = ?, poste = ?, username = ?, type_compte = ?, statut = ? WHERE id_utilisateur = ?');
                $stmt->execute(array($nom, $prenom, $email, $telephone, $adresse, $poste, $username, $type_compte, $statut, $id));
            }
            
            // Préparation des nouvelles données à enregistrer pour l'historique.
            $new_data = array(
                "nom" => $nom,
                "prenom" => $prenom,
                "email" => $email,
                "telephone" => $telephone,
                "adresse" => $adresse,
                "poste" => $poste,
                "username" => $username,
                "type_compte" => $type_compte,
                "statut" => $statut
            );
            
            // Encodage des anciennes et nouvelles valeurs en JSON
            $ancienne_valeur = json_encode($old_data);
            $nouvelle_valeur = json_encode($new_data);
            $nom_action = "Modification utilisateur"; // Libellé de l'action
            $nom_table  = "utilisateur";
            $id_concerne = $id;
            $adresse_ip = $_SERVER['REMOTE_ADDR'];
            
            // Insertion de l'action dans la table historique_action
            $stmt_hist = $bdd->prepare("INSERT INTO historique_action (id_utilisateur, nom_action, nom_table, id_concerne, ancienne_valeur, nouvelle_valeur, adresse_ip) VALUES (?,?,?,?,?,?,?)");
            $stmt_hist->execute(array($_SESSION['id'], $nom_action, $nom_table, $id_concerne, $ancienne_valeur, $nouvelle_valeur, $adresse_ip));
            
            mysqli_close($db);
            $bdd = null;
            $_SESSION['mod'] = 1;
            // Redirection en fonction du type de compte
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
        } else {
            mysqli_close($db);
            $_SESSION['imp'] = 1;
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
        }
    } else {
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
    }
}
?>
