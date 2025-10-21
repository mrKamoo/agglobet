# Intégration Vue.js dans Agglobet

## Vue d'ensemble

Vue.js 3 a été intégré avec succès dans l'application Agglobet. Cette intégration coexiste avec Alpine.js, vous permettant d'utiliser les deux frameworks selon vos besoins.

## Configuration

### Dépendances installées
- `vue@^3.5` - Framework Vue.js 3
- `@vitejs/plugin-vue` - Plugin Vite pour Vue.js

### Fichiers modifiés
- `vite.config.js` - Configuration Vite avec le plugin Vue
- `resources/js/app.js` - Initialisation de Vue et auto-enregistrement des composants
- `package.json` - Ajout des dépendances Vue

## Structure des composants

Les composants Vue.js doivent être placés dans :
```
resources/js/components/
```

Tous les fichiers `.vue` dans ce dossier seront automatiquement enregistrés et disponibles dans vos templates Blade.

## Utilisation dans Blade

### 1. Créer un point de montage

Dans votre template Blade, ajoutez un élément avec l'id `app` :

```blade
<div id="app">
    <example-component></example-component>
</div>
```

### 2. Utiliser les composants

Les composants sont automatiquement enregistrés avec leur nom de fichier en kebab-case :
- `ExampleComponent.vue` → `<example-component>`
- `PredictionForm.vue` → `<prediction-form>`
- `LeaderboardTable.vue` → `<leaderboard-table>`

## Exemple de composant Vue

Créez un fichier dans `resources/js/components/MonComposant.vue` :

```vue
<template>
    <div class="p-4 bg-white rounded-lg shadow">
        <h2>{{ titre }}</h2>
        <button @click="action">Cliquez-moi</button>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const titre = ref('Mon Composant');

const action = () => {
    console.log('Action déclenchée');
};
</script>

<style scoped>
/* Styles spécifiques au composant */
</style>
```

## Composition API vs Options API

Vue.js 3 supporte les deux approches. L'exemple ci-dessus utilise la **Composition API** avec `<script setup>`, qui est la méthode recommandée.

### Composition API (recommandé)
```vue
<script setup>
import { ref, computed } from 'vue';

const count = ref(0);
const double = computed(() => count.value * 2);
</script>
```

### Options API (traditionnel)
```vue
<script>
export default {
    data() {
        return {
            count: 0
        }
    },
    computed: {
        double() {
            return this.count * 2;
        }
    }
}
</script>
```

## Communication avec Laravel

### 1. Props depuis Blade

Passez des données PHP aux composants Vue :

```blade
<div id="app" data-user="{{ json_encode($user) }}">
    <user-profile></user-profile>
</div>
```

Dans le composant :
```vue
<script setup>
import { onMounted, ref } from 'vue';

const user = ref(null);

onMounted(() => {
    const appEl = document.getElementById('app');
    user.value = JSON.parse(appEl.dataset.user);
});
</script>
```

### 2. Appels API

Utilisez Axios (déjà inclus via `bootstrap.js`) :

```vue
<script setup>
import axios from 'axios';
import { ref } from 'vue';

const predictions = ref([]);

const fetchPredictions = async () => {
    try {
        const response = await axios.get('/api/predictions');
        predictions.value = response.data;
    } catch (error) {
        console.error('Erreur:', error);
    }
};
</script>
```

## Coexistence avec Alpine.js

Alpine.js et Vue.js fonctionnent côte à côte. Utilisez-les selon vos besoins :

- **Alpine.js** : Interactivité simple et légère (toggles, dropdowns, etc.)
- **Vue.js** : Composants complexes avec état partagé, formulaires avancés, etc.

### Exemple mixte dans Blade
```blade
<div id="app">
    <!-- Composant Vue -->
    <prediction-form></prediction-form>
</div>

<!-- Alpine.js pour une fonctionnalité simple -->
<div x-data="{ open: false }">
    <button @click="open = !open">Toggle</button>
    <div x-show="open">Contenu</div>
</div>
```

## Test de l'intégration

Une page de test est disponible à l'URL :
```
http://localhost:8000/vue-test
```

Cette route peut être supprimée après vérification de l'intégration.

## Développement

### Démarrer le serveur de développement
```bash
npm run dev
# ou
composer dev  # Lance tous les services (serveur, queue, logs, vite)
```

### Construire pour la production
```bash
npm run build
```

## Recommandations d'utilisation dans Agglobet

### Cas d'usage idéaux pour Vue.js :

1. **Formulaire de prédiction** (`PredictionForm.vue`)
   - Validation en temps réel
   - Calcul automatique des points potentiels
   - État complexe du formulaire

2. **Tableau de classement** (`LeaderboardTable.vue`)
   - Tri et filtrage
   - Pagination
   - Mise à jour en temps réel

3. **Liste des matchs** (`GamesList.vue`)
   - Filtrage par journée/date
   - Recherche
   - Affichage conditionnel

4. **Dashboard admin**
   - Gestion des résultats en masse
   - Synchronisation API avec feedback en temps réel

### Cas d'usage pour Alpine.js :

1. Menus déroulants
2. Modales simples
3. Tabs et accordéons
4. Tooltips

## Ressources

- [Documentation Vue.js 3](https://vuejs.org/)
- [Composition API](https://vuejs.org/guide/extras/composition-api-faq.html)
- [Laravel + Vue.js](https://laravel.com/docs/11.x/vite#vue)
- [Vite](https://vitejs.dev/)

## Prochaines étapes

1. Convertir les formulaires de prédiction en composants Vue
2. Créer un composant de classement interactif
3. Développer un composant de sélection de matchs avec filtres
4. Ajouter des transitions et animations Vue
5. Implémenter le state management avec Pinia si nécessaire
