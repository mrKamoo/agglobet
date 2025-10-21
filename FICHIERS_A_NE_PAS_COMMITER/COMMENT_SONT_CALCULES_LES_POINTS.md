# Comment sont calculés les points dans Agglobet ?

## 🎯 Système de points

Le système de calcul des points est basé sur la précision de votre pronostic par rapport au score réel du match.

---

## 📊 Règles de calcul (par défaut)

Lorsqu'un administrateur entre le résultat final d'un match, les points sont **automatiquement calculés** pour tous les pronostics selon ces règles :

### 1. Score exact : **5 points** 🏆

Vous avez trouvé **exactement** le bon score.

**Exemples :**
- Match réel : PSG 3-1 OM
- Votre pronostic : PSG 3-1 OM
- ✅ **Vous gagnez 5 points**

### 2. Bonne différence de buts : **3 points** 🥈

Vous avez trouvé la **bonne différence de buts** mais pas le score exact.

**Exemples :**
- Match réel : PSG 3-1 OM (+2 pour PSG)
- Votre pronostic : PSG 2-0 OM (+2 pour PSG)
- ✅ **Vous gagnez 3 points**

Autres exemples valides :
- Réel : 4-2, Prono : 3-1 (même différence : +2)
- Réel : 1-1, Prono : 0-0 (même différence : 0)

### 3. Bon résultat (vainqueur ou match nul) : **1 point** 🥉

Vous avez trouvé le bon résultat (victoire domicile, victoire extérieure, ou match nul) mais ni le score exact ni la bonne différence.

**Exemples :**

**Victoire domicile :**
- Réel : PSG 3-0 OM (PSG gagne)
- Prono : PSG 1-0 OM (PSG gagne)
- ✅ **Vous gagnez 1 point**

**Match nul :**
- Réel : 2-2 (match nul)
- Prono : 1-1 (match nul)
- ✅ **Vous gagnez 1 point**

**Victoire extérieure :**
- Réel : Nice 0-2 Lyon (Lyon gagne)
- Prono : Nice 1-3 Lyon (Lyon gagne)
- ✅ **Vous gagnez 1 point**

### 4. Mauvais pronostic : **0 point** ❌

Vous n'avez pas trouvé le bon résultat.

**Exemples :**
- Réel : PSG 3-1 OM (PSG gagne)
- Prono : PSG 1-2 OM (OM gagne)
- ❌ **Vous gagnez 0 point**

---

## 🔄 Ordre de priorité

Le système évalue dans cet ordre :

1. **D'abord** : Est-ce le score exact ? → **5 points**
2. **Sinon** : Est-ce la bonne différence ? → **3 points**
3. **Sinon** : Est-ce le bon vainqueur/nul ? → **1 point**
4. **Sinon** : → **0 point**

**Vous ne pouvez gagner qu'un seul type de points par match.**

---

## 📋 Exemples détaillés

### Match 1 : PSG 4-1 OM

| Pronostic | Points | Raison |
|-----------|--------|--------|
| PSG 4-1 OM | **5** | Score exact ✅ |
| PSG 3-0 OM | **3** | Bonne différence (+3) |
| PSG 5-2 OM | **3** | Bonne différence (+3) |
| PSG 2-0 OM | **1** | PSG gagne (mais mauvaise diff) |
| PSG 1-0 OM | **1** | PSG gagne (mais mauvaise diff) |
| OM 2-1 PSG | **0** | Mauvais vainqueur ❌ |
| PSG 2-2 OM | **0** | Match nul alors que PSG gagne ❌ |

### Match 2 : Lyon 1-1 Marseille

| Pronostic | Points | Raison |
|-----------|--------|--------|
| Lyon 1-1 OM | **5** | Score exact ✅ |
| Lyon 0-0 OM | **3** | Match nul avec même diff (0) |
| Lyon 2-2 OM | **3** | Match nul avec même diff (0) |
| Lyon 3-3 OM | **3** | Match nul avec même diff (0) |
| Lyon 2-1 OM | **0** | Lyon gagne alors que match nul ❌ |
| OM 2-0 Lyon | **0** | OM gagne alors que match nul ❌ |

### Match 3 : Nice 0-3 Lille

| Pronostic | Points | Raison |
|-----------|--------|--------|
| Nice 0-3 Lille | **5** | Score exact ✅ |
| Nice 1-4 Lille | **3** | Bonne différence (-3) |
| Nice 0-1 Lille | **1** | Lille gagne (mais mauvaise diff) |
| Nice 1-0 Lille | **0** | Mauvais vainqueur ❌ |
| Nice 2-2 Lille | **0** | Match nul alors que Lille gagne ❌ |

---

## ⚙️ Configuration des règles

Les règles de points sont **configurables** par l'administrateur dans la table `points_rules` :

**Règle par défaut :**
- Score exact : **5 points**
- Bonne différence : **3 points**
- Bon vainqueur : **1 point**

**L'administrateur peut modifier** ces valeurs pour créer des variantes :
- Exemple : 10 points pour un score exact
- Exemple : 0 point pour le bon vainqueur (plus difficile)

---

## 🔢 Calcul automatique

### Quand sont calculés les points ?

Les points sont calculés **automatiquement** quand :

1. Un administrateur va sur `/admin/results`
2. Il sélectionne un match terminé
3. Il entre les scores finaux (domicile et extérieur)
4. Il clique sur "Enregistrer le résultat"

**Le système calcule alors instantanément les points pour tous les utilisateurs qui ont pronostiqué sur ce match.**

### Code de calcul

Le calcul se trouve dans :
- **Fichier** : `app/Http/Controllers/Admin/ResultController.php`
- **Méthode** : `calculatePoints()` (lignes 44-75)

**Algorithme :**

```php
// 1. Score exact ?
if (pronostic_domicile == réel_domicile ET pronostic_ext == réel_ext)
    → 5 points

// 2. Sinon, bonne différence ?
else if ((pronostic_domicile - pronostic_ext) == (réel_domicile - réel_ext))
    → 3 points

// 3. Sinon, bon vainqueur ?
else if (bon_vainqueur(pronostic) == bon_vainqueur(réel))
    → 1 point

// 4. Sinon
    → 0 point
```

---

## 📈 Classement

Le classement général additionne **tous les points gagnés** sur tous les matchs pronostiqués.

**Exemple :**
- Match 1 : 5 points (score exact)
- Match 2 : 3 points (bonne diff)
- Match 3 : 1 point (bon vainqueur)
- Match 4 : 0 point (raté)
- **Total : 9 points**

Le classement est visible sur la page `/leaderboard` (accessible à tous).

---

## 🎮 Stratégies de pronostic

### Stratégie conservatrice
Pronostiquer des scores courants (1-0, 2-1, etc.) pour maximiser les chances d'avoir au moins le bon vainqueur.

### Stratégie offensive
Tenter des scores plus risqués pour viser le score exact et les 5 points.

### Stratégie différence
Se concentrer sur la différence de buts plutôt que le score exact (ex: toujours parier +1 pour le favori).

---

## 🔒 Règles importantes

1. **Pronostic bloqué** : Vous ne pouvez plus modifier votre pronostic une fois que le match a commencé (heure du `match_date` dépassée)

2. **Un seul pronostic par match** : Vous pouvez modifier votre pronostic autant de fois que vous voulez **avant** le début du match

3. **Points attribués une seule fois** : Quand l'admin entre le résultat, les points sont calculés une fois et ne changent plus

4. **Scores limités** : Les scores doivent être entre 0 et 20 (même si c'est rare d'avoir 20 buts !)

---

## 📱 Où voir mes points ?

- **Classement général** : `/leaderboard`
- **Mes pronostics** : `/my-predictions` (voir vos pronostics et les points gagnés)
- **Page d'accueil** : Voir les matchs à venir

---

Bon pronostic ! ⚽🎯
