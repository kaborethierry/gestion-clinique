<?php
// alert_rendez_vous.php
session_start();

// Si l’utilisateur n’est pas connecté
if (empty($_SESSION['id'])) {
    header('Location:../index.php?erreur=3');
    exit();
}

// Helper pour afficher un SweetAlert et réinitialiser le flag
function showAlert($title, $text, $icon, $flagName) {
    echo "<script>
      Swal.fire(
        " . json_encode($title) . ",
        " . json_encode($text) . ",
        " . json_encode($icon) . "
      );
    </script>";
    $_SESSION[$flagName] = 0;
}

// 1) Rendez-vous ajouté
if (isset($_SESSION['ajout']) && $_SESSION['ajout'] == 1) {
    showAlert('Rendez-vous ajouté !', 'Cliquez sur OK pour continuer', 'success', 'ajout');
}

// 2) Rendez-vous modifié
if (isset($_SESSION['mod']) && $_SESSION['mod'] == 1) {
    showAlert('Rendez-vous modifié !', 'Cliquez sur OK pour continuer', 'success', 'mod');
}

// 3) Rendez-vous supprimé
if (isset($_SESSION['supr']) && $_SESSION['supr'] == 1) {
    showAlert('Rendez-vous supprimé !', 'Cliquez sur OK pour continuer', 'success', 'supr');
}

// 4) Erreur générale (PDO, conflit, etc.)
if (isset($_SESSION['imp']) && $_SESSION['imp'] == 1) {
    showAlert('Erreur !', 'Une erreur est survenue. Veuillez réessayer.', 'error', 'imp');
}

// 5) Champ(s) manquant(s)
if (isset($_SESSION['erreur_champ_manquant']) && $_SESSION['erreur_champ_manquant'] == 1) {
    showAlert('Champs manquants', 'Veuillez remplir tous les champs obligatoires.', 'warning', 'erreur_champ_manquant');
}

// 6) Patient introuvable
if (isset($_SESSION['erreur_patient_introuvable']) && $_SESSION['erreur_patient_introuvable'] == 1) {
    showAlert('Patient introuvable', 'Le patient sélectionné n’existe pas.', 'error', 'erreur_patient_introuvable');
}

// 7) Médecin introuvable
if (isset($_SESSION['erreur_medecin_introuvable']) && $_SESSION['erreur_medecin_introuvable'] == 1) {
    showAlert('Médecin introuvable', 'Le médecin sélectionné n’existe pas.', 'error', 'erreur_medecin_introuvable');
}
?>
