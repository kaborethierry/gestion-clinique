<?php
// api/modules/supprimer_laboratoire.php
// Marque un résultat d’examen comme supprimé

// Affichage des erreurs en dev (désactiver en prod)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// ➊ Contrôle d’accès : Super Administrateur, Médecin, Laborantin
$allowedRoles = ['Super Administrateur', 'Medecin', 'Laborantin'];
if (empty($_SESSION['id'])
    || ! in_array($_SESSION['type_compte'], $allowedRoles, true)
) {
    header('Location: ../../index.php?erreur=3');
    exit();
}

require __DIR__ . '/connect_db_pdo.php';

// ➋ Récupération de l’ID du résultat à supprimer
$idRes = isset($_POST['id_resultat'])
    ? (int) $_POST['id_resultat']
    : 0;

// ➌ Marquage comme supprimé
$sql = "
    UPDATE resultat_examen
    SET supprimer = 'OUI'
    WHERE id_resultat = ?
";
$stmt = $bdd->prepare($sql);
$stmt->execute([$idRes]);

// ➍ Redirection vers la page laboratoire
header('Location: ../../pages/laboratoire.php?success=supp');
exit();
