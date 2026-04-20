# Intranet CRAN 🚀

Ce projet est une application intranet moderne développée avec **Laravel**, **Inertia.js** et **Vue.js 3**.

## 📋 Prérequis

Avant de commencer, assurez-vous d'avoir installé les éléments suivants sur votre machine :

- **PHP 8.2+**
- **Composer**
- **Node.js** & **NPM**
- **SQLite** (ou un autre driver de base de données)
- **Meilisearch** (pour la recherche globale)

## 🛠️ Installation

### 1. Cloner le dépôt
```bash
git clone git@github.com:Olivier5475/intranet-cran.git
cd intranet_laravel
```

### 2. Installation des dépendances
```bash
# Dépendances PHP
composer install

# Dépendances JavaScript
npm install
```

### 3. Configuration de l'environnement
Copiez le fichier d'exemple et générez la clé d'application :
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Base de données
Le projet est configuré par défaut pour utiliser **SQLite**. Créez le fichier de base de données vide :
```bash
touch database/database.sqlite
```
Ensuite, lancez les migrations pour créer les tables :
```bash
php artisan migrate
```

### 5. Meilisearch (Recherche)
Le projet utilise Laravel Scout avec Meilisearch. Assurez-vous que Meilisearch tourne localement :
```bash
# Exemple via Docker
docker run -it --rm -p 7700:7700 getmeili/meilisearch:latest
```

---

## ⚙️ Configuration du .env

Voici les points clés de votre configuration actuelle :

### Base de données (SQLite)
Par défaut, l'application utilise SQLite pour plus de simplicité :
```env
DB_CONNECTION=sqlite
```

### Authentification CAS
Le projet est configuré pour l'authentification centralisée (CAS) :
- `CAS_HOST` & `CAS_URL` : Serveur d'authentification de l'institution.
- `CAS_PORT` : Généralement 443 pour le HTTPS.

### Recherche (Scout & Meilisearch)
Pour le bon fonctionnement de la recherche de fichiers et documents :
```env
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=[http://127.0.0.1:7700](http://127.0.0.1:7700)
```

### Vite (Vue.js)
Le préfixe `VITE_` permet de rendre les variables d'environnement accessibles côté client (Vue.js) :
```env
VITE_APP_NAME="${APP_NAME}"
```

---

## 🚀 Lancement

Pour travailler sur le projet, vous devez lancer le serveur Laravel ET la compilation des assets Vite :

**Terminal 1 (Laravel) :**
```bash
composer run dev
```

L'application sera accessible à l'adresse : `http://localhost:8000`

---

## 📂 Structure du projet (Front-end)

Le projet utilise l'architecture suivante pour la partie Vue :

- `resources/js/Pages` : Les pages de l'application.
- `resources/js/Components` : Les composants réutilisables (UI, Formulaires).
- `resources/js/Composables` : La logique partagée (Drag&Drop, etc.).
- `resources/js/routes` : Définition des routes partagées via Ziggy.

## 📝 Commandes utiles

- **Mettre à jour l'index de recherche :** `php artisan scout:import "App\Models\Document"`
- **Lancer les files d'attente :** `php artisan queue:work`
- **Compiler pour la production :** `npm run build`
