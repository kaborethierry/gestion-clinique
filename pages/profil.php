<?php
session_start();
if (empty($_SESSION['id']) || 
    !in_array($_SESSION['type_compte'], [
        "Super Administrateur", 
        "Secretaire", 
        "Medecin", 
        "Laborantin", 
        "Comptable"
    ])) {
    session_unset();
    session_destroy();
    header('Location: ../index.php?erreur=3');
    exit();
}

require __DIR__ . '/../api/modules/connect_db_pdo.php';

// Récupérer les infos de l’utilisateur connecté
$requete = $bdd->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = ?");
$requete->execute([$_SESSION['id']]);
$profil = $requete->fetch(PDO::FETCH_ASSOC);
$bdd = null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Clinique - Mon profil</title>
  <?php include("inclusion_haut.php"); ?>
  <style>
    /* Boutons carrés avec coins légèrement arrondis */
    .btn-action {
      border-radius: 4px; /* coins peu arrondis */
      padding: 12px 30px;
      font-size: 1rem;
    }
    .btn-primary.btn-action {
      background: linear-gradient(135deg, #0069d9 0%, #0056b3 100%);
      border: none;
    }
    .btn-warning.btn-action {
      background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
      border: none;
    }
    .btn-action:hover {
      opacity: 0.9;
    }
  </style>
</head>
<body>
  <?php include("loader.php"); ?>
  <?php include("alert_profil.php"); ?>

  <div id="main-wrapper">
    <?php include("entete.php"); ?>
    <?php
switch ($_SESSION['type_compte']) {
  case "Super Administrateur":
    include("menu.php"); // ou "menu_superadmin.php" si tu veux le distinguer
    break;
  case "Secretaire":
    include("menu_secretaire.php");
    break;
  case "Medecin":
    include("menu_medecin.php");
    break;
  case "Laborantin":
    include("menu_laborantin.php");
    break;
  case "Comptable":
    include("menu_comptable.php");
    break;
  default:
    // Rôle inconnu : sécurité minimale ou redirection
    include("menu.php"); // ou rien du tout selon ton choix
    break;
}
?>


    <div class="content-body">
      <div class="row page-titles mx-0">
        <div class="col p-md-0">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Clinique</a></li>
            <li class="breadcrumb-item active"><a href="#">Mon profil</a></li>
          </ol>
        </div>
      </div>

      <div class="container-fluid">
        <div class="row justify-content-center">
          <div class="col-xl-6 col-md-8">
            <div class="card">
              <div class="card-header">
                <h4 class="card-title mb-0">Mes informations</h4>
              </div>
              <div class="card-body text-center">
                <img src="img/profil.png" alt="Profil" class="img-fluid mb-4" style="width:30%;">

                <ul class="list-group text-left mb-4">
                  <li class="list-group-item"><strong>Nom :</strong> <?= htmlspecialchars($profil['nom']) ?></li>
                  <li class="list-group-item"><strong>Prénom(s) :</strong> <?= htmlspecialchars($profil['prenom']) ?></li>
                  <li class="list-group-item"><strong>Téléphone :</strong> <?= htmlspecialchars($profil['telephone']) ?></li>
                  <li class="list-group-item"><strong>Email :</strong> <?= htmlspecialchars($profil['email']) ?></li>
                  <li class="list-group-item"><strong>Nom d'utilisateur :</strong> <?= htmlspecialchars($profil['username']) ?></li>
                  <li class="list-group-item"><strong>Poste :</strong> <?= htmlspecialchars($profil['poste']) ?></li>
                  <li class="list-group-item"><strong>Type de compte :</strong> <?= htmlspecialchars($profil['type_compte']) ?></li>
                </ul>

                <div class="row">
                  <div class="col-sm-6 mb-3">
                    <button 
                      type="button" 
                      class="btn btn-primary btn-action btn-block" 
                      data-toggle="modal" 
                      data-target="#modifier_profil">
                      Modifier mon profil
                    </button>
                  </div>
                  <div class="col-sm-6 mb-3">
                    <button 
                      type="button" 
                      class="btn btn-warning btn-action btn-block" 
                      data-toggle="modal" 
                      data-target="#modifier_password">
                      Modifier mot de passe
                    </button>
                  </div>
                </div>
              </div> <!-- /.card-body -->
            </div> <!-- /.card -->
          </div> <!-- /.col -->
        </div> <!-- /.row -->
      </div> <!-- /.container-fluid -->
    </div> <!-- /.content-body -->

    <?php include("footer.php"); ?>
  </div> <!-- /#main-wrapper -->

  <?php include("inclusion_bas.php"); ?>
  <script src="Datatables/data_table_profil.js"></script>
  <?php include("modals/modal_profil.php"); ?>
  <?php include("modals/modal_deconnexion.php"); ?>
</body>
</html>
