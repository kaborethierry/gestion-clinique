<?php
session_start();

// Contrôle d’accès : Super Administrateur et Comptable
if (empty($_SESSION['id'])
    || ! in_array($_SESSION['type_compte'], [
         'Super Administrateur',
         'Comptable'
       ], true)
) {
    session_unset();
    session_destroy();
    header('Location: ../index.php?erreur=3');
    exit();
}

// … le reste de votre traitement d’ajout de facturation …


if ($_SERVER['REQUEST_METHOD']==='POST'
  && isset($_POST['id_patient'],$_POST['type_prestation'],$_POST['montant'],
           $_POST['moyen_paiement'],$_POST['reference_paiement'],
           $_POST['date_paiement'],$_POST['paiement_differe'])) {

  include __DIR__.'/connect_db_pdo.php';

  $id_p    = (int) $_POST['id_patient'];
  $prest   = trim($_POST['type_prestation']);
  $mont    = (float) str_replace(',','.',$_POST['montant']);
  $mp      = $_POST['moyen_paiement'];
  $ref     = trim($_POST['reference_paiement']);
  $dpaie   = $_POST['date_paiement'];
  $diff    = $_POST['paiement_differe'];

  // Détection couverture
  $stmtP = $bdd->prepare("
    SELECT assurance_statut, taux_couverture 
      FROM patient WHERE id=?
  ");
  $stmtP->execute([$id_p]);
  $pat = $stmtP->fetch(PDO::FETCH_ASSOC);
  $taux = ($pat && $pat['assurance_statut']==='Assuré')
        ? (float)$pat['taux_couverture'] : 0.00;

  $partA = round($mont*$taux/100,2);
  $reste = round($mont-$partA,2);
  $total = $mont;

  // Insert facture
  $ins = $bdd->prepare("
    INSERT INTO facturation
      (id_patient,type_prestation,montant,taux_couverture,
       part_assurance,reste_a_charge,montant_total,
       moyen_paiement,reference_paiement,date_paiement,
       paiement_differe)
    VALUES(?,?,?,?,?,?,?,?,?,?,?)
  ");
  $ins->execute([
    $id_p,$prest,$mont,$taux,
    $partA,$reste,$total,
    $mp,$ref,$dpaie,
    $diff
  ]);

  // Historique
  $new = json_encode([
    'prestation'=>$prest,'montant'=>$mont,'taux'=>$taux,
    'partA'=>$partA,'reste'=>$reste,'total'=>$total,
    'mp'=>$mp,'ref'=>$ref,'dpaie'=>$dpaie,'diff'=>$diff
  ]);
  $h = $bdd->prepare("
    INSERT INTO historique_action
      (id_utilisateur,nom_action,nom_table,id_concerne,
       ancienne_valeur,nouvelle_valeur,adresse_ip)
    VALUES(?,?,?,?,?,?,?)
  ");
  $h->execute([
    $_SESSION['id'],
    'Ajout facture','facturation',
    $bdd->lastInsertId(),
    null,$new,$_SERVER['REMOTE_ADDR']
  ]);

  $_SESSION['ajout_fact']=1;
}

header('Location: ../../pages/facturation.php');
exit();
