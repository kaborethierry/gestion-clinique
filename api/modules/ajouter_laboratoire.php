<?php
// api/modules/ajouter_laboratoire.php

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

// ➋ Récupération des données
$idEx    = isset($_POST['id_examen']) ? (int) trim($_POST['id_examen']) : 0;
$contenu = isset($_POST['contenu_texte']) ? trim($_POST['contenu_texte']) : '';
$file    = $_FILES['fichier'] ?? null;
$filename = null;

// ➌ Gestion du fichier uploadé
if ($file && $file['error'] === UPLOAD_ERR_OK) {
    $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = uniqid('res_') . '.' . $ext;
    $dest     = __DIR__ . '/../../uploads/resultats/' . $filename;
    move_uploaded_file($file['tmp_name'], $dest);
}

// ➍ Insertion en base
$sql = "
  INSERT INTO resultat_examen
    (id_examen, contenu_texte, fichier)
  VALUES
    (:iex, :txt, :f)
";
$stmt = $bdd->prepare($sql);
$stmt->execute([
    ':iex' => $idEx,
    ':txt' => $contenu !== '' ? $contenu : null,
    ':f'   => $filename
]);

// ➎ Redirection
header('Location: ../../pages/laboratoire.php?success=ajout');
exit();
