<?php
// pages/statistique.php
session_start();
if (empty($_SESSION['id'])) {
    header('Location: ../index.php?erreur=3');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Dashboard – Clinique</title>

  <?php include "inclusion_haut.php"; ?>

  <!-- Bootstrap pour mise en page -->
  <link
    rel="stylesheet"
    href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"
  />

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <!-- Styles spécifiques aux KPI et graphiques -->
  <style>
    .chart-container {
      position: relative;
      height: 250px;
      width: 100%;
    }
    .kpi-container {
      position: relative;
      height: 120px;
      width: 100%;
      border-radius: 8px;
      padding: 15px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      border: none;
      color: white;
      transition: transform 0.3s ease;
    }
    .kpi-container:hover {
      transform: translateY(-5px);
    }
    .kpi-patients {
      background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    }
    .kpi-consultations {
      background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
    }
    .kpi-hospitalisations {
      background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
    }
    .kpi-revenue {
      background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%);
    }
    .stats-value {
      font-size: 28px;
      font-weight: bold;
      text-align: center;
      color: white;
      margin-bottom: 5px;
    }
    .stats-label {
      text-align: center;
      color: rgba(255, 255, 255, 0.8);
      font-weight: 500;
      font-size: 14px;
    }
    .badge-custom {
      font-size: 14px;
      padding: 5px 10px;
      margin-right: 5px;
      color: white;
    }
    .kpi-square {
      width: 100%;
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
    }
    .kpi-icon {
      font-size: 28px;
      margin-bottom: 10px;
      color: white;
      opacity: 0.8;
    }
  </style>
</head>

<body>
  <?php include "loader.php"; ?>

  <div id="main-wrapper">
    <?php include "entete.php"; ?>
    <?php
      switch ($_SESSION['type_compte']) {
        case "Super Administrateur": include("menu.php"); break;
        case "Secretaire":           include("menu_secretaire.php"); break;
        case "Medecin":              include("menu_medecin.php"); break;
        case "Comptable":            include("menu_comptable.php"); break;
        case "Laborantin":           include("menu_laborantin.php"); break;
      }
    ?>

    <div class="content-body">
      <div class="container-fluid mt-4">
        <h2 class="mb-4">Tableau de bord & Statistique</h2>

        <!-- KPI carrés colorés -->
        <div class="row text-center mb-5">
          <div class="col-md-3">
            <div class="kpi-container kpi-patients">
              <div class="kpi-square">
                <div class="kpi-icon"><i class="fas fa-users"></i></div>
                <div class="stats-value" id="kpiTotalPatientsValue">0</div>
                <div class="stats-label">Total patients</div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="kpi-container kpi-consultations">
              <div class="kpi-square">
                <div class="kpi-icon"><i class="fas fa-stethoscope"></i></div>
                <div class="stats-value" id="kpiConsultationsValue">0</div>
                <div class="stats-label">Consultations</div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="kpi-container kpi-hospitalisations">
              <div class="kpi-square">
                <div class="kpi-icon"><i class="fas fa-procedures"></i></div>
                <div class="stats-value" id="kpiHospitalisationsValue">0</div>
                <div class="stats-label">Hospitalisations</div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="kpi-container kpi-revenue">
              <div class="kpi-square">
                <div class="kpi-icon"><i class="fas fa-money-bill-wave"></i></div>
                <div class="stats-value" id="kpiRevenueValue">0 FCFA</div>
                <div class="stats-label">CA mensuel</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Graphiques détaillés -->
        <div class="row">
          <div class="col-md-4 mb-4">
            <div class="card p-3">
              <h5>Répartition par sexe</h5>
              <div class="chart-container">
                <canvas id="genderChart"></canvas>
              </div>
              <div id="genderChartValues" class="mt-3 text-center"></div>
            </div>
          </div>
          <div class="col-md-4 mb-4">
            <div class="card p-3">
              <h5>Âge des patients</h5>
              <div class="chart-container">
                <canvas id="ageChart"></canvas>
              </div>
              <div id="ageChartValues" class="mt-3 text-center"></div>
            </div>
          </div>
          <div class="col-md-4 mb-4">
            <div class="card p-3">
              <h5>Assurés vs Non assurés</h5>
              <div class="chart-container">
                <canvas id="assuranceChart"></canvas>
              </div>
              <div id="assuranceChartValues" class="mt-3 text-center"></div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-4">
            <div class="card p-3">
              <h5>Nouveaux patients / mois</h5>
              <div class="chart-container">
                <canvas id="newPatientsChart"></canvas>
              </div>
              <div id="newPatientsChartValues" class="mt-3 text-center"></div>
            </div>
          </div>
          <div class="col-md-6 mb-4">
            <div class="card p-3">
              <h5>Chiffre d'affaires / mois</h5>
              <div class="chart-container">
                <canvas id="revenueChart"></canvas>
              </div>
              <div id="revenueChartValues" class="mt-3 text-center"></div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-4">
            <div class="card p-3">
              <h5>Modes de paiement</h5>
              <div class="chart-container">
                <canvas id="paymentChart"></canvas>
              </div>
              <div id="paymentChartValues" class="mt-3 text-center"></div>
            </div>
          </div>
          <div class="col-md-6 mb-4">
            <div class="card p-3">
              <h5>Statut hospitalisation</h5>
              <div class="chart-container">
                <canvas id="hospStatusChart"></canvas>
              </div>
              <div id="hospStatusChartValues" class="mt-3 text-center"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <?php include "footer.php"; ?>
  </div>

  <?php include "inclusion_bas.php"; ?>

  <!-- Font Awesome pour les icônes -->
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

  <script>
    /** Helper pour charger les données des KPI **/
    async function fetchKpiData(endpoint) {
      try {
        const res = await fetch(endpoint);
        if (!res.ok) {
          throw new Error(`HTTP error! status: ${res.status}`);
        }
        const json = await res.json();
        return json.values.length > 0 ? json.values[0] : 0;
      } catch (error) {
        console.error('Error loading KPI data:', error);
        return 0;
      }
    }

    /** Helper pour charger un graphique Chart.js **/
    async function loadChart(endpoint, ctx, type, options = {}) {
      try {
        const res = await fetch(endpoint);
        if (!res.ok) {
          throw new Error(`HTTP error! status: ${res.status}`);
        }
        const json = await res.json();
        
        // Créer le graphique
        const chart = new Chart(ctx, {
          type: type,
          data: {
            labels: json.labels,
            datasets: [{
              data: json.values,
              backgroundColor: options.colors || [
                '#4e73df','#1cc88a','#f6c23e','#e74a3b','#36b9cc'
              ],
              borderColor: options.borderColor || '#fff',
              borderWidth: options.borderWidth || 1
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: 'bottom',
                labels: {
                  boxWidth: 12
                }
              },
              tooltip: {
                enabled: true
              }
            },
            ...(options.config || {})
          }
        });
        
        // Retourner les données pour affichage des valeurs
        return { labels: json.labels, values: json.values };
      } catch (error) {
        console.error('Error loading chart:', error);
        return { labels: [], values: [] };
      }
    }

    function displayValues(containerId, labels, values) {
      const container = document.getElementById(containerId);
      if (!container) return;
      
      container.innerHTML = '';
      
      labels.forEach((label, index) => {
        const badge = document.createElement('span');
        badge.className = 'badge badge-custom mr-2';
        badge.style.backgroundColor = [
          '#4e73df','#1cc88a','#f6c23e','#e74a3b','#36b9cc'
        ][index % 5];
        badge.textContent = `${label}: ${values[index]}`;
        container.appendChild(badge);
      });
    }

    function formatCurrency(value) {
      return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'XOF',
        minimumFractionDigits: 0
      }).format(value);
    }

    window.addEventListener('load', async () => {
      try {
        // Chargement des KPI
        const [totalPatients, consultations, hospitalisations, revenue] = await Promise.all([
          fetchKpiData('../api/modules/stats_total_patients.php'),
          fetchKpiData('../api/modules/stats_consultations.php'),
          fetchKpiData('../api/modules/stats_hospitalisations.php'),
          fetchKpiData('../api/modules/stats_revenue_month.php')
        ]);

        document.getElementById('kpiTotalPatientsValue').textContent = totalPatients;
        document.getElementById('kpiConsultationsValue').textContent = consultations;
        document.getElementById('kpiHospitalisationsValue').textContent = hospitalisations;
        document.getElementById('kpiRevenueValue').textContent = formatCurrency(revenue);

        // Graphiques détaillés
        const genderData = await loadChart(
          '../api/modules/stats_patients_gender.php',
          document.getElementById('genderChart').getContext('2d'),
          'pie'
        );
        displayValues('genderChartValues', genderData.labels, genderData.values);

        const ageData = await loadChart(
          '../api/modules/stats_patients_age.php',
          document.getElementById('ageChart').getContext('2d'),
          'bar',
          { 
            config: { 
              scales: { 
                y: { 
                  beginAtZero: true,
                  ticks: {
                    precision: 0
                  }
                } 
              } 
            } 
          }
        );
        displayValues('ageChartValues', ageData.labels, ageData.values);

        const assuranceData = await loadChart(
          '../api/modules/stats_patients_assurance.php',
          document.getElementById('assuranceChart').getContext('2d'),
          'pie'
        );
        displayValues('assuranceChartValues', assuranceData.labels, assuranceData.values);

        const newPatientsData = await loadChart(
          '../api/modules/stats_new_patients.php',
          document.getElementById('newPatientsChart').getContext('2d'),
          'bar',
          { 
            config: { 
              scales: { 
                y: { 
                  beginAtZero: true,
                  ticks: {
                    precision: 0
                  }
                } 
              } 
            } 
          }
        );
        displayValues('newPatientsChartValues', newPatientsData.labels, newPatientsData.values);

        const revenueData = await loadChart(
          '../api/modules/stats_revenue.php',
          document.getElementById('revenueChart').getContext('2d'),
          'bar',
          { 
            config: { 
              scales: { 
                y: { 
                  beginAtZero: true,
                  ticks: {
                    callback: function(value) {
                      return formatCurrency(value);
                    }
                  }
                } 
              } 
            } 
          }
        );
        displayValues('revenueChartValues', revenueData.labels, revenueData.values);

        const paymentData = await loadChart(
          '../api/modules/stats_payment_methods.php',
          document.getElementById('paymentChart').getContext('2d'),
          'doughnut'
        );
        displayValues('paymentChartValues', paymentData.labels, paymentData.values);

        const hospStatusData = await loadChart(
          '../api/modules/stats_hospitalisation_status.php',
          document.getElementById('hospStatusChart').getContext('2d'),
          'pie'
        );
        displayValues('hospStatusChartValues', hospStatusData.labels, hospStatusData.values);

      } catch (error) {
        console.error('Error initializing dashboard:', error);
      }
    });
  </script>
</body>
</html>