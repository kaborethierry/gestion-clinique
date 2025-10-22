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
  && isset($_POST['id_facture'],$_POST['type_prestation_mod'],$_POST['montant_mod'],
           $_POST['moyen_paiement_mod'],$_POST['reference_paiement_mod'],
           $_POST['date_paiement_mod'],$_POST['paiement_differe_mod'])) {

  include __DIR__.'/connect_db_pdo.php';

  $idf   = (int) $_POST['id_facture'];
  $prest = trim($_POST['type_prestation_mod']);
  $mont  = (float) str_replace(',','.',$_POST['montant_mod']);
  $mp    = $_POST['moyen_paiement_mod'];
  $ref   = trim($_POST['reference_paiement_mod']);
  $dpaie = $_POST['date_paiement_mod'];
  $diff  = $_POST['paiement_differe_mod'];

  // Ancien
  $oldS = $bdd->prepare("SELECT * FROM facturation WHERE id_facture=?");
  $oldS->execute([$idf]);
  $old = $oldS->fetch(PDO::FETCH_ASSOC);

  // Taux
  $stmtP = $bdd->prepare("
    SELECT assurance_statut,taux_couverture FROM patient WHERE id=?
  ");
  $stmtP->execute([$old['id_patient']]);
  $pat = $stmtP->fetch(PDO::FETCH_ASSOC);
  $taux=($pat&&$pat['assurance_statut']==='Assuré')?(float)$pat['taux_couverture']:0.00;

  $partA=round($mont*$taux/100,2);
  $reste=round($mont-$partA,2);
  $total=$mont;

  // Update
  $upd=$bdd->prepare("
    UPDATE facturation SET
      type_prestation=?,montant=?,taux_couverture=?,part_assurance=?,
      reste_a_charge=?,montant_total=?,moyen_paiement=?,reference_paiement=?,
      date_paiement=?,paiement_differe=?,date_modification=NOW()
    WHERE id_facture=?
  ");
  $upd->execute([
    $prest,$mont,$taux,$partA,$reste,$total,
    $mp,$ref,$dpaie,$diff,$idf
  ]);

  // Historique
  $new = json_encode([
    'prestation'=>$prest,'montant'=>$mont,'taux'=>$taux,
    'partA'=>$partA,'reste'=>$reste,'total'=>$total,
    'mp'=>$mp,'ref'=>$ref,'dpaie'=>$dpaie,'diff'=>$diff
  ]);
  $h=$bdd->prepare("
    INSERT INTO historique_action
      (id_utilisateur,nom_action,nom_table,id_concerne,
       ancienne_valeur,nouvelle_valeur,adresse_ip)
    VALUES(?,?,?,?,?,?,?)
  ");
  $h->execute([
    $_SESSION['id'],'Modification facture','facturation',
    $idf,json_encode($old),$new,$_SERVER['REMOTE_ADDR']
  ]);

  $_SESSION['mod_fact']=1;
}

header('Location: ../../pages/facturation.php');
exit();
