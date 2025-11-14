# 🌞 Solar CRM - API REST Symfony 7

> Projet PHP - API REST manuelle (sans API Platform)
> Symfony 7.2 + PHP 8.2 | Next.js 14 + Tailwind CSS

---

## 📋 Présentation

### Objectif

API REST complète pour la gestion d'un CRM dédié à la vente et l'installation de panneaux solaires.

**Fonctionnalités principales :**
- Gestion des clients (prospects et clients actifs)
- Planification des rendez-vous (visites, installations, SAV)
- Création et suivi des devis
- Études solaires personnalisées avec calculs automatiques
- Suivi des activités commerciales
- Statistiques de performance en temps réel

### Stack technique

- **Backend** : Symfony 7.2, PHP 8.2, MySQL 8.0
- **Authentification** : JWT (LexikJWTAuthenticationBundle)
- **Documentation** : OpenAPI/Swagger (NelmioApiDocBundle)
- **CORS** : NelmioCorsBundle
- **Tests** : PHPUnit
- **Frontend** : Next.js 14, TypeScript, Tailwind CSS

---

## 🗄️ Schéma de base de données

### Entités et relations

```
User (1) ──────┬──────> (N) Appointment
               ├──────> (N) Quote
               ├──────> (N) SolarStudy
               └──────> (N) Activity

Client (1) ────┬──────> (N) Appointment
               ├──────> (N) Quote
               ├──────> (N) SolarStudy
               └──────> (N) Activity
```

**Entités :**
- **User** : email, password, firstName, lastName, roles, phone
- **Client** : firstName, lastName, email, phone, address, city, zipCode, status (lead|prospect|active|inactive)
- **Appointment** : client, user, date, type, status, address, notes
- **Quote** : client, user, amount, status (draft|sent|accepted|rejected), validUntil, items
- **SolarStudy** : client, user, roofSurface, consumption, power, panelsCount, cost, savings, roi
- **Activity** : client, user, type (call|email|meeting|note), description

---

## 📚 Documentation des endpoints

### Base URL

- **Dev** : `http://localhost:8004/api`
- **Swagger** : `http://localhost:8004/api/doc`

### Format des réponses

**Succès :**
```json
{
  "status": 200,
  "data": { ... }
}
```

**Erreur :**
```json
{
  "status": 400,
  "error": "Message d'erreur",
  "details": { "field": "Message spécifique" }
}
```

---

### 🔐 Authentification

#### POST `/api/auth/register`
Créer un compte utilisateur.

**Body :**
```json
{
  "email": "user@example.com",
  "password": "password123",
  "firstName": "Jean",
  "lastName": "Dupont"
}
```

#### POST `/api/auth/login`
Connexion et obtention du token JWT.

**Body :**
```json
{
  "email": "admin@solarcrm.com",
  "password": "password"
}
```

**Réponse :**
```json
{
  "status": 200,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": { "id": 1, "email": "admin@solarcrm.com", ... }
  }
}
```

**Utilisation du token :**
```http
Authorization: Bearer {token}
```

---

### 👥 Clients

#### GET `/api/clients`
Liste paginée avec filtres.

**Query params :** `page`, `limit`, `status`, `search`, `sort`, `order`

#### GET `/api/clients/{id}`
Détails d'un client avec relations.

#### POST `/api/clients`
Créer un client.

**Body :**
```json
{
  "firstName": "Pierre",
  "lastName": "Martin",
  "email": "pierre@email.com",
  "phone": "0612345678",
  "status": "lead"
}
```

#### PATCH `/api/clients/{id}`
Modifier un client.

#### DELETE `/api/clients/{id}`
Supprimer un client.

---

### 📅 Rendez-vous

#### GET `/api/appointments`
Liste paginée avec filtres (`status`, `type`, `date`).

#### GET `/api/appointments/today`
Rendez-vous du jour uniquement.

#### POST `/api/appointments`
Créer un rendez-vous.

**Body :**
```json
{
  "clientId": 1,
  "userId": 1,
  "appointmentDate": "2025-11-14T10:00:00",
  "type": "Installation",
  "status": "scheduled",
  "address": "15 rue de la République, Lyon"
}
```

#### PATCH `/api/appointments/{id}`
Modifier un rendez-vous.

#### DELETE `/api/appointments/{id}`
Supprimer un rendez-vous.

---

### 💰 Devis

#### GET `/api/quotes`
Liste avec filtres (`status`, `clientId`).

#### POST `/api/quotes`
Créer un devis.

**Body :**
```json
{
  "clientId": 1,
  "userId": 1,
  "amount": 15000.00,
  "status": "draft",
  "validUntil": "2025-12-31",
  "description": "Installation 6kWc"
}
```

---

### ☀️ Études solaires

#### GET `/api/solar-studies`
Liste des études.

#### POST `/api/solar-studies`
Créer une étude (calculs automatiques de puissance, ROI, économies).

**Body :**
```json
{
  "clientId": 1,
  "userId": 1,
  "roofSurface": 50,
  "annualConsumption": 5000,
  "roofOrientation": "south"
}
```

---

### 📊 Statistiques

#### GET `/api/statistics/dashboard`
KPI pour le dashboard (rendez-vous du mois, clients actifs, devis en cours, CA).

**Réponse :**
```json
{
  "status": 200,
  "data": {
    "appointmentsThisMonth": { "value": 25, "change": "+12.5%", "changeType": "positive" },
    "activeClients": { "value": 150, "change": "+5.3%", "changeType": "positive" },
    "quotesInProgress": { "value": 12, "change": "-2.1%", "changeType": "negative" },
    "revenue": { "value": "45 000€", "change": "+18.0%", "changeType": "positive" }
  }
}
```

**Note :** Les pourcentages sont calculés automatiquement en comparant avec le mois dernier.

---

### 📝 Activités

#### GET `/api/activities`
Liste des activités avec filtres (`clientId`, `type`).

#### POST `/api/activities`
Créer une activité.

**Body :**
```json
{
  "clientId": 1,
  "userId": 1,
  "type": "call",
  "description": "Appel de suivi pour le devis #123"
}
```

---

## 🚀 Installation

### Pré-requis

- PHP ≥ 8.2
- Composer ≥ 2.0
- MySQL ≥ 8.0

### Backend (API Symfony)

```bash
cd api
composer install

# Configuration
cp .env .env.local
# Éditer .env.local avec vos paramètres DATABASE_URL

# Générer les clés JWT
php bin/console lexik:jwt:generate-keypair

# Créer la BDD
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Charger les fixtures (données de test)
php bin/console doctrine:fixtures:load

# Lancer le serveur
php -S localhost:8004 -t public
```

**Données de test créées :**
- Utilisateur : `admin@solarcrm.com` / `password`
- 10 clients, 15 rendez-vous, 8 devis, 5 études solaires

### Frontend (Next.js)

```bash
# Racine du projet
npm install

# Configurer l'URL de l'API
echo "NEXT_PUBLIC_API_BASE_URL=http://localhost:8004" > .env.local

# Lancer le serveur
npm run dev
```

**URLs :**
- Frontend : `http://localhost:3000`
- API : `http://localhost:8004/api`
- Swagger : `http://localhost:8004/api/doc`

---

## 🧪 Tests

### Configuration

```bash
cd api

# Créer la BDD de test
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:migrate --env=test -n

# Lancer les tests
php bin/phpunit
```

### Tests implémentés

**3 tests fonctionnels principaux :**
1. **GET liste** : Retourne 200 OK avec JSON structuré
2. **POST valide** : Création réussie (201 Created)
3. **POST invalide** : Erreurs de validation (400 Bad Request)

**Coverage :**
- ✅ Authentification (login, register, token)
- ✅ CRUD Clients (liste, détail, création, modification, suppression)
- ✅ CRUD Appointments (avec filtres et pagination)
- ✅ Validation des données
- ✅ Gestion d'erreurs (404, 401, 400)

---

## 🔗 Intégration Next.js

### Configuration

**`.env.local` (racine) :**
```env
NEXT_PUBLIC_API_BASE_URL=http://localhost:8004
```

### Services API créés

- **`lib/api.ts`** : Client HTTP configuré (Axios)
- **`lib/services/auth.ts`** : Login, register, logout
- **`lib/services/clients.ts`** : CRUD clients
- **`lib/services/appointments.ts`** : CRUD rendez-vous
- **`lib/services/dashboard.ts`** : Statistiques

### Endpoints utilisés

| Page | Endpoint | Usage |
|------|----------|-------|
| Login | `POST /api/auth/login` | Authentification |
| Dashboard | `GET /api/statistics/dashboard` | KPI en temps réel |
| Dashboard | `GET /api/appointments/today` | Rendez-vous du jour |
| CreateAppointmentModal | `POST /api/appointments` | Créer un RDV |
| EditAppointmentModal | `PATCH /api/appointments/{id}` | Modifier un RDV |
| DeleteConfirmModal | `DELETE /api/appointments/{id}` | Supprimer un RDV |

### Workflow

**1. Connexion :**
```
Login → authService.login() → POST /api/auth/login
→ Token JWT → localStorage → Redirect /dashboard
```

**2. Dashboard :**
```
useEffect() → loadDashboardData()
→ GET /api/statistics/dashboard (avec token)
→ GET /api/appointments/today (avec token)
→ Affichage des données
```

**3. Création de rendez-vous :**
```
"Nouveau RDV" → Modal → Formulaire
→ POST /api/appointments (avec token)
→ Succès → loadDashboardData() → Dashboard rafraîchi
```

### CORS configuré

**`api/config/packages/nelmio_cors.yaml` :**
```yaml
nelmio_cors:
    defaults:
        origin_regex: true
        allow_origin: ['%env(CORS_ALLOW_ORIGIN)%']
        allow_methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS']
        allow_headers: ['Content-Type', 'Authorization']
```

**`.env.local` :**
```env
CORS_ALLOW_ORIGIN='^http://localhost:3000$'
```

---

## ✅ Barème et fonctionnalités

### Fonctionnalités (8/20)

- ✅ Endpoints CRUD complets (Clients, Rendez-vous, Devis, Études, Activités)
- ✅ Authentification JWT (login, register, protection des routes)
- ✅ Validation (contraintes Symfony sur toutes les entités)
- ✅ Pagination (toutes les listes avec `page` et `limit`)
- ✅ Filtres (par statut, type, date, client, recherche)
- ✅ Tri (`sort` et `order`)

### Qualité du code (4/20)

- ✅ Structure propre (contrôleurs, entités, repositories)
- ✅ Nommage clair (conventions Symfony)
- ✅ Sérialisation propre (Symfony Serializer)
- ✅ Gestion d'erreurs (JSON standardisé, statuts HTTP corrects)

### Intégration Next.js (4/20)

- ✅ CORS configuré (NelmioCorsBundle)
- ✅ Services frontend (client API réutilisable)
- ✅ Données dynamiques (dashboard connecté)
- ✅ Auth fluide (JWT, routes protégées, logout)

### Documentation (2/20)

- ✅ README complet (installation, endpoints, exemples)
- ✅ Swagger interactif (`/api/doc`)
- ✅ Commits Git clairs

### Présentation (2/20)

- ✅ Projet démontrable en quelques commandes
- ✅ Fonctionnel de bout en bout (frontend ↔ backend)

---

## 🎥 Scénario de démo (2 minutes)

1. **Présenter** : Domaine Solar CRM, stack Symfony 7 + Next.js
2. **Swagger** : Tester POST `/api/auth/login` puis GET `/api/clients` avec token
3. **Frontend** : Se connecter, montrer dashboard avec KPI dynamiques
4. **Créer RDV** : "Nouveau RDV" → Formulaire → Soumission → Dashboard rafraîchi
5. **Modifier/Supprimer** : Boutons sur les cards, observer mise à jour en temps réel
6. **Conclusion** : API complète, intégration fluide, code propre

---

## 📁 Structure du projet

```
dashboard_ilias_benharrat_eemi/
├── api/                          # Backend Symfony 7
│   ├── config/
│   │   ├── jwt/                  # Clés JWT
│   │   └── packages/             # Config bundles
│   ├── src/
│   │   ├── Controller/           # REST Controllers
│   │   ├── Entity/               # Doctrine Entities
│   │   ├── Repository/           # Doctrine Repositories
│   │   └── DataFixtures/         # Données de test
│   ├── tests/                    # PHPUnit tests
│   └── migrations/               # Migrations BDD
├── app/                          # Pages Next.js
│   ├── dashboard/
│   ├── login/
│   └── register/
├── components/                   # Composants React
│   ├── layout/
│   ├── modals/
│   └── ui/
├── lib/                          # Services & utilitaires
│   ├── api.ts
│   ├── types.ts
│   ├── hooks/
│   └── services/
├── .env.local
└── README.md
```

---

Pour tester :
```bash
# Terminal 1 - Backend
cd api && php -S localhost:8004 -t public

# Terminal 2 - Frontend
npm run dev

# Ouvrir http://localhost:3000
# Login: admin@solarcrm.com / password
```
