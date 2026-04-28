// Alpine standalone bundle para paginas publicas (no-Livewire).
// Reemplaza al historico /js/alpine.min.js. La diferencia clave es que aqui
// registramos los plugins ANTES de Alpine.start(), evitando el race con la
// version standalone vieja (3.13.5) que no garantizaba el orden de plugins.
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

Alpine.plugin(collapse);
window.Alpine = Alpine;
Alpine.start();
