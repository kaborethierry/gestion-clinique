<?php
session_start();

// Contrôle d'accès : Super Administrateur et Comptable
if (
    empty($_SESSION['id']) ||
    !in_array($_SESSION['type_compte'], ['Super Administrateur', 'Comptable'], true)
) {
    session_unset();
    session_destroy();
    header('Location: ../index.php?erreur=3');
    exit();
}

// Vérifier l'ID de la facture en paramètre
if (!isset($_GET['id_facture']) || !is_numeric($_GET['id_facture'])) {
    header('Location: ../../pages/facturation.php');
    exit();
}

$idFact = (int) $_GET['id_facture'];

require __DIR__ . '/connect_db_pdo.php';

// Charger la facture + infos patient + assurance + dernier motif d'hospitalisation
$stmt = $bdd->prepare("
    SELECT
        f.*,
        p.numero_dossier,
        p.nom           AS patient_nom,
        p.prenom        AS patient_prenom,
        p.assurance_statut,
        p.assurance_compagnie,
        p.numero_police,
        p.type_couverture,
        p.taux_couverture,
        (
          SELECT h.motif
            FROM hospitalisation h
           WHERE h.id_patient = f.id_patient
             AND h.supprimer  = 'NON'
           ORDER BY h.id_hosp DESC
           LIMIT 1
        ) AS motif_hospitalisation
    FROM facturation f
    JOIN patient p ON f.id_patient = p.id
    WHERE f.id_facture = ?
      AND f.supprimer   = 'NON'
");
$stmt->execute([$idFact]);
$fact = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$fact) {
    die('Facture introuvable.');
}

function fmtDate(string $dt): string {
    return date('d/m/Y H:i', strtotime($dt));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Facture n°<?= $idFact ?></title>
  <style>
    body { margin:0; padding:0; font-family:Arial,sans-serif; background:#f0f0f0; }
    .page { width:210mm; min-height:297mm; margin:10px auto; padding:20mm;
            background:#fff; box-shadow:0 0 5px rgba(0,0,0,0.1);
            box-sizing:border-box; position:relative; }
    .header { display:flex; justify-content:space-between; align-items:center; }
    .header img { height:60px; }
    .clinic-info { text-align:right; font-size:13px; color:#555; }
    h1 { text-align:center; margin:20px 0; font-size:24px; color:#007bff; }
    .section { margin-bottom:20px; }
    .section .info { font-size:14px; margin:6px 0; }
    .info strong { display:inline-block; width:160px; }
    table { width:100%; border-collapse:collapse; margin-top:10px; }
    th, td { border:1px solid #ddd; padding:8px; font-size:13px; text-align:center; }
    th { background:#f9f9f9; }
    .totals { margin-top:20px; float:right; width:320px; }
    .totals table { width:100%; }
    .totals td { border:none; padding:6px; font-size:14px; }
    .totals td.label { text-align:left; }
    .totals td.value { text-align:right; }
    .footer { position:absolute; bottom:20mm; left:20mm; font-size:12px; color:#777; }
    #downloadBtn { text-align:center; margin:30px 0; }
    #downloadBtn button { background:#007bff; color:#fff; border:none;
                          padding:10px 20px; font-size:15px; cursor:pointer;
                          border-radius:4px; }
    #downloadBtn button:hover { background:#0056b3; }
  </style>
</head>
<body>

  <div class="page" id="print_facture">
    <div class="header">
      <img src="../../pages/images/login.png" alt="Logo Clinique">
      <div class="clinic-info">
        <p>Clinic+ – Ouagadougou</p>
        <p>Tanghin – Tél : +226 07 17 36 39</p>
        <p>www.clinic+.bf</p>
      </div>
    </div>

    <h1>FACTURE n°<?= $idFact ?></h1>

    <div class="section">
      <div class="info">
        <strong>Dossier patient :</strong>
        <?= htmlspecialchars($fact['numero_dossier']) ?>
      </div>
      <div class="info">
        <strong>Patient :</strong>
        <?= htmlspecialchars($fact['patient_nom'] . ' ' . $fact['patient_prenom']) ?>
      </div>
      <div class="info">
        <strong>Date de paiement :</strong>
        <?= fmtDate($fact['date_paiement']) ?>
      </div>
      <div class="info">
        <strong>Paiement différé :</strong>
        <?= htmlspecialchars($fact['paiement_differe']) ?>
      </div>

      <div class="info">
        <strong>Assurance :</strong>
        <?= htmlspecialchars($fact['assurance_statut']) ?>
      </div>
      <?php if ($fact['assurance_statut'] === 'Assuré'): ?>
        <div class="info">
          <strong>Compagnie :</strong>
          <?= htmlspecialchars($fact['assurance_compagnie'] ?? '—') ?>
        </div>
        <div class="info">
          <strong>Numéro police :</strong>
          <?= htmlspecialchars($fact['numero_police'] ?? '—') ?>
        </div>
        <div class="info">
          <strong>Taux prise en charge :</strong>
          <?= number_format((float)$fact['taux_couverture'], 2, '.', ' ') ?> %
        </div>
        <div class="info">
          <strong>Type couverture :</strong>
          <?= htmlspecialchars($fact['type_couverture']) ?>
        </div>
      <?php endif; ?>

      <div class="info">
        <strong>Motif hospitalisation :</strong>
        <?= $fact['motif_hospitalisation']
             ? nl2br(htmlspecialchars($fact['motif_hospitalisation']))
             : '—' ?>
      </div>
    </div>

    <div class="section">
      <table>
        <thead>
          <tr>
            <th>Prestation</th>
            <th>Montant brut</th>
            <th>Taux (%)</th>
            <th>Part assurance</th>
            <th>Reste à charge</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><?= htmlspecialchars($fact['type_prestation']) ?></td>
            <td>
              <?= number_format((float)$fact['montant'], 2, '.', ' ') ?> FCFA
            </td>
            <td>
              <?= number_format((float)$fact['taux_couverture'], 2, '.', ' ') ?> %
            </td>
            <td>
              <?= number_format((float)$fact['part_assurance'], 2, '.', ' ') ?> FCFA
            </td>
            <td>
              <?= number_format((float)$fact['reste_a_charge'], 2, '.', ' ') ?> FCFA
            </td>
          </tr>
        </tbody>
      </table>

      <div class="totals">
        <table>
          <tr>
            <td class="label"><strong>Total facturé :</strong></td>
            <td class="value">
              <?= number_format((float)$fact['montant_total'], 2, '.', ' ') ?> FCFA
            </td>
          </tr>
          <tr>
            <td class="label"><strong>Moyen de paiement :</strong></td>
            <td class="value">
              <?= htmlspecialchars($fact['moyen_paiement']) ?>
            </td>
          </tr>
          <?php if (!empty($fact['reference_paiement'])): ?>
            <tr>
              <td class="label"><strong>Référence :</strong></td>
              <td class="value">
                <?= htmlspecialchars($fact['reference_paiement']) ?>
              </td>
            </tr>
          <?php endif; ?>
        </table>
      </div>
    </div>

    <div class="footer">
      <em>Document généré automatiquement le <?= date('d/m/Y à H:i') ?> - Usage interne uniquement</em>
    </div>
  </div>

  <div id="downloadBtn">
    <button onclick="downloadPDF()">Télécharger la facture PDF</button>
  </div>

  <script
    src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"
  ></script>
  <script>
    function downloadPDF() {
      const element = document.getElementById('print_facture');
      html2pdf()
        .set({
          margin:      10,
          filename:    'Facture_<?= $idFact ?>.pdf',
          image:       { type: 'jpeg', quality: 0.98 },
          html2canvas: { scale: 2 }
        })
        .from(element)
        .save();
    }
  </script>
</body>
</html>