<?php
// /api/modules/fiche_resultats.php

session_start();
if (empty($_SESSION['id'])) {
    header('Location: ../../index.php?erreur=3');
    exit();
}

if (!isset($_GET['id_consultation'])) {
    header('Location: ../../pages/laboratoire.php?error=consultation_introuvable');
    exit();
}

$id_consult = (int) $_GET['id_consultation'];
require __DIR__ . '/connect_db_pdo.php';

// Récupérer les résultats d'examen pour cette consultation
$stmt = $bdd->prepare("
  SELECT
    r.id_resultat,
    r.id_examen,
    el.type_examen       AS examen_type,
    r.contenu_texte,
    r.fichier,
    r.date_resultat
  FROM resultat_examen AS r
  JOIN examen_labo AS el
    ON r.id_examen = el.id_examen
  WHERE el.id_consultation = ?
    AND r.supprimer = 'NON'
    AND el.supprimer = 'NON'
  ORDER BY r.date_resultat ASC
");
$stmt->execute([$id_consult]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Résultats d'examen — Consultation #<?= $id_consult ?></title>
  <style>
    body { margin:0; padding:0; font-family: Arial, sans-serif; color:#333; }
    .page {
      width:210mm; min-height:297mm; padding:20mm;
      margin:auto; box-sizing:border-box; background:#fff; position:relative;
    }
    .header { display:flex; justify-content:space-between; margin-bottom:15px; }
    .header img { height:80px; }
    .clinic-info { text-align:right; font-size:14px; }
    h1 {
      text-align:center; margin:20px 0;
      font-size:26px; color:#007bff;
    }
    .section { margin:20px 0; }
    .section h3 {
      font-size:18px; color:#444;
      border-bottom:1px solid #ccc; padding-bottom:5px;
      margin-bottom:10px;
    }
    .info-line { margin:6px 0; font-size:15px; }
    .info-line strong { display:inline-block; width:180px; }
    .info-line img { max-width:100%; margin-top:10px; }
    .footer {
      position:absolute; bottom:20mm; right:20mm;
      font-size:13px; color:#666;
    }
    #downloadButton { text-align:center; margin:20px 0; }
    #downloadButton button {
      padding:10px 20px; background:#007bff; color:#fff;
      border:none; font-size:16px; cursor:pointer;
    }
    #downloadButton button:hover { background:#0056b3; }
    a.file-link { color:#007bff; text-decoration:none; }
    a.file-link:hover { text-decoration:underline; }
  </style>
</head>
<body>

  <div class="page" id="fiche_resultats">
    <div class="header">
      <img src="../../pages/images/login.png" alt="Logo Clinique">
      <div class="clinic-info">
        <p>Clinic + Ouagadougou</p>
        <p>Quartier Tanghin — Tél: +226 07 17 36 39</p>
        <p>www.clinic+.bf</p>
      </div>
    </div>

    <h1>RÉSULTATS D'EXAMEN</h1>

    <?php if (empty($results)): ?>
      <p>Aucun résultat d'examen disponible pour cette consultation.</p>
    <?php else: ?>
      <?php foreach ($results as $r): ?>
        <div class="section">
          <h3>
            Examen : <?= htmlspecialchars($r['examen_type']) ?>
            — le <?= date('d/m/Y H:i', strtotime($r['date_resultat'])) ?>
          </h3>

          <?php if (!empty($r['contenu_texte'])): ?>
            <div class="info-line">
              <strong>Contenu :</strong><br>
              <?= nl2br(htmlspecialchars($r['contenu_texte'])) ?>
            </div>
          <?php endif; ?>

          <?php if (!empty($r['fichier'])):
            $ext     = strtolower(pathinfo($r['fichier'], PATHINFO_EXTENSION));
            $isImage = in_array($ext, ['jpg','jpeg','png','gif']);
          ?>
            <div class="info-line">
              <strong><?= $isImage ? 'Image jointe' : 'Fichier joint' ?> :</strong><br>
              <?php if ($isImage): ?>
                <img
                  src="../../uploads/resultats/<?= htmlspecialchars($r['fichier']) ?>"
                  alt="Résultat examen <?= htmlspecialchars($r['examen_type']) ?>">
              <?php else: ?>
                <a
                  class="file-link"
                  href="../../uploads/resultats/<?= htmlspecialchars($r['fichier']) ?>"
                  target="_blank">
                  <?= htmlspecialchars($r['fichier']) ?>
                </a>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <div class="footer">
      <em>Fiche générée automatiquement le <?= date('d/m/Y à H:i') ?>. Usage médical uniquement.</em>
    </div>
  </div>

  <div id="downloadButton">
    <button onclick="downloadPDF()">Télécharger les résultats PDF</button>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
  <script>
    function downloadPDF() {
      html2pdf()
        .set({
          margin:       0,
          filename:     'resultats_consultation_<?= $id_consult ?>.pdf',
          image:        { type: 'jpeg', quality: 0.98 },
          html2canvas:  { scale: 2 },
          jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
        })
        .from(document.getElementById('fiche_resultats'))
        .save();
    }
  </script>

</body>
</html>