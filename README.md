# Application de gestion des rendez-vous médicaux (PHP/MySQL)

Projet simple réalisé selon le cahier des charges (DSI22).

## Installation (XAMPP)

1. Copier le dossier `cabinet_medical` dans `C:\xampp\htdocs\` (ou le dossier web de votre serveur).
2. Démarrer **Apache** et **MySQL** depuis XAMPP.
3. Ouvrir phpMyAdmin et importer le fichier `database.sql` (il crée la base `cabinet_medical` et les données de démonstration).
4. Vérifier si besoin les paramètres de connexion dans `config/database.php` (par défaut : hôte `localhost`, utilisateur `root`, mot de passe vide).
5. Ouvrir dans le navigateur : `http://localhost/cabinet_medical/`

## Comptes de démonstration

Mot de passe pour tous les comptes : `password`

- **Administrateur** : `admin@cabinet.test`
- **Médecin** : `medecin1@cabinet.test` / `medecin2@cabinet.test`
- **Patient** : `patient1@cabinet.test` / `patient2@cabinet.test`

## Structure

- `config/` : connexion PDO (`database.php`)
- `includes/` : header, footer, fonctions (sessions, sécurité, helpers)
- `admin/` : tableau de bord, gestion médecins/spécialités/disponibilités/rendez-vous
- `medecin/` : planning du médecin
- `patient/` : espace patient, prise de rendez-vous, historique
- `assets/css/` : styles
- `database.sql` : structure + données de test

## Fonctionnalités principales

- Inscription patient et authentification (sessions, `password_hash`/`password_verify`)
- Rôles : administrateur, médecin, patient (pages protégées)
- CRUD médecins et spécialités, recherche médecin, activation/désactivation
- Gestion des disponibilités et durée de rendez-vous
- Demande de rendez-vous par créneaux disponibles (sans conflit)
- Traitement des rendez-vous (confirmation/refus/modification/annulation/statuts)
- Planning médecin et espace patient (historique, annulation)
- Sécurité de base : PDO + requêtes préparées, contrôle serveur, `htmlspecialchars()`
