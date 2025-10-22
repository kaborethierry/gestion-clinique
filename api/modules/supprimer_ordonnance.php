<?php
session_start();
if (empty($_SESSION['id'])) {
    header('Location: ../../index.php?erreur=3'); exit();
}
if (empty($_GET['id_ordonnance'])) {
    header('Location: ../../pages/ordonnance.php'); exit();
}
require __DIR__ . '/connect_db_pdo.php';
$id = (int)$_GET['id_ordonnance'];
$upd = $bdd->prepare("UPDATE ordonnance SET supprimer='OUI' WHERE id_ordonnance=?");
$upd->execute([$id]);
$_SESSION['sup_ordon']=1;
header('Location: ../../pages/ordonnance.php');
exit();
