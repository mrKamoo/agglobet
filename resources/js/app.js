import './bootstrap';

import Alpine from 'alpinejs';
import { createApp } from 'vue';

window.Alpine = Alpine;
Alpine.start();

// Expose Vue globally for inline scripts
window.Vue = { createApp };

// Import and expose components globally for use in Blade templates
import UserStatsModal from './components/UserStatsModal.vue';

window.UserStatsModal = UserStatsModal;

// Initialiser Vue 3
// Import de tous les composants Vue depuis le dossier components
const app = createApp({});

// Auto-enregistrement des composants Vue
const components = import.meta.glob('./components/**/*.vue', { eager: true });
Object.entries(components).forEach(([path, definition]) => {
    const componentName = path.split('/').pop().replace(/\.\w+$/, '');
    app.component(componentName, definition.default);
});

// Monter l'application Vue si un élément #app existe
if (document.getElementById('app')) {
    app.mount('#app');
}
