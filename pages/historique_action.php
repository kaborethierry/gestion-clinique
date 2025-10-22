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
} else {
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Clinique - Historique des Actions</title>
  <?php include("inclusion_haut.php"); ?>
  <!-- DataTables pour l'historique -->
  <script src="DataTables/data_table_historique_action.js?Version=1.3"></script>
  <style>
    /* Styles spécifiques si besoin */
  </style>
</head>
<body>
  <?php include("loader.php"); ?>
  <?php include("alert_utilisateur.php"); ?>

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
            <li class="breadcrumb-item"><a href="javascript:void(0)">Clinique</a></li>
            <li class="breadcrumb-item active"><a href="javascript:void(0)">Historique des Actions</a></li>
          </ol>
        </div>
      </div>

      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <h4 class="card-title m-0">Historique des Actions</h4>
                <div class="table-responsive">
                  <table id="datatable" class="table table-striped table-bordered zero-configuration">
                    <thead>
                      <tr>
                        <th class="text-center">N°</th>
                        <th class="text-center">Adresse IP</th>
                        <th class="text-center">Date & Heure Action</th>
                        <th class="text-center">Nom d'utilisateur</th>
                        <th class="text-center">Nom de l'action</th>
                        <th class="text-center">Nom de la table</th>
                        <th class="text-center">Identifiant concerné</th>
                        <th class="text-center">Ancienne valeur</th>
                        <th class="text-center">Nouvelle valeur</th>
                      </tr>
                    </thead>
                    <tbody>
                      <!-- Chargé via AJAX -->
                    </tbody>
                    <tfoot>
                      <tr>
                        <th class="text-center">N°</th>
                        <th class="text-center">Adresse IP</th>
                        <th class="text-center">Date & Heure Action</th>
                        <th class="text-center">Nom d'utilisateur</th>
                        <th class="text-center">Nom de l'action</th>
                        <th class="text-center">Nom de la table</th>
                        <th class="text-center">Identifiant concerné</th>
                        <th class="text-center">Ancienne valeur</th>
                        <th class="text-center">Nouvelle valeur</th>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <?php include("footer.php"); ?>
  </div>

  <?php include("inclusion_bas.php"); ?>
</body>
</html>
<?php
}
