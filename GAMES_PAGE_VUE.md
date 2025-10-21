# Page des Matchs et Pronostics - Version Vue.js

## Vue d'ensemble

La page `/games` a été entièrement refactorisée avec Vue.js 3 pour offrir une expérience utilisateur dynamique et interactive sans rechargement de page.

## Fonctionnalités

### 🎯 Filtrage dynamique

1. **Par journée** : Sélection rapide de n'importe quelle journée de championnat
2. **Par statut** :
   - Tous les matchs
   - Matchs à venir
   - Matchs terminés
3. **Par recherche** : Recherche en temps réel par nom d'équipe

### ⚡ Interactions en temps réel

- **Pronostics sans rechargement** : Soumettre et modifier des pronostics instantanément
- **Notifications visuelles** : Confirmation immédiate des actions
- **Compteur de temps** : Affichage du temps restant avant le début du match
- **Badges de points** : Affichage coloré des points gagnés sur matchs terminés

### 🎨 Interface améliorée

- **Animations fluides** : Transitions douces entre les états
- **États de chargement** : Indicateurs visuels pendant les opérations
- **Gestion d'erreurs** : Messages clairs en cas de problème
- **Design responsive** : Optimisé pour mobile, tablette et desktop

## Architecture des composants

### Composants Vue.js créés

#### 1. `GamesList.vue` (Composant principal)
**Responsabilités** :
- Gestion de l'état global (filtres, chargement, erreurs)
- Appels API pour récupérer les matchs
- Rendu de la section filtres
- Orchestration des GameCard

**Props** : Aucune (composant racine)

**Événements** :
- Écoute `prediction-updated` des GameCard enfants

**État** :
```javascript
games: []                    // Liste des matchs
availableMatchdays: []       // Journées disponibles
season: null                 // Saison active
isLoading: false             // État de chargement
error: ''                    // Message d'erreur
filters: {                   // Filtres actifs
  matchday: null,
  status: '',
  search: ''
}
```

#### 2. `GameCard.vue` (Carte de match)
**Responsabilités** :
- Affichage des informations du match (équipes, logos, score, date)
- Calcul du temps restant avant le match
- Gestion des badges de points
- Orchestration du PredictionForm

**Props** :
```javascript
game: Object  // Données complètes du match
```

**Événements** :
- Émet `prediction-updated` quand un pronostic est soumis

**Computed** :
- `formatDate()` - Formatage de la date
- `formatTime()` - Formatage de l'heure
- `getTimeUntilMatch()` - Calcul du temps restant
- `getPointsBadgeClass()` - Style du badge de points

#### 3. `PredictionForm.vue` (Formulaire de pronostic)
**Responsabilités** :
- Affichage du formulaire de saisie des scores
- Validation des données
- Soumission AJAX du pronostic
- Gestion des états (loading, success, error)

**Props** :
```javascript
gameId: Number           // ID du match
userPrediction: Object   // Pronostic existant (si applicable)
canPredict: Boolean      // Autorisation de pronostiquer
isPast: Boolean          // Match passé ?
isFinished: Boolean      // Match terminé ?
```

**Événements** :
- Émet `prediction-updated` après soumission réussie

**État** :
```javascript
homeScore: Number        // Score domicile
awayScore: Number        // Score extérieur
isSubmitting: false      // Soumission en cours
error: ''                // Message d'erreur
successMessage: ''       // Message de succès
hasPrediction: false     // Pronostic existant ?
```

## API Endpoint

### `GET /api/games`

**Authentification** : Requise (middleware `auth` + `verified`)

**Paramètres de requête** :
- `matchday` (optional) : Filtre par journée (1-34)
- `status` (optional) : `finished` ou `upcoming`
- `search` (optional) : Recherche par nom d'équipe

**Réponse JSON** :
```json
{
  "games": [
    {
      "id": 1,
      "matchday": 5,
      "match_date": "2024-09-15T20:00:00+02:00",
      "match_date_formatted": "15/09/2024 20:00",
      "is_finished": false,
      "is_past": false,
      "home_score": null,
      "away_score": null,
      "home_team": {
        "id": 1,
        "name": "Paris Saint-Germain",
        "short_name": "PSG",
        "logo": "https://..."
      },
      "away_team": {
        "id": 2,
        "name": "Olympique de Marseille",
        "short_name": "OM",
        "logo": "https://..."
      },
      "user_prediction": {
        "id": 10,
        "home_score": 2,
        "away_score": 1,
        "points_earned": null
      },
      "can_predict": true
    }
  ],
  "season": {
    "id": 1,
    "name": "Ligue 1 2024-2025"
  },
  "matchdays": [1, 2, 3, 4, 5, ...]
}
```

## Flux de données

### Chargement initial
```
User accède à /games
    ↓
Blade rend la page avec <games-list>
    ↓
GamesList.vue monté → onMounted()
    ↓
API GET /api/games
    ↓
Données reçues → games[] mis à jour
    ↓
Rendu de GameCard pour chaque match
```

### Soumission de pronostic
```
User saisit scores et clique "Pronostiquer"
    ↓
PredictionForm.vue → submitPrediction()
    ↓
API POST /games/{id}/predictions
    ↓
Succès → Émet 'prediction-updated'
    ↓
GameCard reçoit l'événement → Propage à GamesList
    ↓
GamesList met à jour game.user_prediction
    ↓
Notification de succès affichée
```

### Filtrage
```
User change un filtre
    ↓
Watch détecte le changement dans filters
    ↓
Appel loadGames() avec nouveaux paramètres
    ↓
API GET /api/games?matchday=5&status=upcoming
    ↓
games[] mis à jour
    ↓
Re-rendu automatique de la liste
```

## Optimisations

### Performance
1. **Chargement initial** : Une seule requête API au montage
2. **Filtrage côté serveur** : Requêtes API filtrées (pas de filtrage client massif)
3. **Transitions légères** : CSS transitions pour fluidité
4. **Eager loading** : Relations Eloquent préchargées (homeTeam, awayTeam, predictions)

### UX
1. **Debouncing implicite** : Watch réactif pour éviter les requêtes excessives
2. **États de chargement** : Spinner pendant les opérations
3. **Gestion d'erreurs** : Messages clairs + bouton "Réessayer"
4. **Feedback visuel** : Notifications, badges de couleur, animations

## Compatibilité

### Ancien système
L'ancien système Blade/Alpine.js a été **remplacé** par la version Vue.js, mais :
- Les routes sont conservées (`/games`)
- L'API de pronostic reste identique (`POST /games/{game}/predictions`)
- Le middleware d'authentification est inchangé
- La base de données n'a pas été modifiée

### Coexistence Alpine.js
Alpine.js reste actif pour les autres pages. Les deux frameworks coexistent sans conflit.

## Tests manuels recommandés

### ✅ Checklist de tests

- [ ] Affichage de la liste des matchs au chargement
- [ ] Filtrage par journée fonctionne
- [ ] Filtrage par statut (à venir / terminés)
- [ ] Recherche d'équipe en temps réel
- [ ] Bouton "Réinitialiser les filtres"
- [ ] Soumission d'un nouveau pronostic
- [ ] Modification d'un pronostic existant
- [ ] Message de confirmation après soumission
- [ ] Gestion d'erreurs (API indisponible, validation)
- [ ] Affichage des logos des équipes
- [ ] Fallback si logo manquant
- [ ] Affichage du temps restant avant match
- [ ] Badge de points sur matchs terminés
- [ ] Responsive design (mobile, tablette, desktop)
- [ ] Pronostics bloqués sur matchs passés
- [ ] Aucune régression sur les autres pages

## Évolutions futures possibles

### Court terme
- [ ] Pagination ou scroll infini si beaucoup de matchs
- [ ] Tri personnalisable (date, équipe, points)
- [ ] Statistiques utilisateur en temps réel
- [ ] Animation du compteur de temps (mise à jour chaque minute)

### Moyen terme
- [ ] WebSocket pour mises à jour en direct des scores
- [ ] Comparaison avec pronostics d'autres utilisateurs
- [ ] Historique des modifications de pronostics
- [ ] Mode "favori" pour suivre certaines équipes

### Long terme
- [ ] State management avec Pinia (si l'app grandit)
- [ ] Tests unitaires avec Vitest
- [ ] Tests E2E avec Playwright
- [ ] PWA pour notifications push

## Dépannage

### Les composants ne se chargent pas
1. Vérifier que `npm run build` a réussi
2. Vider le cache Laravel : `php artisan cache:clear`
3. Vérifier les logs du navigateur (F12 → Console)

### Les pronostics ne se soumettent pas
1. Vérifier l'authentification (middleware)
2. Vérifier le CSRF token (géré par axios/bootstrap.js)
3. Consulter les logs Laravel : `storage/logs/laravel.log`

### Les filtres ne fonctionnent pas
1. Vérifier les paramètres de requête dans l'onglet Network
2. Vérifier que l'API `/api/games` répond correctement
3. Tester l'endpoint directement : `GET /api/games?matchday=1`

## Fichiers modifiés/créés

### Nouveaux fichiers
- `resources/js/components/GamesList.vue`
- `resources/js/components/GameCard.vue`
- `resources/js/components/PredictionForm.vue`
- `GAMES_PAGE_VUE.md` (ce fichier)

### Fichiers modifiés
- `app/Http/Controllers/GameController.php` - Ajout de `getGames()`
- `routes/web.php` - Ajout route `/api/games`
- `resources/views/games/index.blade.php` - Utilisation de Vue.js

### Fichiers inchangés
- `app/Http/Controllers/PredictionController.php` - API de pronostic conservée
- `app/Models/Game.php`, `Prediction.php` - Modèles inchangés
- Migrations - Base de données inchangée
