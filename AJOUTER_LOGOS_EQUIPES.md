# Ajouter les logos des équipes aux pages de pronostics

## Modifications à effectuer

J'ai créé deux versions améliorées des fichiers avec les logos des équipes bien positionnés :

### 1. Page de pronostics (games/index)

**Fichier à remplacer** : `resources/views/games/index.blade.php`
**Fichier de référence** : `resources/views/games/index_avec_logos.blade.php`

**Renommez simplement les fichiers :**

```bash
# Sauvegarde de l'ancien fichier
mv resources/views/games/index.blade.php resources/views/games/index.blade.php.old

# Renommer le nouveau fichier
mv resources/views/games/index_avec_logos.blade.php resources/views/games/index.blade.php
```

**Ou copiez manuellement** le contenu de `index_avec_logos.blade.php` dans `index.blade.php`

---

### 2. Page d'accueil (home)

**Fichier à remplacer** : `resources/views/home.blade.php`
**Fichier de référence** : `resources/views/home_avec_logos.blade.php`

**Renommez simplement les fichiers :**

```bash
# Sauvegarde de l'ancien fichier
mv resources/views/home.blade.php resources/views/home.blade.php.old

# Renommer le nouveau fichier
mv resources/views/home_avec_logos.blade.php resources/views/home.blade.php
```

**Ou copiez manuellement** le contenu de `home_avec_logos.blade.php` dans `home.blade.php`

---

## Modifications apportées

### Page de pronostics (`games/index.blade.php`)

**Avant :**
- Seulement le nom de l'équipe et le nom court
- Pas de logo

**Après :**
- Logo de l'équipe affiché au-dessus du nom (64x64px)
- Nom de l'équipe
- Nom court en gris
- Logos centrés et responsive

**Lignes modifiées (31-37 et 54-60) :**

```blade
<!-- Équipe domicile -->
<div class="flex-1 text-center">
    @if($game->homeTeam->logo)
        <img src="{{ $game->homeTeam->logo }}" alt="{{ $game->homeTeam->name }}" class="mx-auto h-16 w-16 object-contain mb-2">
    @endif
    <p class="text-lg font-bold">{{ $game->homeTeam->name }}</p>
    <p class="text-sm text-gray-500">{{ $game->homeTeam->short_name }}</p>
</div>

<!-- Équipe extérieure -->
<div class="flex-1 text-center">
    @if($game->awayTeam->logo)
        <img src="{{ $game->awayTeam->logo }}" alt="{{ $game->awayTeam->name }}" class="mx-auto h-16 w-16 object-contain mb-2">
    @endif
    <p class="text-lg font-bold">{{ $game->awayTeam->name }}</p>
    <p class="text-sm text-gray-500">{{ $game->awayTeam->short_name }}</p>
</div>
```

---

### Page d'accueil (`home.blade.php`)

**Avant :**
- Logos placés sur la même ligne que le nom (après)
- Taille 8x8px (trop petit)

**Après :**
- Logos affichés **au-dessus** du nom de l'équipe
- Taille 12x12px (48px, plus visible)
- Meilleure disposition visuelle
- Date et heure mieux formatées

**Lignes modifiées (40-52) :**

```blade
<div class="flex items-center justify-between">
    <!-- Équipe domicile -->
    <div class="flex-1 text-center">
        @if($game->homeTeam->logo)
            <img src="{{ $game->homeTeam->logo }}" alt="{{ $game->homeTeam->name }}" class="mx-auto h-12 w-12 object-contain mb-2">
        @endif
        <p class="font-semibold text-sm">{{ $game->homeTeam->name }}</p>
    </div>

    <!-- VS et date -->
    <div class="px-6 text-center">
        <p class="text-gray-500 text-xl font-bold">VS</p>
        <p class="text-xs text-gray-400 mt-1">{{ $game->match_date->format('d/m/Y') }}</p>
        <p class="text-xs text-gray-400">{{ $game->match_date->format('H:i') }}</p>
    </div>

    <!-- Équipe extérieure -->
    <div class="flex-1 text-center">
        @if($game->awayTeam->logo)
            <img src="{{ $game->awayTeam->logo }}" alt="{{ $game->awayTeam->name }}" class="mx-auto h-12 w-12 object-contain mb-2">
        @endif
        <p class="font-semibold text-sm">{{ $game->awayTeam->name }}</p>
    </div>
</div>
```

---

## Classes CSS utilisées

- `h-12 w-12` : Taille des logos sur la page d'accueil (48px)
- `h-16 w-16` : Taille des logos sur la page de pronostics (64px)
- `object-contain` : Conserve les proportions du logo
- `mx-auto` : Centre le logo horizontalement
- `mb-2` : Marge en bas pour espacer du nom

---

## Note importante

Les logos seront automatiquement récupérés depuis l'API **Football-Data.org** lors de la synchronisation des équipes. Si certaines équipes n'ont pas encore de logo :

1. Synchronisez les équipes depuis `/admin/sync`
2. Cliquez sur "Synchroniser les Équipes"
3. Les logos seront automatiquement téléchargés et stockés

Si une équipe n'a pas de logo, le nom s'affiche quand même sans logo (grâce à `@if($game->homeTeam->logo)`).

---

## Aperçu visuel

### Page d'accueil
```
┌────────────────────────────────────────────┐
│  [LOGO PSG]        VS      [LOGO OM]       │
│   PSG              │        OM              │
│                 15/11/24                    │
│                  21:00                      │
│         → Faire un pronostic               │
└────────────────────────────────────────────┘
```

### Page de pronostics
```
┌────────────────────────────────────────────┐
│                                            │
│  [LOGO PLUS GRAND]   3 - 1  [LOGO PLUS GRAND] │
│  Paris Saint-Germain        Olympique Marseille │
│        PSG                      OM          │
│                                            │
│  [Formulaire de pronostic]                │
└────────────────────────────────────────────┘
```

---

Profitez de vos pronostics avec les logos des équipes ! ⚽
