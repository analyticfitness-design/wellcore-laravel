/**
 * Chart.js config factory para Metrics Tracker v2.
 * Cada función retorna una config completa lista para `new Chart(canvas, config)`.
 * Separar la config del componente facilita testing unitario sin canvas.
 */

export function useMetricsChart() {

  function setGlobalDefaults(Chart) {
    Chart.defaults.color = '#a3a3a3';
    Chart.defaults.borderColor = '#262626';
    Chart.defaults.font.family = "'Barlow', sans-serif";
    Chart.defaults.font.size = 11;
  }

  function weightChartConfig(entries, period = '90d') {
    const labels = entries.map(d => d.date);
    const values = entries.map(d => d.value);

    return {
      type: 'line',
      data: {
        labels,
        datasets: [{
          label: 'Peso (kg)',
          data: values,
          borderColor: '#DC2626',
          backgroundColor: 'rgba(220,38,38,0.08)',
          fill: true,
          tension: 0.35,
          pointRadius: 3,
          pointBackgroundColor: '#DC2626',
          pointBorderColor: '#DC2626',
          borderWidth: 2,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: { label: ctx => ` ${ctx.parsed.y} kg` },
          },
        },
        scales: {
          x: { grid: { display: false }, ticks: { maxTicksLimit: 8, font: { family: "'Barlow', sans-serif" } } },
          y: { beginAtZero: false, grid: { color: '#262626' }, ticks: { font: { family: "'JetBrains Mono', monospace" } } },
        },
      },
    };
  }

  function checkinChartConfig(weeklyCheckins) {
    const labels = weeklyCheckins.map(d => {
      const yw = String(d.week);
      return `S${parseInt(yw.slice(4), 10)}`;
    });

    return {
      type: 'bar',
      data: {
        labels,
        datasets: [{
          label: 'Check-ins',
          data: weeklyCheckins.map(d => d.cnt),
          backgroundColor: 'rgba(220,38,38,0.55)',
          borderColor: '#DC2626',
          borderWidth: 1,
          borderRadius: 4,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { display: false } },
          y: { beginAtZero: true, grid: { color: '#262626' }, ticks: { stepSize: 1 } },
        },
      },
    };
  }

  function compositionChartConfig(comp) {
    return {
      type: 'doughnut',
      data: {
        labels: ['Grasa', 'Músculo', 'Otro'],
        datasets: [{
          data: [comp.grasa, comp.musculo, comp.otro],
          backgroundColor: ['#DC2626', '#3B82F6', '#525252'],
          borderColor: '#171717',
          borderWidth: 2,
          hoverOffset: 6,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: true, position: 'bottom', labels: { padding: 12, boxWidth: 10 } },
          tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed}%` } },
        },
        cutout: '65%',
      },
    };
  }

  function trainingChartConfig(trainingVolume) {
    const labels = trainingVolume.map(d => {
      const yw = String(d.week);
      return `S${parseInt(yw.slice(4), 10)}`;
    });

    return {
      type: 'line',
      data: {
        labels,
        datasets: [{
          label: 'Sesiones',
          data: trainingVolume.map(d => d.sessions),
          borderColor: '#3B82F6',
          backgroundColor: 'rgba(59,130,246,0.08)',
          fill: true,
          tension: 0.3,
          pointRadius: 4,
          pointBackgroundColor: '#3B82F6',
          borderWidth: 2,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { display: false } },
          y: { beginAtZero: true, grid: { color: '#262626' }, ticks: { stepSize: 1 } },
        },
      },
    };
  }

  return { setGlobalDefaults, weightChartConfig, checkinChartConfig, compositionChartConfig, trainingChartConfig };
}
