# 🎉 EventurasWeb2.0

## 📝 Description du Projet
**EventurasWeb2.0** est une plateforme complète de gestion d'événements développée avec **Symfony**. Elle permet de gérer les utilisateurs, les événements, les réservations, les commandes, les partenariats, le forum, la boutique et bien plus encore.

---

## 🗂 Table des Matières
- [Fonctionnalités Principales](#fonctionnalités-principales)
- [Architecture](#architecture)
- [Installation](#installation)
- [Utilisation](#utilisation)
- [Technologies et Bundles](#technologies-et-bundles)
- [Tests](#tests)
- [Contribuer](#contribuer)
- [Crédits](#crédits)
- [Licence](#licence)

---

## ✅ Fonctionnalités Principales

- 🔐 Authentification sécurisée des utilisateurs
- 🗓 Gestion d'événements (création, édition, suppression)
- 🧾 Réservations avec QR Code
- 🛒 Boutique (produits, commandes)
- 📣 Forum pour la communication
- 📦 Gestion de partenariats et sponsors
- 🧑‍💼 Espace administrateur et organisateur
- 📊 Dashboard dynamique
- 📩 Notifications organisateur (avec système temps réel)

---

## 🏗 Architecture

Le projet suit le modèle **MVC Symfony** :
- **Modèles** : `src/Entity`
- **Contrôleurs** : `src/Controller`
- **Vues** : `templates/` (Twig)
- **Sécurité** : `config/packages/security.yaml`
- **Routing** : Annotations `#[Route(...)]` ou `config/routes.yaml`
- **Services** : Logique métier réutilisable

---

## ⚙️ Installation

### 1. Cloner le dépôt
```bash
git clone <url-du-repo>
cd EventurasWeb2.0
```

### 2. Installer les dépendances
```bash
composer install
```

### 3. Configurer les variables d'environnement
```bash
cp .env .env.local
```
Modifiez `.env.local` selon votre environnement (DB, SMTP, STRIPE, etc.)

### 4. Lancer le serveur
```bash
symfony serve
```

### 5. Accéder à l'application
[http://localhost:8000](http://localhost:8000)

---

## 🚀 Utilisation

- 🧑‍🎓 **Participants** : inscription, réservation, consultation des événements
- 🧑‍💼 **Organisateurs** : création/modération d’événements
- 👮‍♂️ **Admin** : gestion centralisée des utilisateurs, réclamations, statistiques

---

## 🧰 Technologies et Bundles

- PHP 8.2+, Symfony 6.x
- Doctrine ORM (Base de données)
- Twig (Moteur de templates)
- Webpack Encore (Assets frontend)
- Symfony Security
- Mailer (Gmail SMTP)
- Stripe (Paiement)
- QR Code (endroid/qr-code)
- Bootstrap 5, Chart.js, SweetAlert2

---

## 🧪 Tests

```bash
php bin/phpunit
```

---

## 🤝 Contribuer

1. Forkez le projet
2. Créez une branche : `git checkout -b ma-nouvelle-feature`
3. Commit : `git commit -am 'Ajout de ma feature'`
4. Push : `git push origin ma-nouvelle-feature`
5. Créez une Pull Request

---

## 🙏 Crédits

Projet réalisé par [Votre Nom]  
Encadré par [Nom Encadrant / École]  
Icones et Design : Kaiadmin, FontAwesome, Bootstrap

---

## 📄 Licence

Ce projet est sous licence MIT.

---
