<?php
// pages/alert_patient.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si session invalide, on redirige
if (empty($_SESSION['id'])) {
    header('Location: ../index.php?erreur=3');
    exit();
}

// Utilitaire SweetAlert
function afficher_alert($titre, $message, $type = 'success') {
    // Couleur du bouton selon le type
    $btnClass = $type === 'success' ? 'btn btn-success' : 'btn btn-danger';

    echo <<<JS
<script>
document.addEventListener('DOMContentLoaded', function(){
  Swal.fire({
    title:   "{$titre}",
    text:    "{$message}",
    icon:    "{$type}",
    confirmButtonText: "OK",
    customClass: { confirmButton: "{$btnClass}" }
  });
});
</script>
JS;
}

// 1) Nouveaux noms de sessions (success / error)
if (!empty($_SESSION['success'])) {
    afficher_alert('Succès', addslashes($_SESSION['success']), 'success');
    unset($_SESSION['success']);
}
if (!empty($_SESSION['error'])) {
    afficher_alert('Erreur', addslashes($_SESSION['error']), 'error');
    unset($_SESSION['error']);
}

// 2) Ancienne logique (ajout / mod / suppr / dossier_existe / imp)
if (!empty($_SESSION['ajout'])) {
    afficher_alert('Patient ajouté !', 'Le nouveau patient a été enregistré avec succès.', 'success');
    unset($_SESSION['ajout']);
}

if (!empty($_SESSION['mod'])) {
    afficher_alert('Patient modifié !', 'Les informations du patient ont été mises à jour.', 'success');
    unset($_SESSION['mod']);
}

if (!empty($_SESSION['suppr'])) {
    afficher_alert('Patient supprimé !', 'Le patient a été retiré du système.', 'success');
    unset($_SESSION['suppr']);
}

if (!empty($_SESSION['dossier_existe'])) {
    afficher_alert('Erreur', 'Ce numéro de dossier est déjà utilisé.', 'error');
    unset($_SESSION['dossier_existe']);
}

if (!empty($_SESSION['imp'])) {
    $msg = !empty($_SESSION['message_erreur'])
         ? $_SESSION['message_erreur']
         : 'Une erreur est survenue ou les données sont incomplètes.';
    afficher_alert('Erreur', addslashes($msg), 'error');
    unset($_SESSION['imp'], $_SESSION['message_erreur']);
}
?>
