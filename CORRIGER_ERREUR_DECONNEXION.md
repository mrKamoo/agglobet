# Corriger l'erreur de déconnexion

## 🐛 Problème

Quand vous vous déconnectez (ou quand un visiteur non connecté accède au site), vous obtenez une erreur :

```
Attempt to read property "name" on null
```

**Cause :** Le fichier `resources/views/layouts/navigation.blade.php` essaie d'afficher `{{ Auth::user()->name }}` même quand l'utilisateur n'est pas connecté.

---

## ✅ Solution

Le menu utilisateur doit être protégé par `@auth` pour ne s'afficher que si l'utilisateur est connecté. Sinon, il faut afficher des boutons "Connexion" et "Inscription".

---

## 🔧 Installation de la correction

### Option 1 : Remplacement complet (Recommandé)

```bash
# Sauvegarder l'ancien fichier
mv resources/views/layouts/navigation.blade.php resources/views/layouts/navigation.blade.php.old

# Utiliser le fichier corrigé
mv resources/views/layouts/navigation_fixed.blade.php resources/views/layouts/navigation.blade.php
```

---

### Option 2 : Modification manuelle

Si vous préférez modifier manuellement, voici les changements à faire :

#### 1. Menu desktop (ligne 40-71)

**Entourer le dropdown avec `@auth` :**

```blade
<!-- Settings Dropdown -->
<div class="hidden sm:flex sm:items-center sm:ms-6">
    @auth
        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                    <div>{{ Auth::user()->name }}</div>

                    <div class="ms-1">
                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </button>
            </x-slot>

            <x-slot name="content">
                <x-dropdown-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-dropdown-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-dropdown-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-dropdown-link>
                </form>
            </x-slot>
        </x-dropdown>
    @else
        <div class="space-x-2">
            <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                Connexion
            </a>
            <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                Inscription
            </a>
        </div>
    @endauth
</div>
```

---

#### 2. Menu responsive mobile (ligne 112-134)

**Entourer la section "Responsive Settings Options" avec `@auth` :**

```blade
<!-- Responsive Settings Options -->
@auth
    <div class="pt-4 pb-1 border-t border-gray-200">
        <div class="px-4">
            <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
            <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
        </div>

        <div class="mt-3 space-y-1">
            <x-responsive-nav-link :href="route('profile.edit')">
                {{ __('Profile') }}
            </x-responsive-nav-link>

            <!-- Authentication -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault();
                                    this.closest('form').submit();">
                    {{ __('Log Out') }}
                </x-responsive-nav-link>
            </form>
        </div>
    </div>
@else
    <div class="pt-4 pb-1 border-t border-gray-200">
        <div class="px-4 space-y-2">
            <a href="{{ route('login') }}" class="block w-full px-4 py-2 text-center bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                Connexion
            </a>
            <a href="{{ route('register') }}" class="block w-full px-4 py-2 text-center bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                Inscription
            </a>
        </div>
    </div>
@endauth
```

---

## 📊 Modifications apportées

### Ce qui change :

1. **Menu desktop** (ligne 40) :
   - ✅ Ajout de `@auth` autour du dropdown
   - ✅ Ajout de `@else` avec boutons Connexion/Inscription
   - ✅ Fermé par `@endauth`

2. **Menu responsive mobile** (ligne 112) :
   - ✅ Ajout de `@auth` autour de la section paramètres
   - ✅ Ajout de `@else` avec boutons Connexion/Inscription
   - ✅ Fermé par `@endauth`

---

## 🎯 Résultat

### Pour les utilisateurs connectés :
- Menu avec leur nom et dropdown (Profil, Déconnexion)
- Fonctionne comme avant ✅

### Pour les visiteurs non connectés :
- Boutons "Connexion" et "Inscription"
- Plus d'erreur ! ✅

---

## 🖼️ Aperçu visuel

### Menu desktop

**Utilisateur connecté :**
```
[Logo] Accueil Matchs Mes Pronostics Classement | [Jean Dupont ▼]
                                                   └─> Profil
                                                   └─> Déconnexion
```

**Visiteur non connecté :**
```
[Logo] Accueil Classement | [Connexion] [Inscription]
```

---

### Menu mobile

**Utilisateur connecté :**
```
☰ Menu
  Accueil
  Matchs
  Mes Pronostics
  Classement
  ──────────────
  Jean Dupont
  jean@example.com

  > Profil
  > Déconnexion
```

**Visiteur non connecté :**
```
☰ Menu
  Accueil
  Classement
  ──────────────
  [Connexion]
  [Inscription]
```

---

## ✅ Test

Après avoir appliqué la correction :

1. **Déconnectez-vous** → Plus d'erreur ✅
2. Vous verrez les boutons **"Connexion"** et **"Inscription"** ✅
3. **Connectez-vous** → Le menu avec votre nom réapparaît ✅

---

## 🔍 Explication technique

### Le problème

```blade
<div>{{ Auth::user()->name }}</div>
```

Quand `Auth::user()` retourne `null` (utilisateur non connecté), PHP essaie d'accéder à `->name` sur `null`, ce qui provoque l'erreur :

```
Attempt to read property "name" on null
```

### La solution

```blade
@auth
    <div>{{ Auth::user()->name }}</div>
@else
    <!-- Boutons pour visiteurs -->
@endauth
```

La directive `@auth` vérifie si l'utilisateur est connecté **avant** d'essayer d'accéder à `Auth::user()->name`.

---

## 🚨 Important

Cette correction est **essentielle** car :
- ❌ Sans elle, les visiteurs non connectés voient une erreur
- ❌ Ils ne peuvent pas accéder à votre site
- ✅ Avec la correction, tout le monde peut accéder au site
- ✅ Les visiteurs voient les boutons pour se connecter/s'inscrire

---

Bon pronostic sans erreur ! ⚽🎯
