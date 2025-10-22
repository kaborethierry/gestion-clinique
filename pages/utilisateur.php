<?php
session_start();
if (empty($_SESSION['id']) || ($_SESSION['type_compte'] != "Super Administrateur")) {
    session_unset();
    session_destroy();
    header('Location:./../index.php?erreur=3');
    exit();
} else {
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Clinique - Accueil</title>
  <?php include("inclusion_haut.php"); ?>
  <!-- Inclusion de SweetAlert2 CSS depuis le dossier css -->
   
</head>
<body>
  <?php include("loader.php"); ?>
  <?php include("alert_utilisateur.php"); ?>

  <div id="main-wrapper">
    <?php include("entete.php"); ?>
    <?php include("menu.php"); ?>

    <div class="content-body">
      <div class="row page-titles mx-0">
        <div class="col p-md-0">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0)">Clinique</a></li>
            <li class="breadcrumb-item active"><a href="javascript:void(0)">Utilisateur</a></li>
          </ol>
        </div>
      </div>

      <!-- Conteneur du tableau -->
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <!-- Titre et bouton d'ajout placé en haut -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h4 class="card-title m-0">Liste des utilisateurs</h4>
                  <!-- Au clic, le bouton affiche le modal -->
                  <button data-toggle="modal"  data-backdrop="false"
                  class="open-Ajouter_Utilisateur btn btn-primary"  href="#ajouter_utilisateur" >
                    <i class="fa fa-plus"></i> Ajouter un utilisateur
                  </button>
                </div>

                <!-- Tableau DataTables -->
                <div class="table-responsive">
                  <table id="datatable" class="table table-striped table-bordered zero-configuration">
                    <thead>
                      <tr>
                        <th class="text-center">N°</th>
                        <th class="text-center">Nom</th>
                        <th class="text-center">Prénom(s)</th>
                        <th class="text-center">Nom d'utilisateur</th>
                        <th class="text-center">Email</th>
                        <th class="text-center">Téléphone</th>
                        <th class="text-center">Adresse</th>
                        <th class="text-center">Poste</th>
                        <th class="text-center">Type de compte</th>
                        <th class="text-center">Date d'inscription</th>
                        <th class="text-center">Statut</th>
                        <th class="text-center">Modifier</th>
                        <th class="text-center">Supprimer</th>
                      </tr>
                    </thead>
                    <tbody>
                      <!-- Les enregistrements seront chargés via AJAX -->
                    </tbody>
                    <tfoot>
                      <tr>
                        <th class="text-center">N°</th>
                        <th class="text-center">Nom</th>
                        <th class="text-center">Prénom(s)</th>
                        <th class="text-center">Nom d'utilisateur</th>
                        <th class="text-center">Email</th>
                        <th class="text-center">Téléphone</th>
                        <th class="text-center">Adresse</th>
                        <th class="text-center">Poste</th>
                        <th class="text-center">Type de compte</th>
                        <th class="text-center">Date d'inscription</th>
                        <th class="text-center">Statut</th>
                        <th class="text-center">Modifier</th>
                        <th class="text-center">Supprimer</th>
                      </tr>
                    </tfoot>
                  </table>
                </div>
                <!-- Fin table-responsive -->
              </div> <!-- Fin card-body -->
            </div> <!-- Fin card -->
          </div> <!-- Fin col-12 -->
        </div> <!-- Fin row -->
      </div> <!-- Fin container-fluid -->
    </div> <!-- Fin content-body -->

    <?php include("footer.php"); ?>
  </div> <!-- Fin main-wrapper -->

  <?php include("inclusion_bas.php"); ?>

  <!-- Chargement du script DataTables et du script de gestion AJAX -->
  <script type="text/javascript" src="DataTables/data_table_utilisateur.js?Version=1.2"></script>

  <!-- Inclusion du modal (fichier contenu dans le dossier modals) -->
  <?php include("modals/modal_utilisateur.php"); ?>
</body>
</html>
<?php
}
?>
