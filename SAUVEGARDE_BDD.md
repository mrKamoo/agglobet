# Sauvegarde de la Base de Données

Cette fonctionnalité permet de créer des sauvegardes complètes de la base de données MySQL depuis le tableau de bord administrateur.

## Utilisation

### Via le Dashboard Admin

1. Connectez-vous en tant qu'administrateur
2. Accédez au tableau de bord admin (`/admin/dashboard`)
3. Dans la section "Actions rapides", cliquez sur le bouton **"Sauvegarder la BDD"**
4. La sauvegarde sera créée et téléchargée automatiquement

### Via la ligne de commande

```bash
php artisan db:backup
```

Cette commande crée un fichier de sauvegarde dans `storage/app/backups/` avec un nom au format :
```
backup-[nom_bdd]-YYYY-MM-DD-HHmmss.sql
```

## Gestion des sauvegardes

### Liste des sauvegardes

Le tableau de bord admin affiche automatiquement la liste de toutes les sauvegardes disponibles avec :
- Le nom du fichier
- La taille du fichier
- La date de création
- Un lien de téléchargement

### Emplacement des sauvegardes

Les fichiers de sauvegarde sont stockés dans :
```
storage/app/backups/
```

**Important** : Ce répertoire est automatiquement ignoré par Git (via `.gitignore`) pour éviter de versionner les données sensibles.

## Restauration d'une sauvegarde

Pour restaurer une sauvegarde :

```bash
mysql -u [utilisateur] -p [nom_base] < storage/app/backups/backup-[nom].sql
```

Exemple :
```bash
mysql -u root -p agglobet_v2 < storage/app/backups/backup-agglobet_v2-2025-10-26-102955.sql
```

## Prérequis

- **mysqldump** : La commande `mysqldump` doit être disponible sur le serveur
- **Permissions** : L'application doit avoir les permissions d'écriture sur `storage/app/backups/`
- **Configuration** : Les informations de connexion MySQL doivent être correctement configurées dans `.env`

## Automatisation (optionnel)

Pour automatiser les sauvegardes quotidiennes, ajoutez cette ligne dans le scheduler Laravel (`app/Console/Kernel.php`) :

```php
protected function schedule(Schedule $schedule)
{
    // Sauvegarde quotidienne à 2h du matin
    $schedule->command('db:backup')->daily()->at('02:00');
}
```

Puis configurez le cron sur le serveur :
```bash
* * * * * cd /chemin/vers/agglobet && php artisan schedule:run >> /dev/null 2>&1
```

## Sécurité

- Les sauvegardes contiennent toutes les données de l'application, y compris les informations sensibles
- Assurez-vous que le répertoire `storage/app/backups/` n'est pas accessible publiquement
- Conservez les sauvegardes dans un endroit sécurisé
- Supprimez régulièrement les anciennes sauvegardes pour économiser l'espace disque

## Dépannage

### Erreur "mysqldump: command not found"

Assurez-vous que MySQL est installé et que `mysqldump` est dans le PATH système.

Sur Windows avec XAMPP :
```bash
C:\xampp\mysql\bin\mysqldump.exe
```

### Erreur de permissions

Vérifiez que le répertoire `storage/app/backups/` existe et a les bonnes permissions :
```bash
mkdir -p storage/app/backups
chmod 755 storage/app/backups
```

### Fichier de sauvegarde vide ou erreur

Vérifiez les informations de connexion MySQL dans `.env` :
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
