import './bootstrap';

// Alpine.js is automatically loaded by Livewire 3.
// Do NOT import it here — duplicate Alpine instances break wire: directives.

// Chart.js — expose globally so Alpine.js components in Blade views can use it
import Chart from 'chart.js/auto';
window.Chart = Chart;
