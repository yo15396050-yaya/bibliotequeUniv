/**
 * Point d'entrée des scripts de l'application.
 * Tout est empaqueté localement : l'interface reste fonctionnelle sans accès
 * à un CDN externe.
 */
import './bootstrap';

import * as bootstrap from 'bootstrap';
import Swal from 'sweetalert2';
import Chart from 'chart.js/auto';

// Exposés pour les scripts inline des vues Blade.
window.bootstrap = bootstrap;
window.Swal = Swal;
window.Chart = Chart;
