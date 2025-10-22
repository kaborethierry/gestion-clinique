<?php
// pages/alert_chambre.php
if (session_status() === PHP_SESSION_NONE) session_start();

// Utilitaire SweetAlert
function swal($title, $text, $icon = 'success') {
  echo "<script>
    Swal.fire({
      title: '".addslashes($title)."',
      text:  '".addslashes($text)."',
      icon:  '$icon',
      confirmButtonText: 'OK'
    });
  </script>";
}

// Chambre créée
if (!empty($_SESSION['ajout_ch'])) {
  swal('Chambre créée !', 'La chambre a bien été ajoutée.');
  unset($_SESSION['ajout_ch']);
}
// Chambre modifiée
if (!empty($_SESSION['mod_ch'])) {
  swal('Chambre modifiée !', 'Les informations ont été mises à jour.');
  unset($_SESSION['mod_ch']);
}
// Chambre supprimée
if (!empty($_SESSION['suppr_ch'])) {
  swal('Chambre supprimée !', 'La chambre a été supprimée avec succès.');
  unset($_SESSION['suppr_ch']);
}
// Erreur
if (!empty($_SESSION['imp_ch'])) {
  $msg = $_SESSION['message_ch'] ?? 'Une erreur est survenue.';
  swal('Erreur !', $msg, 'error');
  unset($_SESSION['imp_ch'], $_SESSION['message_ch']);
}
?>
