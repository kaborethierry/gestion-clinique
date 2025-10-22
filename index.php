<!DOCTYPE html>
<html class="h-100" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Clinique - Login</title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="pages/images/favicon.png">
    <link href="pages/css/style.css" rel="stylesheet">
    <!-- Css SWEET ALERT 2 -->
    <link rel="stylesheet" href="pages/css/sweetalert2.min.css">
    <!-- /Css SWEET ALERT 2 -->
    <!-- Javascript SWEET ALERT 2 -->
    <script src="pages/js/sweetalert2.all.min.js"></script>
    <!-- Icônes Font Awesome -->
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
session_start();
if (isset($_SESSION['err']) && $_SESSION['err'] == 1){ ?>
    <script>
    Swal.fire(
        'Utilisateur ou mot de passe incorrect.',
        'Cliquez sur OK!',
        'error'
    )
    </script> 
<?php $_SESSION['err'] = 0; } ?>

<?php if (isset($_SESSION['err']) && $_SESSION['err'] == 2){ ?>
    <script>
    Swal.fire(
        'Utilisateur ou mot de passe vide.',
        'Cliquez sur OK!',
        'error'
    )
    </script> 
<?php $_SESSION['err'] = 0; } ?>

<!--*******************
    Preloader start
********************-->
<div id="preloader">
    <div class="loader">
        <svg class="circular" viewBox="25 25 50 50">
            <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="3" stroke-miterlimit="10" />
        </svg>
    </div>
</div>
<!--*******************
    Preloader end
********************-->

<div class="login-form-bg h-100">
    <div class="container h-100">
        <div class="row justify-content-center h-100">
            <div class="col-xl-6">
                <div class="form-input-content">
                    <div class="card login-form mb-0">
                        <div class="card-body pt-5">
                            <a class="text-center" href="index.php"> <h4>BI Health</h4></a>
    
                            <form class="mt-5 mb-4 login-input" action="api/modules/connection.php" method="POST">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="username" placeholder="Nom d'utilisateur" required>
                                </div>
                                <div class="form-group password-wrapper">
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Mot de passe" required>
                                    <button type="button" class="toggle-password" onclick="togglePassword()">
                                        <i id="toggleIcon" class="fa fa-eye"></i>
                                    </button>
                                </div>
                                <button class="btn login-form__btn submit w-100">Connexion</button>
                            </form>

                            <!-- Lien mot de passe oublié -->
                            <div class="text-center mb-3">
                                <a href="reset_password.php" class="text-primary" style="font-weight: 500;">
                                    Mot de passe oublié&nbsp;?
                                </a>
                            </div>
                            <!-- /Lien mot de passe oublié -->

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!--**********************************
    Scripts
***********************************-->
<script src="pages/plugins/common/common.min.js"></script>
<script src="pages/js/custom.min.js"></script>
<script src="pages/js/settings.js"></script>
<script src="pages/js/gleek.js"></script>
<script src="pages/js/styleSwitcher.js"></script>
<script>
function togglePassword() {
    const passwordField = document.getElementById("password");
    const toggleIcon = document.getElementById("toggleIcon");
    if (passwordField.type === "password") {
        passwordField.type = "text";
        toggleIcon.classList.remove("fa-eye");
        toggleIcon.classList.add("fa-eye-slash");
    } else {
        passwordField.type = "password";
        toggleIcon.classList.remove("fa-eye-slash");
        toggleIcon.classList.add("fa-eye");
    }
}
</script>
</body>
</html>
