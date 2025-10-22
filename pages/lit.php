<?php
// api/modules/ajouter_chambre.php

// Affichage des erreurs en dev (désactiver en production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// ➊ Contrôle d’accès : Super Administrateur, Secretaire et Medecin
$allowedRoles = ['Super Administrateur', 'Secretaire', 'Medecin'];
if (empty($_SESSION['id'])
    || ! in_array($_SESSION['type_compte'], $allowedRoles, true)
) {
    header('Location: ../../index.php?erreur=3');
    exit();
}
 else {
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Clinique – Gestion des Lits</title>
  <?php include("inclusion_haut.php"); ?>
</head>
<body>
  <?php include("loader.php"); ?>
  <?php include("alert_lit.php"); ?>

  <div id="main-wrapper">
    <?php include("entete.php"); ?>
    <?php
      switch ($_SESSION['type_compte']) {
        case "Super Administrateur": include("menu.php"); break;
        case "Secretaire":           include("menu_secretaire.php"); break;
        case "Medecin":              include("menu_medecin.php"); break;
      }
    ?>


    <div class="content-body">
      <div class="row page-titles mx-0">
        <div class="col p-md-0">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0)">Clinique</a></li>
            <li class="breadcrumb-item active"><a href="javascript:void(0)">Lit</a></li>
          </ol>
        </div>
      </div>

      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h4 class="card-title m-0">Liste des Lits</h4>
                  <button data-toggle="modal" data-backdrop="false"
                          class="open-Ajouter_Lit btn btn-primary"
                          href="#ajouter_lit">
                    <i class="fa fa-plus"></i> Ajouter un lit
                  </button>
                </div>

                <div class="table-responsive">
                  <table id="datatable"
                         class="table table-striped table-bordered zero-configuration">
                    <thead>
                      <tr>
                        <th class="text-center">N°</th>
                        <th class="text-center">Chambre n°</th>
                        <th class="text-center">Numéro lit</th>
                        <th class="text-center">Statut</th>
                        <th class="text-center">Date création</th>
                        <th class="text-center">Modifier</th>
                        <th class="text-center">Supprimer</th>
                      </tr>
                    </thead>
                    <tbody>
                      <!-- Chargé via AJAX -->
                    </tbody>
                    <tfoot>
                      <tr>
                        <th class="text-center">N°</th>
                        <th class="text-center">Chambre n°</th>
                        <th class="text-center">Numéro lit</th>
                        <th class="text-center">Statut</th>
                        <th class="text-center">Date création</th>
                        <th class="text-center">Modifier</th>
                        <th class="text-center">Supprimer</th>
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

  <script type="text/javascript"
          src="DataTables/data_table_lit.js?Version=1.2"></script>

  <?php include("modals/modal_lit.php"); ?>

</body>
</html>
<?php
}
?>
```