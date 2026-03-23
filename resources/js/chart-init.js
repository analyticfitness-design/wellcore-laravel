import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

// WellCore chart defaults — Barlow font (font-data)
Chart.defaults.font.family = "'Barlow', sans-serif";
Chart.defaults.plugins.legend.labels.usePointStyle = true;
Chart.defaults.plugins.legend.labels.padding = 16;
Chart.defaults.plugins.tooltip.cornerRadius = 8;
Chart.defaults.plugins.tooltip.padding = 12;

// Dark mode support — function to apply correct colors
const applyChartTheme = () => {
    const isDark = document.documentElement.classList.contains('dark');

    Chart.defaults.color = isDark ? '#A1A1AA' : '#52525B';
    Chart.defaults.borderColor = isDark ? '#3F3F46' : '#E4E4E7';

    // Tooltip styling
    Chart.defaults.plugins.tooltip.backgroundColor = isDark ? '#27272A' : '#FAFAFA';
    Chart.defaults.plugins.tooltip.titleColor = isDark ? '#FAFAFA' : '#09090B';
    Chart.defaults.plugins.tooltip.bodyColor = isDark ? '#A1A1AA' : '#52525B';
    Chart.defaults.plugins.tooltip.borderColor = isDark ? '#3F3F46' : '#E4E4E7';
    Chart.defaults.plugins.tooltip.borderWidth = 1;

    // Re-render active charts (guard against null ctx during SPA navigation)
    Chart.instances && Object.values(Chart.instances).forEach(chart => {
        if (chart && chart.canvas && chart.ctx) {
            chart.update('none');
        }
    });
};

// Observe dark mode class changes on <html>
const observer = new MutationObserver(applyChartTheme);
observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

// Initial theme apply
applyChartTheme();

// Expose globally for Alpine.js components
window.Chart = Chart;

// Cleanup + re-init on Livewire navigation
document.addEventListener('livewire:navigated', () => {
    // Charts re-init via Alpine x-init automatically
});

// Stop all chart animations before SPA navigation to prevent rAF-after-destroy race
document.addEventListener('livewire:navigating', () => {
    Chart.instances && Object.values(Chart.instances).forEach(chart => {
        if (chart) {
            chart.stop();
        }
    });
});
