# Silo - Gestionnaire d'images disque

Silo est une application web sécurisée et performante conçue pour téléverser, gérer et partager des images disque (ISO, IMG, QCOW2, VMDK, VHDX, WIM…) et d'autres archives volumineuses. Elle offre une interface utilisateur moderne et réactive construite avec le framework TALL (Tailwind CSS, Alpine.js, Laravel, et Livewire).

## Fonctionnalités Principales

-   **Gestion des Fichiers :** Téléversez, listez, modifiez et supprimez facilement vos fichiers ISO.
-   **Authentification Complète :** Système d'inscription et de connexion sécurisé pour les utilisateurs.
-   **Partage de Fichiers :**
    -   Générez des liens de partage uniques pour chaque fichier.
    -   Protégez les liens avec un **mot de passe**.
    -   Définissez une **date d'expiration**.
    -   Limitez le **nombre maximal de téléchargements**.
-   **Panneau d'Administration Robuste :**
    -   Tableau de bord avec statistiques d'utilisation.
    -   Gestion des utilisateurs, des groupes et des invitations.
    -   Configuration des emplacements de stockage (multi-disques supporté : local, S3, etc.).
    -   Audit des logs d'activité.
-   **Gestion des Rôles et Permissions :** Contrôle d'accès fin grâce aux rôles (e.g., administrateur, utilisateur).
-   **Interface Réactive :** Construite avec Livewire & Volt pour une expérience utilisateur dynamique sans rechargement de page.
-   **Support Multi-langues :** Interface disponible en Français et en Anglais.

## Stack Technique

-   **Backend :** Laravel 13
-   **Frontend :** Livewire 4 & Volt, Tailwind CSS, Alpine.js
-   **Base de données :** Compatible avec MySQL, PostgreSQL, SQLite
-   **Permissions :** Spatie/laravel-permission
-   **Logs d'activité :** Spatie/laravel-activitylog

## Installation

Suivez ces étapes pour installer et lancer le projet en local.

### Prérequis

-   **Docker** & **Docker Compose**
-   (Optionnel pour le dev local) PHP 8.5+, Composer, Node.js

## Déploiement avec Docker (Recommandé)

Le projet utilise **FrankenPHP** pour des performances optimales.

### 1. Configuration de l'Environnement
Copiez le fichier de configuration production et personnalisez-le (notamment les mots de passe) :
```bash
cp .env.example .env
```

### 2. Lancer l'environnement
```bash
docker-compose up -d --build
```
L'application sera accessible sur `http://localhost:8080`.

### 3. Initialiser la base de données
```bash
docker-compose exec app php artisan migrate --seed
```

### 3. Build & CI/CD (GitHub Actions)
Le projet est configuré pour se construire automatiquement via **GitHub Actions** et se publier sur **GHCR** (`ghcr.io/pokertour/silo`).

Pour déclencher un build et une publication :
1. Créez un tag git : `git tag v1.0.0`
2. Poussez le tag : `git push origin v1.0.0`

Le workflow s'occupera de builder l'image, de la taguer et de la pousser sur le registry.

## Compte Administrateur Initial

Le seeder (`--seed`) crée un compte administrateur **sans mot de passe par défaut** :

- **Email :** valeur de `ADMIN_EMAIL` (par défaut `admin@silo.local`)
- **Mot de passe :** valeur de `ADMIN_PASSWORD` si elle est définie, sinon un mot de passe aléatoire est généré et **affiché une seule fois** dans la sortie de la commande.

Changez ce mot de passe et activez la double authentification (2FA) dès la première connexion.

## Sécurité

- **Stockage des ISOs :** utilisez toujours un emplacement de stockage **privé** (`storage/app/private` ou un disque distant). Ne placez jamais les ISOs sous `storage/app/public` : ce dossier est exposé publiquement via le lien `/storage`, ce qui contournerait les droits d'accès et les liens de partage.
  > Les installations créées avec une ancienne version utilisent l'emplacement `local_public` (`storage/app/public`). Le Caddyfile fourni bloque désormais `/storage/iso_files/*`, mais il est recommandé de créer un emplacement privé, de le définir par défaut et d'y déplacer les fichiers existants.
- **Reverse proxy :** si l'application est derrière un reverse proxy, renseignez `TRUSTED_PROXIES` avec son adresse. Par défaut seuls les réseaux privés sont considérés comme des proxies de confiance.
- **HTTPS :** servez l'application en HTTPS et laissez `SESSION_SECURE_COOKIE=true`.
- **Base de données :** le `docker-compose.yml` n'expose pas MariaDB sur l'hôte et exige des mots de passe (`DB_PASSWORD`, `DB_ROOT_PASSWORD`) définis dans `.env`.

## Installation Manuelle (Développement)

Si vous ne souhaitez pas utiliser Docker :

### 1. Installer les Dépendances
```bash
composer install
npm install
```

### 2. Configuration & Base de Données
```bash
cp .env.example .env
php artisan key:generate
# Configurez votre DB dans le .env, puis :
php artisan migrate --seed
```

### 3. Compiler & Lancer
```bash
npm run build
php artisan serve
```

## Stack Technique

- **Serveur :** FrankenPHP (PHP 8.5)
- **Framework :** Laravel 13, Livewire 4, Volt
- **Assets :** Tailwind CSS 4, Vite
- **Base de données :** MariaDB (Docker) / SQLite (Local)
