<?php
// connection.php
// PAGE DE TRAITEMENT D'UNE CONNEXION

session_start();
$_SESSION['password'] = 0;

// Vérification des champs username et password
if (isset($_POST['username'], $_POST['password'])) {

    // Connexion MySQLi pour l’assainissement
    include __DIR__ . '/connect_db.php';

    // Assainissement des entrées
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $username = mysqli_real_escape_string($db, htmlspecialchars($username, ENT_QUOTES));
    $password = mysqli_real_escape_string($db, htmlspecialchars($password, ENT_QUOTES));

    if ($username !== '' && $password !== '') {
        // On récupère le hash du mot de passe
        $sql  = "SELECT COUNT(*) AS cnt, passworde 
                 FROM utilisateur 
                 WHERE username = '{$username}'";
        $res  = mysqli_query($db, $sql);
        $row  = mysqli_fetch_assoc($res);
        $count = (int)($row['cnt'] ?? 0);

        // Vérification du password
        if ($count === 1 && password_verify($password, $row['passworde'])) {

            // Connexion PDO pour récupérer les infos utilisateur
            include __DIR__ . '/connect_db_pdo.php';

            $stmt = $bdd->prepare("
                SELECT id_utilisateur, username, type_compte 
                  FROM utilisateur 
                 WHERE username = ? 
                 LIMIT 1
            ");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Initialisation des variables de session
                $_SESSION['id']           = $user['id_utilisateur'];
                $_SESSION['username']     = $user['username'];
                $_SESSION['type_compte']  = $user['type_compte'];

                // Flags pour SweetAlert
                $_SESSION['supr']  = 0;
                $_SESSION['mod']   = 0;
                $_SESSION['ajout'] = 0;

                // Fermeture des connexions
                mysqli_close($db);
                $bdd = null;

                // Redirection vers la page principale
                header('Location: ../../pages/statistique.php');
                exit();
            }

            // En cas de problème de récupération utilisateur
            mysqli_close($db);
            $bdd = null;
            $_SESSION['err'] = 1;
            header('Location: ../../index.php');
            exit();

        } else {
            // Identifiants incorrects
            mysqli_close($db);
            $_SESSION['err'] = 1;
            header('Location: ../../index.php');
            exit();
        }

    } else {
        // Champs vides
        mysqli_close($db);
        $_SESSION['err'] = 2;
        header('Location: ../../index.php');
        exit();
    }

} else {
    // Accès direct sans POST
    header('Location: ../../index.php');
    exit();
}
?>
