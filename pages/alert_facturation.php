<?php
if (empty($_SESSION['id'])) {
  header('Location: ../index.php?erreur=3');
  exit();
}

if (!empty($_SESSION['ajout_fact'])) {
  echo "<script>Swal.fire('Facture enregistrée !','','success');</script>";
  $_SESSION['ajout_fact'] = null;
}
if (!empty($_SESSION['mod_fact'])) {
  echo "<script>Swal.fire('Facture modifiée !','','success');</script>";
  $_SESSION['mod_fact'] = null;
}
if (!empty($_SESSION['supr_fact'])) {
  echo "<script>Swal.fire('Facture supprimée !','','success');</script>";
  $_SESSION['supr_fact'] = null;
}
?>
