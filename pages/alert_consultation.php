<?php
if (session_status() === PHP_SESSION_NONE) session_start();
function swal($title, $text, $icon = 'success') {
  echo "<script>Swal.fire({title:'".addslashes($title)."',text:'".addslashes($text)."',icon:'$icon'});</script>";
}

if (!empty($_SESSION['ajout_co'])) {
  swal('Consultation créée','La consultation a bien été enregistrée.');
  unset($_SESSION['ajout_co']);
}
if (!empty($_SESSION['mod_co'])) {
  swal('Consultation modifiée','Les données ont été mises à jour.');
  unset($_SESSION['mod_co']);
}
if (!empty($_SESSION['suppr_co'])) {
  swal('Consultation supprimée','La consultation a été supprimée.');
  unset($_SESSION['suppr_co']);
}
if (!empty($_SESSION['imp_co'])) {
  $msg = $_SESSION['message_co'] ?? 'Une erreur est survenue.';
  swal('Erreur',$msg,'error');
  unset($_SESSION['imp_co'],$_SESSION['message_co']);
}
?>
