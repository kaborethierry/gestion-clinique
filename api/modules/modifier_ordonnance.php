<?php
session_start();
if (empty($_SESSION['id'])) {
    header('Location: ../../index.php?erreur=3'); exit();
}
if ($_SERVER['REQUEST_METHOD']!=='POST' || empty($_POST['id_ordonnance'])) {
    header('Location: ../../pages/ordonnance.php'); exit();
}
require __DIR__ . '/connect_db_pdo.php';
$id = (int)$_POST['id_ordonnance'];

try {
    $bdd->beginTransaction();
    // header
    $up = $bdd->prepare("
      UPDATE ordonnance SET
        type_ordonnance=?, id_patient=?, id_consultation=?, instructions=?
      WHERE id_ordonnance=?
    ");
    $up->execute([
        $_POST['type_ordonnance'],
        (int)$_POST['id_patient'],
        (int)$_POST['id_consultation'],
        trim($_POST['instructions'])?:null,
        $id
    ]);
    // delete old meds
    $bdd->prepare("DELETE FROM ordonnance_medicament WHERE id_ordonnance=?")
        ->execute([$id]);
    // insert new meds
    if (!empty($_POST['meds'])) {
      $insL = $bdd->prepare("
        INSERT INTO ordonnance_medicament
          (id_ordonnance,medicament,posologie,duree)
        VALUES (?,?,?,?)
      ");
      foreach ($_POST['meds'] as $m) {
        $insL->execute([$id,$m['nom'],$m['posologie'],$m['duree']]);
      }
    }
    $bdd->commit();
    $_SESSION['mod_ordon']=1;
    header('Location: ../../pages/ordonnance.php');
} catch(Exception $e) {
    $bdd->rollBack();
    $_SESSION['err_ordon']=1;
    $_SESSION['message_ordon']=$e->getMessage();
    header('Location: ../../pages/ordonnance.php');
}
exit();
