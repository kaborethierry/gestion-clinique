<?php
session_start();

// ➊ Contrôle d’accès : uniquement Super Administrateur et Médecin
if (empty($_SESSION['id'])
    || ! in_array($_SESSION['type_compte'], [
         "Super Administrateur",
         "Medecin"
       ], true)
) {
    session_unset();
    session_destroy();
    header('Location: ../index.php?erreur=3');
    exit();
}

// … le reste de votre page consultation.php …

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Clinique - Gestion des Consultations</title>
    <?php include("inclusion_haut.php"); ?>
</head>
<body>
    <?php include("loader.php"); ?>
    <?php include("alert_consultation.php"); ?>

    <div id="main-wrapper">
        <?php include("entete.php"); ?>
        <?php
      switch ($_SESSION['type_compte']) {
        case "Super Administrateur": include("menu.php"); break;

        case "Medecin":              include("menu_medecin.php"); break;
      }
    ?>

        <div class="content-body">
        <div class="row page-titles mx-0">
    <div class="col p-md-0">
        <div style="display: flex; gap: 10px; margin: 10px 0;">
            
            <button style="
                background-color: #007bff;
                border: 1px solid #007bff;
                color: white;
                padding: 8px 16px;
                border-radius: 5px;
                cursor: pointer;
                font-weight: bold;
                transition: 0.3s;
            " onclick="location.href='chambre.php'">
                🏠 Chambre
            </button>

            <button style="
                background-color: #6f42c1;
                border: 1px solid #6f42c1;
                color: white;
                padding: 8px 16px;
                border-radius: 5px;
                cursor: pointer;
                font-weight: bold;
                transition: 0.3s;
            " onclick="location.href='lit.php'">
                🛏️ Lit
            </button>

            <button style="
                background-color: #28a745;
                border: 1px solid #28a745;
                color: white;
                padding: 8px 16px;
                border-radius: 5px;
                cursor: pointer;
                font-weight: bold;
                transition: 0.3s;
            " onclick="location.href='hospitalisation.php'">
                🏥 Hospitalisation
            </button>

            <button style="
                background-color: #ffc107;
                border: 1px solid #ffc107;
                color: black;
                padding: 8px 16px;
                border-radius: 5px;
                cursor: pointer;
                font-weight: bold;
                transition: 0.3s;
            " onclick="location.href='laboratoire.php'">
                🧪 Laboratoire
            </button>

        </div>
    </div>
</div>




            <div class="container-fluid">
                <div class="card">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="card-title m-0">Liste des Consultations</h4>
                            <button
                                type="button"
                                class="btn btn-primary"
                                data-toggle="modal"
                                data-target="#ajouter_consultation"
                            >
                                <i class="fa fa-plus"></i> Nouvelle consultation
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table
                                id="datatable"
                                class="table table-striped table-bordered zero-configuration"
                            >
                                <thead>
                                    <tr>
                                        <th>N°</th>
                                        <th>Patient</th>
                                        <th>Médecin</th>
                                        <th>Date</th>
                                        <th>Motif</th>
                                        <th>Symptômes</th>
                                        <th>Diagnostic</th>
                                        <th>Observations</th>
                                        <th>TA</th>
                                        <th>Temp (°C)</th>
                                        <th>Poids (kg)</th>
                                        <th>Taille (cm)</th>
                                        <th>FC (bpm)</th>
                                        <th>IMC (kg/m²)</th>
                                        <th>Glycémie (g/L)</th>
                                        <th>FR (cpm)</th>
                                        <th>SaO₂ (%)</th>
                                        <th>Détails</th>
                                        <th>Modifier</th>
                                        <th>Impr. Consult.</th>
                                        <th>Impr. Ordon.</th>
                                        <th>Rés. Exam.</th>
                                        <th>Supprimer</th>
                                        <th hidden>ID</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Chargé via AJAX -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>N°</th>
                                        <th>Patient</th>
                                        <th>Médecin</th>
                                        <th>Date</th>
                                        <th>Motif</th>
                                        <th>Symptômes</th>
                                        <th>Diagnostic</th>
                                        <th>Observations</th>
                                        <th>TA</th>
                                        <th>Temp (°C)</th>
                                        <th>Poids (kg)</th>
                                        <th>Taille (cm)</th>
                                        <th>FC (bpm)</th>
                                        <th>IMC (kg/m²)</th>
                                        <th>Glycémie (g/L)</th>
                                        <th>FR (cpm)</th>
                                        <th>SaO₂ (%)</th>
                                        <th>Détails</th>
                                        <th>Modifier</th>
                                        <th>Impr. Consult.</th>
                                        <th>Impr. Ordon.</th>
                                        <th>Rés. Exam.</th>
                                        <th>Supprimer</th>
                                        <th hidden>ID</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <?php include("footer.php"); ?>
    </div>

    <!-- Modal Lecture Seule « Détails Consultation » -->
    <div class="modal fade" id="details_consultation" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header bg-info text-white">
            <h5 class="modal-title" style="color: white;">Détails de la Consultation</h5>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
          </div>
          <div class="modal-body" id="details_body">
            <!-- Contenu injecté par JS -->
          </div>
        </div>
      </div>
    </div>

    <?php include("inclusion_bas.php"); ?>
    <script src="DataTables/data_table_consultation.js?Version=1.4"></script>
    <?php include("modals/modal_consultation.php"); ?>
</body>
</html>
