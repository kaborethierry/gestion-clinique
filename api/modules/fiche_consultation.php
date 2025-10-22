<?php
// /api/modules/fiche_consultation.php

session_start();
if (empty($_SESSION['id'])) {
    header('Location: ../../index.php?erreur=3');
    exit();
}

if (!isset($_GET['id_consultation'])) {
    header('Location: ../../pages/consultation.php');
    exit();
}

$id_consult = (int) $_GET['id_consultation'];
require __DIR__ . '/connect_db_pdo.php';

// 1) Charger la consultation
$stmt = $bdd->prepare("
  SELECT
    c.*,
    CONCAT(p.nom, ' ', p.prenom) AS patient_nom,
    CONCAT(u.nom, ' ', u.prenom) AS medecin_nom
  FROM consultation AS c
  JOIN patient     AS p ON c.id_patient = p.id
  JOIN utilisateur AS u ON c.id_medecin  = u.id_utilisateur
  WHERE c.id_consultation = ?
    AND c.supprimer        = 'NON'
");
$stmt->execute([$id_consult]);
$consultation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$consultation) {
    die('Consultation introuvable.');
}

// 2) Charger les constantes vitales les plus récentes
$constantesMap = [
    'Température'               => 'temperature',
    'Poids'                     => 'poids',
    'Taille'                    => 'taille',
    'IMC'                       => 'imc',
    'Tension artérielle'        => 'tension_arterielle',
    'Fréquence cardiaque'       => 'frequence_cardiaque',
    'Fréquence respiratoire'    => 'frequence_respiratoire',
    'Saturation oxygène'        => 'saturation_oxygene',
    'Glycémie'                  => 'glycemie',
];

foreach ($constantesMap as $nom => $key) {
    $cStmt = $bdd->prepare("
      SELECT valeur
        FROM constante_consultation
       WHERE id_consultation = ?
         AND nom              = ?
         AND supprimer        = 'NON'
       ORDER BY date_saisie DESC
       LIMIT 1
    ");
    $cStmt->execute([$id_consult, $nom]);
    $val = $cStmt->fetchColumn();
    $consultation[$key] = $val !== false ? $val : null;
}

// 3) Charger l'ordonnance (le cas échéant)
$ordStmt = $bdd->prepare("
  SELECT id_ordonnance, type_ordonnance, instructions
    FROM ordonnance
   WHERE id_consultation = ?
     AND supprimer       = 'NON'
   LIMIT 1
");
$ordStmt->execute([$id_consult]);
$ordonnance = $ordStmt->fetch(PDO::FETCH_ASSOC);

// 4) Médicaments de l'ordonnance
$medicaments = [];
if ($ordonnance) {
    $medStmt = $bdd->prepare("
      SELECT medicament, posologie, duree
        FROM ordonnance_medicament
       WHERE id_ordonnance = ?
         AND supprimer     = 'NON'
    ");
    $medStmt->execute([$ordonnance['id_ordonnance']]);
    $medicaments = $medStmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Fiche de consultation #<?= $id_consult ?></title>
  <style>
    body { margin:0; padding:0; font-family: Arial, sans-serif; color:#333; }
    .page { width:210mm; min-height:297mm; padding:20mm; margin:auto;
            box-sizing:border-box; background:#fff; position:relative; }
    .header { display:flex; justify-content:space-between; margin-bottom:15px; }
    .header img { height:80px; }
    .clinic-info { text-align:right; font-size:14px; }
    h1 { text-align:center; margin:20px 0; font-size:26px; color:#007bff; }
    .section { margin:20px 0; }
    .section h3 { font-size:18px; color:#444; border-bottom:1px solid #ccc;
                  padding-bottom:5px; margin-bottom:10px; }
    .info-line { margin:6px 0; font-size:15px; }
    .info-line strong { display:inline-block; width:180px; }
    table { width:100%; border-collapse:collapse; margin-top:10px; }
    th, td { border:1px solid #ddd; padding:8px; font-size:14px; text-align:center; }
    th { background:#f2f2f2; }
    .footer { position:absolute; bottom:20mm; right:20mm;
              font-size:13px; color:#666; }
    #downloadButton { text-align:center; margin:20px 0; }
    #downloadButton button { padding:10px 20px; background:#007bff; color:#fff;
                              border:none; font-size:16px; cursor:pointer; }
    #downloadButton button:hover { background:#0056b3; }
  </style>
</head>
<body>

  <div class="page" id="fiche_consultation">
    <div class="header">
      <img src="../../pages/images/login.png" alt="Logo Clinique">
      <div class="clinic-info">
        <p>BI Health</p>
        <p>Quartier Tanghin — Tél: +226 07 17 36 39</p>
        <p>www.bihealth.com</p>
      </div>
    </div>

    <h1>FICHE DE CONSULTATION</h1>

    <div class="section">
      <h3>Informations Patient</h3>
      <div class="info-line"><strong>Nom du patient :</strong> <?= htmlspecialchars($consultation['patient_nom']) ?></div>
      <div class="info-line"><strong>Date de consultation :</strong> <?= date('d/m/Y H:i', strtotime($consultation['date_consultation'])) ?></div>
      <div class="info-line"><strong>Médecin :</strong> <?= htmlspecialchars($consultation['medecin_nom']) ?></div>
    </div>

    <div class="section">
      <h3>Constantes Vitales</h3>
      <table>
        <tr>
          <th>Température</th>
          <th>Poids</th>
          <th>Taille</th>
          <th>IMC</th>
          <th>TA</th>
          <th>FC</th>
          <th>FR</th>
          <th>SaO₂</th>
          <th>Glycémie</th>
        </tr>
        <tr>
          <td><?= $consultation['temperature'] !== null 
                  ? htmlspecialchars($consultation['temperature']).' °C' 
                  : '—' ?></td>
          <td><?= $consultation['poids'] !== null 
                  ? htmlspecialchars($consultation['poids']).' kg' 
                  : '—' ?></td>
          <td><?= $consultation['taille'] !== null 
                  ? htmlspecialchars($consultation['taille']).' cm' 
                  : '—' ?></td>
          <td><?= $consultation['imc'] !== null 
                  ? htmlspecialchars($consultation['imc']).' kg/m²' 
                  : '—' ?></td>
          <td><?= $consultation['tension_arterielle'] !== null 
                  ? htmlspecialchars($consultation['tension_arterielle']) 
                  : '—' ?></td>
          <td><?= $consultation['frequence_cardiaque'] !== null 
                  ? htmlspecialchars($consultation['frequence_cardiaque']).' bpm' 
                  : '—' ?></td>
          <td><?= $consultation['frequence_respiratoire'] !== null 
                  ? htmlspecialchars($consultation['frequence_respiratoire']).' cpm' 
                  : '—' ?></td>
          <td><?= $consultation['saturation_oxygene'] !== null 
                  ? htmlspecialchars($consultation['saturation_oxygene']).' %' 
                  : '—' ?></td>
          <td><?= $consultation['glycemie'] !== null 
                  ? htmlspecialchars($consultation['glycemie']).' g/L' 
                  : '—' ?></td>
        </tr>
      </table>
    </div>

    <div class="section">
      <h3>Éléments Cliniques</h3>
      <div class="info-line"><strong>Motif :</strong> <?= nl2br(htmlspecialchars($consultation['motif'])) ?></div>
      <div class="info-line"><strong>Symptômes :</strong> <?= nl2br(htmlspecialchars($consultation['symptomes'])) ?></div>
      <div class="info-line"><strong>Diagnostic :</strong> <?= nl2br(htmlspecialchars($consultation['diagnostic'])) ?></div>
      <div class="info-line"><strong>Observations :</strong> <?= nl2br(htmlspecialchars($consultation['observations'])) ?></div>
    </div>

    <?php if (!empty($ordonnance)): ?>
      <div class="section">
        <h3>Ordonnance</h3>
        <div class="info-line"><strong>Type :</strong> <?= htmlspecialchars($ordonnance['type_ordonnance']) ?></div>
        <div class="info-line"><strong>Instructions :</strong> <?= nl2br(htmlspecialchars($ordonnance['instructions'])) ?></div>
        <?php if (!empty($medicaments)): ?>
          <table>
            <tr><th>Médicament</th><th>Posologie</th><th>Durée</th></tr>
            <?php foreach ($medicaments as $m): ?>
              <tr>
                <td><?= htmlspecialchars($m['medicament']) ?></td>
                <td><?= htmlspecialchars($m['posologie']) ?></td>
                <td><?= htmlspecialchars($m['duree']) ?></td>
              </tr>
            <?php endforeach; ?>
          </table>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="footer">
      <em>Fiche générée automatiquement le <?= date('d/m/Y à H:i') ?>. Pour usage médical uniquement.</em>
    </div>
  </div>

  <div id="downloadButton">
    <button onclick="downloadPDF()">Télécharger la fiche PDF</button>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
  <script>
    function downloadPDF() {
      const element = document.getElementById('fiche_consultation');
      html2pdf().from(element).save('consultation_<?= $id_consult ?>.pdf');
    }
  </script>
</body>
</html>