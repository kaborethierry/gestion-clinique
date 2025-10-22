<?php
// pages/alert_profil.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si l’utilisateur n’est pas connecté, on redirige
if (empty($_SESSION['id'])) {
    header('Location: ../index.php?erreur=3');
    exit();
}

// Récupère et reset une variable de session, retourne sa valeur booléenne
function popSessionFlag(string $key): bool {
    if (!empty($_SESSION[$key]) && $_SESSION[$key] == 1) {
        unset($_SESSION[$key]);
        return true;
    }
    return false;
}

// On prépare un tableau d’alertes à déclencher
$alerts = [];

// profil modifié ?
if (popSessionFlag('mod_profil')) {
    $alerts[] = [
        'title' => 'Profil mis à jour',
        'text'  => 'Vos informations ont bien été enregistrées.',
        'icon'  => 'success'
    ];
}

// ancien mot de passe invalide ?
if (popSessionFlag('anc_password')) {
    $alerts[] = [
        'title' => 'Mot de passe incorrect',
        'text'  => 'L’ancien mot de passe ne correspond pas.',
        'icon'  => 'error'
    ];
}

// mot de passe changé ?
if (popSessionFlag('modif_password')) {
    $alerts[] = [
        'title' => 'Mot de passe modifié',
        'text'  => 'Votre nouveau mot de passe a été enregistré.',
        'icon'  => 'success'
    ];
}

// nom d’utilisateur en conflit ?
if (popSessionFlag('imp')) {
    $alerts[] = [
        'title' => 'Erreur',
        'text'  => 'Ce nom d’utilisateur est déjà pris.',
        'icon'  => 'error'
    ];
}

// Si aucune alerte, on sort
if (empty($alerts)) {
    return;
}

// Génère le script JS
?>
<script>
// On attend que SweetAlert2 et le DOM soient prêts
document.addEventListener('DOMContentLoaded', function(){
  <?php foreach($alerts as $a): ?>
  Swal.fire({
    title:   "<?= addslashes($a['title']) ?>",
    text:    "<?= addslashes($a['text']) ?>",
    icon:    "<?= addslashes($a['icon']) ?>",
    confirmButtonText: "OK"
  });
  <?php endforeach; ?>
});
</script>
