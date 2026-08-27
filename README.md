# Zandu Retraite

Application métier développée avec **Symfony** pour gérer des processus administratifs autour des membres, paiements, contrats, documents et opérations associées.

Le projet met l'accent sur la **modélisation métier**, la sécurité, la traçabilité des opérations, la génération documentaire et la maintenabilité d'une application Symfony qui évolue dans le temps.

> 🚧 **Statut : projet en développement actif**

---

## 🎯 Objectif du projet

Zandu Retraite vise à centraliser plusieurs processus administratifs qui seraient autrement répartis entre documents, fichiers et opérations manuelles.

L'application permet notamment de gérer :

* les membres ;
* les paiements ;
* les contrats ;
* les documents administratifs ;
* les demandes de modification ;
* les notifications ;
* les utilisateurs et leurs accès ;
* la traçabilité des opérations.

---

## ✨ Fonctionnalités principales

### Gestion des membres

* création et mise à jour des dossiers ;
* consultation des informations administratives ;
* suivi des informations liées au membre ;
* gestion du cycle de vie des données.

### Paiements

* enregistrement des paiements ;
* consultation de l'historique ;
* association des paiements aux membres ;
* exploitation des données financières dans les workflows métier.

### Contrats et documents

* gestion de modèles de contrats ;
* création de contrats émis ;
* génération de documents ;
* production de fichiers PDF.

### Administration

* gestion des utilisateurs ;
* contrôle des accès ;
* workflows administratifs ;
* demandes de modification ;
* notifications.

### Audit

Les opérations importantes peuvent être tracées afin de conserver un historique des actions effectuées dans l'application.

---

## 🛠 Stack technique

### Backend

* PHP
* Symfony
* Doctrine ORM
* Doctrine Migrations
* Symfony Security
* Symfony Messenger

### Frontend

* Twig
* Symfony UX
* Turbo
* Stimulus

### Tests

* PHPUnit
* Zenstruck Foundry

### Documents

* Dompdf

### Persistence

* Base de données relationnelle avec Doctrine ORM

---

## 🏗 Structure du projet

```text
src/
├── Command/
├── Controller/
├── Entity/
├── Enum/
├── Factory/
├── Form/
├── Repository/
├── Security/
├── Service/
├── Story/
└── Twig/

migrations/
tests/
templates/
```

Le projet suit une organisation Symfony classique tout en séparant progressivement les responsabilités métier, applicatives et techniques.

---

## 🧠 Sujets d'ingénierie travaillés

Zandu Retraite est également utilisé pour approfondir plusieurs problématiques de Software Engineering :

* modélisation d'un domaine métier ;
* séparation des responsabilités ;
* services applicatifs ;
* gestion des rôles et permissions ;
* workflows administratifs ;
* auditabilité ;
* persistence avec Doctrine ;
* migrations de base de données ;
* génération documentaire ;
* tests automatisés ;
* gestion de l'évolution du modèle métier.

---

## 🧪 Tests

Le projet utilise **PHPUnit** et **Zenstruck Foundry** pour automatiser la validation du comportement de l'application.

Les tests permettent notamment de sécuriser :

* les règles métier ;
* les services ;
* les opérations sur les entités ;
* les scénarios applicatifs importants.

Exécution :

```bash
php bin/phpunit
```

---

## 🚀 Installation locale

### 1. Cloner le repository

```bash
git clone https://github.com/johnmabs/zandu-retraite.git
cd zandu-retraite
```

### 2. Installer les dépendances

```bash
composer install
```

### 3. Configurer l'environnement

Créer ou adapter le fichier :

```text
.env.local
```

avec notamment la configuration de la base de données.

### 4. Créer la base de données

```bash
php bin/console doctrine:database:create
```

### 5. Exécuter les migrations

```bash
php bin/console doctrine:migrations:migrate
```

### 6. Charger éventuellement les données de développement

Selon les stories/fixtures disponibles dans le projet.

### 7. Lancer l'application

Avec Symfony CLI :

```bash
symfony server:start
```

---

## 🗄 Migrations

Les évolutions du modèle de données sont versionnées avec Doctrine Migrations.

```bash
php bin/console doctrine:migrations:migrate
```

Pour générer une nouvelle migration :

```bash
php bin/console make:migration
```

---

## 🔐 Sécurité

Le projet utilise les composants de sécurité Symfony pour gérer l'authentification et l'autorisation.

Les sujets traités comprennent notamment :

* utilisateurs ;
* rôles ;
* contrôle d'accès ;
* protection des opérations administratives.

---

## 📄 Génération de documents

Certaines opérations métier nécessitent la génération de documents PDF.

Le projet utilise **Dompdf** pour transformer les vues/documentations générées par l'application en fichiers PDF exploitables.

---

## 📈 Évolution du projet

Le repository contient plusieurs itérations successives du modèle métier.

Les prochaines évolutions peuvent notamment porter sur :

* renforcement des invariants métier ;
* couverture de tests ;
* amélioration des workflows ;
* audit avancé ;
* notifications ;
* amélioration de l'architecture applicative ;
* observabilité ;
* documentation technique.

---

## 👨‍💻 Auteur

**John Mabiala**

Full-Stack Developer — Symfony • Next.js • PostgreSQL

* GitHub : [github.com/johnmabs](https://github.com/johnmabs)
* LinkedIn : [linkedin.com/in/john-mabiala](https://linkedin.com/in/john-mabiala)

---

## 📌 À propos

Ce repository fait partie de mon portfolio Software Engineering et illustre mon travail sur une **application métier Symfony avec des workflows administratifs, de la persistence, de la sécurité, de l'audit et des tests**.
