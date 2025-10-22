<?php
session_start();

// Contrôle d’accès : uniquement Super Administrateur et Comptable
if (empty($_SESSION['id'])
    || ! in_array($_SESSION['type_compte'], [
         'Super Administrateur',
         'Comptable'
       ], true)
) {
    header('Location: ../index.php?erreur=3');
    exit();
}

// … suite de votre pages/facturation.php …

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Clinique – Facturation</title>
  <?php include "inclusion_haut.php"; ?>
</head>
<body>
  <?php include "loader.php"; ?>
  <?php include "alert_facturation.php"; ?>

  <div id="main-wrapper">
    <?php include "entete.php"; ?>
    <?php
      switch ($_SESSION['type_compte']) {
        case "Super Administrateur": include("menu.php"); break;
        case "Comptable":           include("menu_comptable.php"); break;

      }
    ?>


    <div class="content-body">
      <div class="breadcrumb row mx-0">
        <ol class="breadcrumb">

        </ol>
      </div>

      <div class="container-fluid">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between mb-3">
              <h4 class="card-title">Liste des factures</h4>
              <button class="btn btn-primary" data-toggle="modal" href="#ajouter_fact">
                <i class="fa fa-money-bill-wave"></i> Nouvelle facture
              </button>
            </div>

            <div class="table-responsive">
              <table id="datatable" class="table table-striped table-bordered">
                <thead>
                  <tr>
                    <th>N°</th><th>Patient</th><th>Prestation</th><th>Montant</th>
                    <th>Taux (%)</th><th>Assurance</th><th>Reste</th><th>Total</th>
                    <th>Moyen</th><th>Référence</th><th>Date paiement</th>
                    <th>Différé</th><th>Modifier</th><th>Imprimer</th><th>Supprimer</th>
                  </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                  <tr>
                    <th>N°</th><th>Patient</th><th>Prestation</th><th>Montant</th>
                    <th>Taux (%)</th><th>Assurance</th><th>Reste</th><th>Total</th>
                    <th>Moyen</th><th>Référence</th><th>Date paiement</th>
                    <th>Différé</th><th>Modifier</th><th>Imprimer</th><th>Supprimer</th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <?php include "footer.php"; ?>
  </div>

  <?php include "inclusion_bas.php"; ?>
  <script src="Datatables/data_table_facturation.js?version=1.0"></script>
  <?php include "modals/modal_facturation.php"; ?>
</body>
</html>
