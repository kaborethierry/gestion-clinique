<?php
// api/modules/modifier_laboratoire.php

session_start();

// ➊ Contrôle d’accès : Super Administrateur, Medecin, Laborantin
$allowedRoles = ['Super Administrateur', 'Medecin', 'Laborantin'];
if (empty($_SESSION['id'])
    || ! in_array($_SESSION['type_compte'], $allowedRoles, true)
) {
    header('Location: ../../index.php?erreur=3');
    exit();
}

require __DIR__ . '/connect_db_pdo.php';

// ➋ Vérifier la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../pages/laboratoire.php?error=method');
    exit();
}

// ➌ Récupérer et valider l’ID
$idRes   = isset($_POST['id_resultat'])  ? (int) $_POST['id_resultat'] : 0;
$contenu = isset($_POST['contenu_texte']) ? trim($_POST['contenu_texte']) : '';
$file    = $_FILES['fichier']            ?? null;

if ($idRes <= 0) {
    header('Location: ../../pages/laboratoire.php?error=missing');
    exit();
}

// ➍ Ancien fichier
$q   = $bdd->prepare("SELECT fichier FROM resultat_examen WHERE id_resultat = ?");
$q->execute([$idRes]);
$old = $q->fetchColumn();

$filename = $old;

// ➎ Traitement du nouveau fichier
if ($file && $file['error'] === UPLOAD_ERR_OK) {
    $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = uniqid('res_') . '.' . $ext;

    $uploadDir = __DIR__ . '/../../uploads/resultats/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    move_uploaded_file($file['tmp_name'], $uploadDir . $filename);

    // Supprimer l’ancien
    if (!empty($old) && file_exists($uploadDir . $old)) {
        unlink($uploadDir . $old);
    }
}

// ➏ Mise à jour en base
$sql = "
    UPDATE resultat_examen
    SET contenu_texte = :txt,
        fichier       = :f
    WHERE id_resultat = :idr
";
$stmt = $bdd->prepare($sql);
$stmt->execute([
    ':txt' => $contenu !== '' ? $contenu : null,
    ':f'   => $filename,
    ':idr' => $idRes
]);

// ➐ Redirection
header('Location: ../../pages/laboratoire.php?success=modif');
exit();
