#Description du projet

EcoRide est une plateforme de covoiturage permettant aux utilisateurs de proposer ou réserver des trajets en voiture en privilégiant les déplacements écologiques.
L’application gère plusieurs rôles : Visiteur, Utilisateur, Chauffeur, Passager, Employé, Administrateur.

Fonctionnalités principales :

Création de compte & authentification sécurisée

Recherche de covoiturages

Participation à un trajet (gestion des crédits)

Saisie et gestion des trajets pour les chauffeurs

Système d’avis et validation par les employés

Tableau de bord administrateur et gestion des comptes

Mise à jour des crédits / notifications / statistiques

Technologies utilisées
Front-end

HTML5

CSS3 (Bootstrap)

JavaScript

Back-end

PHP (PDO)

MVC 

Base de données

Base relationnelle : MySQL / MariaDB

Base NoSQL : MongoDB


1 Guide d’installation complet du projet full-stack en local
 1.1Cloner le projet

Ouvrir un terminal.

Cloner le dépôt Git :

git clone <URL_DU_REPO>


2.Ouvrir le projet dans Visual Studio Code.

2.1 Installer les outils nécessaires
- Visual Studio Code

Installer VS Code depuis le site officiel.

- XAMPP

Installer XAMPP, puis lancer :

Apache

MySQL (si vous n’utilisez pas MySQL installé indépendamment)

✔ MySQL installé séparément

Si MySQL a été installé directement sur Windows, vous utiliserez CMD pour gérer votre base.

2.2 Configurer un domaine local (Virtual Host)

Ouvrir :
xampp/apache/conf/extra/httpd-vhosts.conf

Ajouter un VirtualHost, par exemple :

<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/ecoride"
    ServerName ecoride.local
</VirtualHost>


Modifier le fichier hosts :

127.0.0.1   ecoride.local


Redémarrer Apache.

2.3 Installer les dépendances PHP
- Installer Composer

Dans le dossier du projet :

composer install

- Installer Faker
composer require fakerphp/faker

- Installer Whoops (pour afficher les erreurs proprement)
composer require filp/whoops


2.4 Création de la base de données via CMD (méthode utilisée)

-Ouvrir CMD.

-Se connecter à MySQL installé sur votre PC :

-mysql -u root -p
Créer la base :
CREATE DATABASE IF NOT EXISTS ecoride;

2.5 Connexion à la base dans VS Code + création des tables
- Installer l’extension MySQL dans VS Code

Rechercher : MySQL.

Installer l’extension.

- Se connecter à la base MySQL créée via CMD

Dans l’extension MySQL de VS Code :

Ajouter une nouvelle connexion.

Renseigner :

Host : localhost

Port : 3306

Utilisateur : root

Mot de passe : celui de votre MySQL

Database : ecoride

-Création des tables

Une fois connecté dans VS Code :

Ouvrir un nouveau fichier SQL.

CREATE DATABASE IF NOT EXISTS ecoride;
copier les requêtes de création de tables qui sont dans le fichier database.sql.

Les exécuter directement depuis l’extension MySQL.

(C’est à ce moment que vous avez créé toutes tes tables.)

- Générer les données de test

Dans le terminal du projet :

php seed.php

Deploiement: le site n'est pas encore disponible en ligne.




