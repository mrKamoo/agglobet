# Classement de la Ligue 1

## Vue d'ensemble

La page de classement de la Ligue 1 affiche le classement officiel du championnat français en temps réel, synchronisé automatiquement depuis l'API Football-Data.org.

## Fonctionnalités

### Affichage du classement
- Classement complet des 18 équipes de Ligue 1
- Position, logo et nom de chaque équipe
- Statistiques détaillées :
  - Matchs joués (MJ)
  - Matchs gagnés (G)
  - Matchs nuls (N)
  - Matchs perdus (P)
  - Buts pour (BP)
  - Buts contre (BC)
  - Différence de buts (Diff)
  - Points totaux (Pts)

### Indicateurs visuels
- **Zone verte** : Top 3 (qualifications Ligue des Champions)
- **Zone rouge** : 3 dernières places (zone de relégation)
- **Flèches** : Indicateurs montants/descendants pour les positions clés
- **Couleurs** : Différence de buts positive (vert) / négative (rouge)

### Mise en cache
Le classement est mis en cache pendant **1 heure** pour optimiser les performances et respecter les limites de l'API (10 requêtes/minute sur le tier gratuit).

## Accès

### Route publique
```
GET /standings
```

La page est accessible à tous les utilisateurs (connectés ou non).

### Navigation
- Menu principal : "Classement Ligue 1"
- Menu mobile : "Classement Ligue 1"

## Architecture technique

### Contrôleur
**StandingsController** (`app/Http/Controllers/StandingsController.php`)
- Méthode `index()` : Récupère et affiche le classement
- Utilise le cache Laravel (TTL: 1 heure)
- Gestion des erreurs avec message utilisateur

### Service
**FootballDataService** (`app/Services/FootballDataService.php`)
- Méthode `fetchStandings(?Season $season)` : Récupère le classement depuis l'API
- Endpoint API : `GET /competitions/2015/standings`
- Retourne uniquement le classement général (type: 'TOTAL')
- Filtrage optionnel par saison

### Vue
**standings/index.blade.php** (`resources/views/standings/index.blade.php`)
- Template Blade avec layout `app`
- Table responsive avec Tailwind CSS
- Légende des abréviations
- Informations sur la source des données

## API Football-Data.org

### Endpoint utilisé
```
GET https://api.football-data.org/v4/competitions/2015/standings
```

**Paramètres :**
- `season` (optionnel) : Année de début de saison (ex: 2024)

**Headers requis :**
- `X-Auth-Token: [VOTRE_CLE_API]`

### Structure de la réponse
```json
{
  "competition": {...},
  "season": {
    "startDate": "2024-08-01",
    "endDate": "2025-05-31",
    "currentMatchday": 15
  },
  "standings": [
    {
      "type": "TOTAL",
      "table": [
        {
          "position": 1,
          "team": {
            "id": 524,
            "name": "Paris Saint-Germain FC",
            "shortName": "Paris SG",
            "tla": "PSG",
            "crest": "https://..."
          },
          "playedGames": 15,
          "won": 12,
          "draw": 2,
          "lost": 1,
          "points": 38,
          "goalsFor": 40,
          "goalsAgainst": 12,
          "goalDifference": 28
        },
        ...
      ]
    }
  ]
}
```

### Limites de l'API
- **Tier gratuit** : 10 requêtes par minute
- **Données** : Saison en cours uniquement (pas d'historique des saisons passées)
- **Mise à jour** : En temps réel après chaque match

## Gestion du cache

### Configuration
Le classement est automatiquement mis en cache pour **1 heure** (3600 secondes).

### Invalidation manuelle
Si vous souhaitez forcer la mise à jour du classement, utilisez la console Artisan :

```bash
php artisan cache:forget ligue1_standings
```

### Désactivation du cache (développement)
Pour désactiver temporairement le cache dans le contrôleur, modifiez la méthode `index()` :

```php
// AVANT (avec cache)
$standingsData = Cache::remember('ligue1_standings', 3600, function () {
    return $this->footballDataService->fetchStandings();
});

// APRÈS (sans cache)
$standingsData = $this->footballDataService->fetchStandings();
```

## Design et UX

### Responsive
- Table horizontalement scrollable sur mobile
- Colonnes optimisées pour petits écrans
- Menu burger avec lien vers le classement

### Accessibilité
- Headers de tableau sémantiques (`<th scope="col">`)
- Textes alternatifs sur les logos
- Couleurs avec contraste suffisant
- Légende explicative des abréviations

### Performance
- Logos des équipes chargés en lazy loading (navigateur natif)
- Cache de 1 heure pour réduire les appels API
- Table paginée (non implémenté, car seulement 18 équipes)

## Différences avec le classement joueurs

| Fonctionnalité | Classement Ligue 1 | Classement Joueurs |
|----------------|--------------------|--------------------|
| **Route** | `/standings` | `/leaderboard` |
| **Source** | API Football-Data.org | Base de données locale |
| **Données** | Équipes de Ligue 1 | Utilisateurs de l'application |
| **Mise à jour** | Temps réel (API) | Après calcul des points |
| **Cache** | 1 heure | Aucun |
| **Accès** | Public | Public |

## Améliorations possibles

### Court terme
- [ ] Afficher la forme récente (5 derniers matchs : V-N-D)
- [ ] Ajouter un filtre par journée
- [ ] Afficher les buteurs et passeurs

### Moyen terme
- [ ] Comparaison avec la saison précédente
- [ ] Graphique d'évolution de position
- [ ] Notifications sur changements de position

### Long terme
- [ ] Historique des saisons
- [ ] Prédictions de classement final
- [ ] Intégration avec les pronostics utilisateurs

## Dépannage

### Erreur "Impossible de récupérer le classement"

**Causes possibles :**
1. Clé API manquante ou invalide
2. Limite de requêtes API dépassée (10/min)
3. API temporairement indisponible
4. Problème de connexion internet

**Solutions :**
```bash
# 1. Vérifier la clé API
php artisan config:clear
php artisan cache:clear

# 2. Vérifier les logs
php artisan pail

# 3. Tester manuellement l'API
curl -H "X-Auth-Token: VOTRE_CLE" \
  https://api.football-data.org/v4/competitions/2015/standings

# 4. Invalider le cache
php artisan cache:forget ligue1_standings
```

### Logos manquants

Les logos sont fournis par l'API Football-Data.org. Si un logo est manquant :
1. L'API ne fournit pas le logo pour cette équipe
2. Le CDN de Football-Data.org est temporairement inaccessible

La vue affiche uniquement le nom de l'équipe si le logo est absent.

### Affichage incorrectdes données

Si les positions ou statistiques semblent incorrectes :
1. Invalidez le cache : `php artisan cache:forget ligue1_standings`
2. Rechargez la page pour obtenir des données fraîches
3. Vérifiez sur le site officiel de la Ligue 1 pour confirmation

## Ressources

- [Documentation API Football-Data.org](https://www.football-data.org/documentation/api)
- [Endpoint Standings](https://www.football-data.org/documentation/api#standings)
- [Site officiel Ligue 1](https://www.ligue1.fr/)
- [Cache Laravel](https://laravel.com/docs/12.x/cache)
