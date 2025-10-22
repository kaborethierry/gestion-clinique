<?php
// reset_password.php
session_start();
?>

<!DOCTYPE html>
<html class="h-100" lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Réinitialisation du mot de passe</title>

    <link rel="icon" type="image/png" sizes="16x16" href="pages/images/favicon.png">
    <link href="pages/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="pages/css/sweetalert2.min.css">
    <script src="pages/js/sweetalert2.all.min.js"></script>

    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .password-wrapper {
            position: relative;
        }
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #666;
        }
        .toggle-password:focus {
            outline: none;
        }
    </style>
</head>

<body class="h-100">
<?php
// Messages SweetAlert selon session
if (!empty($_SESSION['reset_msg'])) {
    $type = $_SESSION['reset_msg']['type'];
    $text = $_SESSION['reset_msg']['text'];
    echo "<script>Swal.fire('$text','','{$type}')</script>";
    unset($_SESSION['reset_msg']);
}
?>

<div class="login-form-bg h-100">
    <div class="container h-100">
        <div class="row justify-content-center h-100">
            <div class="col-xl-6">
                <div class="form-input-content">
                    <div class="card login-form mb-0">
                        <div class="card-body pt-5">
                            <a class="text-center" href="index.php"><h4>Réinitialisation du mot de passe</h4></a>

                            <form class="mt-5 mb-4 login-input" action="" method="POST">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="username" placeholder="Nom d'utilisateur" required>
                                </div>

                                <div class="form-group password-wrapper">
                                    <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Nouveau mot de passe" required>
                                    <button type="button" class="toggle-password" onclick="togglePassword('new_password','toggleIcon1')">
                                        <i id="toggleIcon1" class="fa fa-eye"></i>
                                    </button>
                                </div>

                                <div class="form-group password-wrapper">
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirmez le mot de passe" required>
                                    <button type="button" class="toggle-password" onclick="togglePassword('confirm_password','toggleIcon2')">
                                        <i id="toggleIcon2" class="fa fa-eye"></i>
                                    </button>
                                </div>

                                <button class="btn login-form__btn submit w-100" type="submit" name="reset">Réinitialiser</button>
                            </form>

                            <div class="text-center mb-3">
                                <a href="index.php" class="text-primary" style="font-weight: 500;">Retour à la connexion</a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="pages/plugins/common/common.min.js"></script>
<script src="pages/js/custom.min.js"></script>
<script>
function togglePassword(fieldId, iconId) {
    const field = document.getElementById(fieldId);
    const icon  = document.getElementById(iconId);
    if (field.type === "password") {
        field.type = "text";
        icon.classList.replace("fa-eye", "fa-eye-slash");
    } else {
        field.type = "password";
        icon.classList.replace("fa-eye-slash", "fa-eye");
    }
}
</script>
</body>
</html>

<?php
// --- TRAITEMENT DU FORMULAIRE ---
if (isset($_POST['reset'])) {
    include __DIR__ . '/api/modules/connect_db.php';

    $username         = trim($_POST['username']);
    $new_password     = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validation
    if ($username === '' || $new_password === '' || $confirm_password === '') {
        $_SESSION['reset_msg'] = ['type'=>'error','text'=>'Tous les champs sont obligatoires.'];
        header("Location: reset_password.php");
        exit();
    }
    if ($new_password !== $confirm_password) {
        $_SESSION['reset_msg'] = ['type'=>'error','text'=>'Les mots de passe ne correspondent pas.'];
        header("Location: reset_password.php");
        exit();
    }

    // Vérifier existence utilisateur
    $username_sql = mysqli_real_escape_string($db, htmlspecialchars($username, ENT_QUOTES));
    $check_sql = "SELECT id_utilisateur FROM utilisateur WHERE username = '{$username_sql}' LIMIT 1";
    $res = mysqli_query($db, $check_sql);
    if (mysqli_num_rows($res) === 0) {
        $_SESSION['reset_msg'] = ['type'=>'error','text'=>'Utilisateur introuvable.'];
        header("Location: reset_password.php");
        exit();
    }

    // Hash sécurisé
    $hashed = password_hash($new_password, PASSWORD_DEFAULT);

    // Mise à jour
    $update_sql = "UPDATE utilisateur SET passworde = '{$hashed}' WHERE username = '{$username_sql}' LIMIT 1";
    if (mysqli_query($db, $update_sql)) {
        $_SESSION['reset_msg'] = ['type'=>'success','text'=>'Mot de passe réinitialisé avec succès.'];
    } else {
        $_SESSION['reset_msg'] = ['type'=>'error','text'=>'Erreur lors de la mise à jour.'];
    }
    mysqli_close($db);
    header("Location: reset_password.php");
    exit();
}
?>
