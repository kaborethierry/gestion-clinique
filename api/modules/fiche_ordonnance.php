<?php
// /api/modules/fiche_ordonnance.php

ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require __DIR__ . '/connect_db_pdo.php';

// 1) Déterminer l'ID de l'ordonnance à afficher
if (isset($_GET['id_consultation'])) {
    $id_consult = (int) $_GET['id_consultation'];

    // Récupère la dernière ordonnance non supprimée liée à cette consultation
    $oStmt = $bdd->prepare("
      SELECT id_ordonnance
        FROM ordonnance
       WHERE id_consultation = ?
         AND supprimer       = 'NON'
       ORDER BY date_creation DESC
       LIMIT 1
    ");
    $oStmt->execute([$id_consult]);
    $id_ordonnance = (int) $oStmt->fetchColumn();

    if (!$id_ordonnance) {
        die('Aucune ordonnance trouvée pour cette consultation.');
    }
}
elseif (isset($_GET['id_ordonnance'])) {
    $id_ordonnance = (int) $_GET['id_ordonnance'];
}
else {
    header('Location: ../../pages/ordonnance.php');
    exit();
}

// 2) Charger l'en-tête de l'ordonnance et infos patient / prescripteur
$stmt = $bdd->prepare("
  SELECT
    o.id_ordonnance,
    o.date_creation,
    o.instructions,
    p.nom   AS patient_nom,
    p.prenom AS patient_prenom,
    u.nom   AS prescripteur_nom,
    u.prenom AS prescripteur_prenom
  FROM ordonnance AS o
  JOIN patient       AS p ON o.id_patient      = p.id
  JOIN consultation  AS c ON o.id_consultation = c.id_consultation
  JOIN utilisateur   AS u ON c.id_medecin      = u.id_utilisateur
 WHERE o.id_ordonnance = ?
   AND o.supprimer     = 'NON'
");
$stmt->execute([$id_ordonnance]);
$ord = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ord) {
    die('Ordonnance introuvable.');
}

// 3) Charger les lignes de médicaments
$lstmt = $bdd->prepare("
  SELECT medicament, posologie, duree
    FROM ordonnance_medicament
   WHERE id_ordonnance = ?
     AND supprimer     = 'NON'
");
$lstmt->execute([$id_ordonnance]);
$lignes = $lstmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Ordonnance <?= str_pad($ord['id_ordonnance'], 4, '0', STR_PAD_LEFT) ?></title>
  <style>
    body { margin:0; padding:0; font-family: Arial, sans-serif; color:#333; }
    .page {
      width:210mm; min-height:297mm; padding:20mm; margin:auto;
      background:#fff; box-sizing:border-box; position:relative;
    }
    .header { display:flex; justify-content:space-between; align-items:center; }
    .header img { height:60px; }
    .clinic-info { text-align:right; font-size:14px; }
    h1 { text-align:center; color:#007bff; margin:20px 0; }
    .section { margin:20px 0; }
    .section h3 {
      font-size:18px; color:#444; border-bottom:1px solid #ccc;
      padding-bottom:5px; margin-bottom:10px;
    }
    .info-line { margin:6px 0; font-size:15px; }
    .info-line strong { display:inline-block; width:150px; }
    table { width:100%; border-collapse:collapse; margin-top:10px; }
    th, td { border:1px solid #ddd; padding:8px; font-size:14px; text-align:left; }
    th { background:#f2f2f2; }
    .footer {
      position:absolute; bottom:20mm; right:20mm;
      font-size:13px; color:#666; text-align:right;
    }
    #downloadButton { text-align:center; margin:20px 0; }
    #downloadButton button {
      padding:10px 20px; background:#007bff; color:#fff;
      border:none; cursor:pointer; font-size:16px;
    }
    #downloadButton button:hover { background:#0056b3; }
  </style>
</head>
<body>

  <div class="page" id="fiche_ordonnance">
    <div class="header">
      <img src="../../pages/images/login.png" alt="Logo Clinique">
      <div class="clinic-info">
        <p>Clinique Santé+</p>
        <p>Quartier Tanghin — Tél: +226 70 00 00 00</p>
        <p>www.clinique-santeplus.bf</p>
      </div>
    </div>

    <h1>ORDONNANCE N° <?= str_pad($ord['id_ordonnance'], 4, '0', STR_PAD_LEFT) ?></h1>

    <div class="section">
      <h3>Informations</h3>
      <div class="info-line">
        <strong>Patient :</strong>
        <?= htmlspecialchars($ord['patient_nom'] . ' ' . $ord['patient_prenom']) ?>
      </div>
      <div class="info-line">
        <strong>Date :</strong>
        <?= date('d/m/Y H:i', strtotime($ord['date_creation'])) ?>
      </div>
      <div class="info-line">
        <strong>Prescripteur :</strong>
        <?= htmlspecialchars($ord['prescripteur_nom'] . ' ' . $ord['prescripteur_prenom']) ?>
      </div>
    </div>

    <div class="section">
      <h3>Médicaments</h3>
      <table>
        <thead>
          <tr>
            <th>Nom</th>
            <th>Posologie</th>
            <th>Durée</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($lignes as $l): ?>
          <tr>
            <td><?= htmlspecialchars($l['medicament']) ?></td>
            <td><?= htmlspecialchars($l['posologie']) ?></td>
            <td><?= htmlspecialchars($l['duree']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if (!empty($ord['instructions'])): ?>
    <div class="section">
      <h3>Instructions supplémentaires</h3>
      <div class="info-line"><?= nl2br(htmlspecialchars($ord['instructions'])) ?></div>
    </div>
    <?php endif; ?>

    <div class="footer">
      <em>Document généré automatiquement le <?= date('d/m/Y à H:i') ?>. Usage médical uniquement.</em>
    </div>
  </div>

  <div id="downloadButton">
    <button onclick="downloadPDF()">Télécharger en PDF</button>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
  <script>
    function downloadPDF() {
      html2pdf()
        .set({ margin: 10 })
        .from(document.getElementById('fiche_ordonnance'))
        .save('ordonnance_<?= $ord['id_ordonnance'] ?>.pdf');
    }
  </script>

</body>
</html>