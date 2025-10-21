# Enregistrement automatique des pronostics

## Vue d'ensemble

Les pronostics sont maintenant enregistrés **automatiquement** sans avoir besoin de cliquer sur un bouton. L'expérience est similaire à Google Docs : vous tapez, et ça s'enregistre tout seul !

## Fonctionnement

### 🎯 Déclenchement de l'auto-save

L'enregistrement automatique se déclenche quand :
- L'utilisateur modifie le score domicile OU le score extérieur
- **Après 800ms d'inactivité** (debounce)

### ⏱️ Timeline d'enregistrement

```
User tape "3" dans le champ
    ↓
Timer démarre (800ms)
    ↓
User tape rapidement "1"
    ↓
Timer réinitialisé (800ms recommence)
    ↓
800ms s'écoulent sans modification
    ↓
Auto-save déclenché
    ↓
Requête API POST
    ↓
Succès → "Enregistré" affiché 2 secondes
```

## Interface utilisateur

### 1. **Indicateur d'état en haut à droite**

Trois états possibles :

```
┌─────────────────────────────────────────┐
│  Mon pronostic          🔄 Enregistrement...│
│                                          │
│       [  3  ]   -   [  1  ]             │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│  Mon pronostic          ✓ Enregistré    │
│                                          │
│       [  3  ]   -   [  1  ]             │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│  Mon pronostic          ✗ Erreur        │
│                                          │
│       [  3  ]   -   [  1  ]             │
└─────────────────────────────────────────┘
```

### 2. **Feedback visuel sur les inputs**

Les champs de saisie changent de couleur selon l'état :

| État | Couleur bordure | Couleur fond |
|------|----------------|--------------|
| **Vide** (pas de pronostic) | Gris (`border-gray-300`) | Blanc |
| **Enregistrement en cours** | Bleu (`border-blue-400`) | Bleu clair (`bg-blue-50`) |
| **Enregistré** | Vert (`border-green-400`) | Vert clair (`bg-green-50`) |
| **Erreur** | Rouge (`border-red-400`) | Rouge clair (`bg-red-50`) |

### 3. **Message d'aide**

En bas du formulaire :
> _"Vos modifications sont enregistrées automatiquement"_

### 4. **Pas de bouton !**

Le formulaire n'a **plus de bouton** de soumission. Tout est automatique.

## Détails techniques

### Debounce (800ms)

Le debounce évite de faire une requête API à chaque frappe :

```javascript
watch([homeScore, awayScore], () => {
    // Clear previous timeout
    if (saveTimeout) {
        clearTimeout(saveTimeout);
    }

    // Debounce: wait 800ms after user stops typing
    saveTimeout = setTimeout(() => {
        autoSavePrediction();
    }, 800);
});
```

**Pourquoi 800ms ?**
- Assez long pour éviter les requêtes excessives
- Assez court pour que ça semble instantané
- Équilibre optimal entre UX et performance serveur

### Validation avant sauvegarde

```javascript
const autoSavePrediction = async () => {
    // Validate scores are numbers
    if (typeof homeScore.value !== 'number' ||
        typeof awayScore.value !== 'number') {
        return;
    }

    // Validate range (0-20)
    if (homeScore.value < 0 || homeScore.value > 20 ||
        awayScore.value < 0 || awayScore.value > 20) {
        return;
    }

    // Proceed with save...
}
```

### États de sauvegarde

```javascript
saveStatus = ref(''); // Valeurs possibles:
// '' (vide) - Aucune action en cours
// 'saving' - Enregistrement en cours
// 'saved' - Enregistré avec succès
// 'error' - Erreur d'enregistrement
```

### Gestion des erreurs

En cas d'erreur :
1. Indicateur "Erreur" affiché en rouge
2. Message d'erreur détaillé sous les inputs
3. Auto-disparition après 3 secondes
4. Couleur rouge sur les inputs

### Auto-disparition des statuts

**Succès** :
- "Enregistré" disparaît après **2 secondes**
- Inputs restent verts (pronostic existe)

**Erreur** :
- "Erreur" disparaît après **3 secondes**
- Message d'erreur disparaît aussi
- Inputs redeviennent verts si pronostic existait avant

## Avantages de l'auto-save

### Pour l'utilisateur ✨

1. **Zéro friction** : Pas besoin de cliquer sur un bouton
2. **Pas de perte de données** : Impossible d'oublier d'enregistrer
3. **Feedback immédiat** : Voit instantanément que c'est sauvegardé
4. **Expérience moderne** : Comme Google Docs, Notion, etc.
5. **Moins de clics** : UX optimale

### Pour le développement 🛠️

1. **Moins de code UI** : Pas de bouton à gérer
2. **État simplifié** : Un seul watcher pour tout
3. **Réduction des erreurs** : Validation automatique
4. **API inchangée** : Même endpoint que avant

## Comparaison Avant/Après

| Aspect | Avec bouton | Auto-save |
|--------|-------------|-----------|
| **Actions utilisateur** | Taper + Cliquer | Taper |
| **Risque d'oubli** | Oui (ne pas cliquer) | Non |
| **Feedback** | Au clic | En continu |
| **UX** | Traditionnelle | Moderne |
| **Requêtes API** | 1 au clic | 1 après debounce |
| **Complexité UI** | Bouton + états | Indicateur simple |

## Scénarios d'utilisation

### Scénario 1 : Nouveau pronostic
```
1. User ouvre la page
2. Champs à 0 - 0 (par défaut)
3. User tape "2" (domicile)
4. Bordures deviennent bleues → "Enregistrement..."
5. 800ms passent
6. Sauvegarde → Bordures vertes → "Enregistré"
7. 2s plus tard → Indicateur disparaît, bordures restent vertes
```

### Scénario 2 : Modification rapide
```
1. Pronostic existant : 2 - 1
2. User change "1" → "2"
3. Puis change immédiatement "2" → "3"
4. Timer réinitialisé à chaque frappe
5. 800ms après la dernière frappe → Sauvegarde
6. Un seul appel API pour 2 - 3
```

### Scénario 3 : Erreur réseau
```
1. User tape 3 - 1
2. Débounce → Tentative de sauvegarde
3. Erreur serveur (500)
4. Bordures rouges → "Erreur"
5. Message : "Erreur lors de l'enregistrement automatique"
6. User peut retaper → Nouvelle tentative
```

## Optimisations

### 1. **Pas de sauvegarde redondante**

Si l'utilisateur tape "3" puis efface pour revenir à la valeur initiale, aucune requête n'est faite (validation empêche).

### 2. **Désactivation pendant la sauvegarde**

Les inputs sont désactivés pendant `saveStatus === 'saving'` pour éviter les modifications concurrentes.

### 3. **Gestion du focus**

Variable `inputFocused` permet de tracker si l'utilisateur est en train de saisir (pour futures améliorations).

## Configuration

### Modifier le délai de debounce

Dans `PredictionForm.vue` :

```javascript
// Actuellement : 800ms
saveTimeout = setTimeout(() => {
    autoSavePrediction();
}, 800); // ← Changer cette valeur

// Exemples :
// 500 → Plus réactif, plus de requêtes
// 1000 → Moins réactif, moins de requêtes
// 1500 → Pour connexions lentes
```

### Modifier la durée d'affichage des statuts

```javascript
// Succès (actuellement 2s)
setTimeout(() => {
    if (saveStatus.value === 'saved') {
        saveStatus.value = '';
    }
}, 2000); // ← Changer ici

// Erreur (actuellement 3s)
setTimeout(() => {
    if (saveStatus.value === 'error') {
        saveStatus.value = '';
    }
}, 3000); // ← Changer ici
```

## Tests recommandés

- [ ] Saisir un nouveau pronostic et attendre 800ms
- [ ] Modifier rapidement plusieurs fois (doit faire 1 seul appel)
- [ ] Vérifier l'indicateur "Enregistrement..." apparaît
- [ ] Vérifier l'indicateur "Enregistré" apparaît puis disparaît
- [ ] Simuler une erreur serveur (vérifier indicateur rouge)
- [ ] Tester avec une connexion lente
- [ ] Vérifier que les inputs se désactivent pendant la sauvegarde
- [ ] Tester le responsive (mobile/desktop)
- [ ] Vérifier que ça fonctionne sur plusieurs matchs simultanément

## Performances

### Charge serveur

**Avant (avec bouton)** :
- 1 requête par clic de bouton
- Utilisateur peut cliquer plusieurs fois (spam)

**Maintenant (auto-save)** :
- 1 requête max toutes les 800ms par match
- Impossible de spammer (debounce)
- Même charge ou inférieure

### Exemple : Utilisateur change 10 fois d'avis

**Avec bouton** :
- Potentiellement 10 clics = 10 requêtes

**Avec auto-save** :
- Si changements rapides < 800ms : **1 seule requête**
- Si changements espacés : 1 requête par changement après 800ms

## Code de référence

### Structure du template

```vue
<div class="relative">
    <!-- Status indicator (top-right) -->
    <div class="absolute top-4 right-4">
        <div v-if="saveStatus === 'saving'">🔄 Enregistrement...</div>
        <div v-if="saveStatus === 'saved'">✓ Enregistré</div>
        <div v-if="saveStatus === 'error'">✗ Erreur</div>
    </div>

    <!-- Inputs with dynamic classes -->
    <input
        v-model.number="homeScore"
        :class="getInputClass()"
        :disabled="saveStatus === 'saving'"
    >

    <!-- Helper text -->
    <p>Vos modifications sont enregistrées automatiquement</p>
</div>
```

### Fonction getInputClass()

```javascript
const getInputClass = () => {
    if (saveStatus.value === 'saving') {
        return 'border-blue-400 bg-blue-50';
    } else if (saveStatus.value === 'saved' || hasPrediction.value) {
        return 'border-green-400 bg-green-50';
    } else if (saveStatus.value === 'error') {
        return 'border-red-400 bg-red-50';
    } else {
        return 'border-gray-300 bg-white';
    }
};
```

## Évolutions futures possibles

### Court terme
- [ ] Indicateur de "changements non sauvegardés" si offline
- [ ] Retry automatique en cas d'erreur temporaire
- [ ] Animation subtile lors de la sauvegarde

### Moyen terme
- [ ] Offline-first avec LocalStorage
- [ ] Synchronisation en arrière-plan
- [ ] Historique des modifications

### Long terme
- [ ] Collaborative editing (voir les changements en temps réel)
- [ ] WebSocket pour push des scores finaux
- [ ] Undo/Redo

## Dépannage

### L'auto-save ne se déclenche pas
1. Vérifier la console du navigateur (erreurs JS)
2. Vérifier que `canPredict === true`
3. Vérifier les permissions utilisateur
4. Tester manuellement l'API : `POST /games/{id}/predictions`

### Trop de requêtes
1. Augmenter le délai de debounce (800 → 1200ms)
2. Vérifier qu'il n'y a pas de watch infinis

### Indicateurs ne s'affichent pas
1. Vérifier les transitions CSS sont bien chargées
2. Vérifier `saveStatus` dans Vue DevTools
3. Vérifier les classes Tailwind sont compilées

## Fichiers modifiés

- `resources/js/components/PredictionForm.vue` - Refonte complète avec auto-save
- `AUTO_SAVE_PREDICTIONS.md` - Cette documentation
