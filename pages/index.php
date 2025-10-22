<?php
session_start();
if (empty($_SESSION['id']) || ($_SESSION['type_compte'] != "Super Administrateur")) {
    session_unset();
    session_destroy();
    header('Location:./../index.php?erreur=3');
} else {
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Clinique - Accueil</title>
    <?php include("inclusion_haut.php") ?>


</head>

<body>

    
    <?php include("loader.php") ?>

    
    <!--**********************************
        Main wrapper start
    ***********************************-->
    <div id="main-wrapper">

        <?php include("entete.php") ?>

        <?php include("menu.php") ?>


        <!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">

            <div class="row page-titles mx-0">
                <div class="col p-md-0">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0)">Clinique</a></li>
                        <li class="breadcrumb-item active"><a href="javascript:void(0)">Accueil</a></li>
                    </ol>
                </div>
            </div>
            <!-- row -->

            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Liste des utilisateurs</h4>
                                <div class="table-responsive">
                                    <table id="datatable" class="table table-striped table-bordered zero-configuration">
                                        <thead>
                                            <tr>
                                                <th class="text-center">N°</th>
                                                <th class="text-center">Nom</th>
                                                <th class="text-center">Prénom(s)</th>
                                                <th class="text-center">Nom d'utilisateur</th>
                                                <th class="text-center">Type de compte</th>
                                                <th class="text-center">Modifier</th>
                                                <th class="text-center">Supprimer</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            
                                            
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th class="text-center">N°</th>    
                                                <th class="text-center">Nom</th>
                                                <th class="text-center">Prénom(s)</th>
                                                <th class="text-center">Nom d'utilisateur</th>
                                                <th class="text-center">Type de compte</th>
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
            <!-- #/ container -->
        </div>
        <!--**********************************
            Content body end
        ***********************************-->
        
        
        <?php include("footer.php") ?>

    </div>
    <!--**********************************
        Main wrapper end
    ***********************************-->

    <?php include("inclusion_bas.php") ?>
    <script type="text/javascript" src="DataTables/data_table_utilisateur.js"></script>


</body>

</html>
<?php
}
?>