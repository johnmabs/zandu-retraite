# Zandu Retraite

Application métier de gestion administrative destinée au suivi
des membres, paiements, documents, contrats et opérations associées.

Le projet est développé avec Symfony et met l'accent sur la
modélisation métier, la sécurité, la traçabilité et les workflows
administratifs.

## Fonctionnalités principales

- Gestion des membres
- Gestion des paiements
- Génération et suivi de documents
- Modèles et contrats émis
- Notifications
- Journal d'audit
- Gestion des demandes de modification
- Gestion des utilisateurs administratifs
- Production de documents PDF

## Stack

- PHP 8.4+
- Symfony 8.1
- Doctrine ORM
- Doctrine Migrations
- Twig
- Symfony UX / Turbo / Stimulus
- Symfony Security
- Symfony Messenger
- PHPUnit
- Zenstruck Foundry
- Dompdf

## Architecture

Le projet utilise une architecture Symfony structurée autour de :

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
```

## Engineering topics
- Modélisation d'un domaine métier
- Gestion des rôles et permissions
- Auditabilité
- Workflows administratifs
- Génération documentaire
- Persistence avec Doctrine
- Tests automatisés
- Migrations de base de données
- Services applicatifs

## Tests

Le projet inclut une suite de tests basée sur PHPUnit
et Zenstruck Foundry.

## Statut
🚧 Projet en développement actif.

Le repository représente plusieurs itérations successives
du modèle métier et de l'architecture.

**Description :**

> Symfony business application for member, payment, contract and administrative workflow management.

Topics :

```text
symfony
php
doctrine
postgresql
business-application
twig
phpunit
ddd
```
