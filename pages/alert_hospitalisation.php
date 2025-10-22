<?php
if (empty($_SESSION['id'])) {
  header('Location: ./../index.php?erreur=3');
  exit();
}
if (!empty($_SESSION['ajout_hosp'])) {
  echo "<script>Swal.fire('Patient admis !','','success');</script>";
  $_SESSION['ajout_hosp']=null;
}
if (!empty($_SESSION['mod_hosp'])) {
  echo "<script>Swal.fire('Hospitalisation modifiée !','','success');</script>";
  $_SESSION['mod_hosp']=null;
}
if (!empty($_SESSION['supr_hosp'])) {
  echo "<script>Swal.fire('Hospitalisation supprimée !','','success');</script>";
  $_SESSION['supr_hosp']=null;
}
if (!empty($_SESSION['imp_hosp'])) {
  echo "<script>Swal.fire('Erreur !','Vérifiez les données.','error');</script>";
  $_SESSION['imp_hosp']=null;
}
?>
