#Description du projet

EcoRide est une plateforme de covoiturage permettant aux utilisateurs de proposer ou réserver des trajets en voiture en privilégiant les déplacements écologiques.

Les utilisateurs sont répartis en deux catégories : 
1. Les rôles administratifs (Visiteur, Utilisateur, Employé, Administrateur)
2. Les rôles usagers (Chauffeur, Passager).


### Fonctionnalités principales :

    1. Création de compte & authentification sécurisée
    2. Recherche de covoiturages
    3. Participation à un trajet (gestion des crédits)
    4. Saisie et gestion des trajets pour les chauffeurs
    5. Système d’avis et validation par les employés
    6. Tableau de bord administrateur et gestion des comptes
    7. Mise à jour des crédits / notifications / statistiques

### Technologies utilisées
#### Front-end
1. HTML 5
2. CSS 3 (Classique + Framework Bootstrap 5)
3. JavaScript (Vanilla, Bootstrap 5, JQuery et Owl Carousel)

#### Back-end

1. PHP avec extension PDO pour MySQL/MariaDB

#### Architecture
Modèle - Vue - Contrôleur

#### SGBD
1. MySQL / MariaDB Base de donnees relationelle
2. MongoDB Base de donnees document

#### Editeur de texte
Un editeur de votre choix

# Installation du projet.
## Prérequis
Serveur XAMPP (Recommandé), WAMP ou autre ...
PHP 8.2+
MariaDB 10.4 ou MySQL 5.7
Apache 2.4
Composer
Navigateur Web récent


### 1. Cloner le projet
Placer le projet dans le dossier htdocs de XAMPP

```bash
git clone https://github.com/Nad-dev-junior/ecoride.git
```

Chemin attendu
``` C:\xampp\htdocs\ecoride```

### 2. Installer les dependances PHP
A la racine du projet
``` composer install ```

Le dossier ```vendor/``` est généré automatiquement

### 3. Configuration Apache et réécriture d'URL
1. Le projet utilise Apache avec le mod_rewrite
2. Un fichiers ```.htaccess```  dans le dossier ```public/```

Vérifier que le module ```rewrite``` est activé dans ```httpd.conf```

```bash
LoadModule rewrite_module modules/mod_rewrite.so
```

Ouvrir ```xampp/apache/conf/extra/httpd-vhosts.conf```

Ajouter un VirtualHost, par exemple :

```bash
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/ecoride"
    ServerName ecoride.local
</VirtualHost>
```

Modifier le fichier hosts ```127.0.0.1   ecoride.local```

Redémarrer Apache après modification

### 4. Configuration de la base de données
1. Lancer Apache et MariaDB depuis XAMPP
2. Executer le fichier ```ecoride/src/database/ecoride.sql``` 

### 5. Configuration de l'application
Renseigner les accès à la base de données dans le fichier de configuration ```/core/database.php```

Exemple:
```php
const DB_HOST = 'localhost';
const DB_NAME = 'ecoride';
const DB_USER = 'root';
const DB_PASSWORD = 'root';
```
### 6. point d'entrée de l'application
Le point d'entrée est ```/public/index.php```

L'architecture repose sur le pattern MVC avec routage personnalisé. Accéder à l'application par ```http://ecoride.local```

#### Architecture du projet
```bash
ecoride/
│── config/           # Configuration ( constantes)
│── public/           # Point d’entrée + assets publics
│   ├── assets/
│   └── index.php
│   └── .htaccess
│── src/
│   ├── Controllers/
│   ├── Core/
│   ├── database/
│   ├── Models/
│   ├── Services/
│   └── Views/
│── vendor/            # Dépendances Composer
│── .htaccess
│── composer.json
│── seed.php           # Remplir la base de données 
                       # avec des données de test
```

#### Compte de test
Le fichier ```ecoride/src/database/seeder.php``` contient une logique de fixture executé par ```ecoride/seed.php``` qui permet de peupler la base de données avec des fausses données réalistes pour des tests.

Peupler la base de donnees: ouvrir un terminal a la racine du projet et lancer la commande ```php seed.php```

Le compte administrateur est:
```
username: abdallah1
password: 123456789
```

#### Sécurité
1. Accès base de données via PDO
2. Protection contre les injections SQL
3. Hashage des mots de passe
4. Contrôle des rôles
2.2 Configurer un domaine local (Virtual Host)





