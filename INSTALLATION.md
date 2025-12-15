# Installation et Configuration du CRM Multi-Tenant

## Prérequis

- PHP 8.3+
- MySQL/MariaDB
- Composer
- Node.js & NPM

## Installation

### 1. Cloner et Installer les Dépendances

```bash
composer install
npm install
```

### 2. Configuration de l'Environnement

Copier `.env.example` vers `.env` et configurer :

```env
APP_NAME="CRM Multi-Tenant"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

# Base de données CENTRALE (pour super admin et tenants)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crm_central
DB_USERNAME=root
DB_PASSWORD=

# Tenancy
TENANCY_DATABASE_PREFIX=tenant_
```

Générer la clé d'application :
```bash
php artisan key:generate
```

### 3. Créer les Bases de Données

#### Base Centrale
```sql
CREATE DATABASE crm_central CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### Bases Tenant (exemples)
```sql
CREATE DATABASE tenant_company1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE tenant_company2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 4. Exécuter les Migrations

#### Migrations Centrales (Super Admin + Tenants)
```bash
php artisan module:migrate UsersGuard
```

Ou pour toutes les migrations centrales :
```bash
php artisan migrate
```

#### Migrations Tenant (Pour chaque tenant)
```bash
# Pour tous les tenants
php artisan tenants:migrate

# Pour un tenant spécifique
php artisan tenants:migrate --tenants=company1
```

### 5. Créer le Premier Super Admin

Créer un seeder ou utiliser Tinker :

```bash
php artisan tinker
```

```php
use Modules\UsersGuard\Entities\SuperAdmin;

SuperAdmin::create([
    'username' => 'superadmin',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
    'firstname' => 'Super',
    'lastname' => 'Admin',
    'application' => 'superadmin',
    'is_active' => true,
]);
```

### 6. Créer un Tenant

```php
use Modules\UsersGuard\Entities\Tenant;

$tenant = Tenant::create([
    'site_id' => 'company1',
    'site_host' => 'tenant1.local',
    'site_db_name' => 'tenant_company1',
    'company_name' => 'Company One',
    'company_email' => 'contact@company1.com',
    'is_active' => true,
]);

// Ajouter le domaine
$tenant->domains()->create([
    'domain' => 'tenant1.local',
    'is_primary' => true,
]);

// Exécuter les migrations pour ce tenant
\Artisan::call('tenants:migrate', ['--tenants' => [$tenant->site_id]]);
```

### 7. Créer un Utilisateur Tenant

```php
use Modules\UsersGuard\Entities\TenantUser;

// Initialiser le tenant
tenancy()->initialize($tenant);

TenantUser::create([
    'username' => 'admin',
    'email' => 'admin@company1.com',
    'password' => bcrypt('password'),
    'firstname' => 'Admin',
    'lastname' => 'Company1',
    'application' => 'admin',
    'is_active' => true,
]);

// Terminer le contexte tenant
tenancy()->end();
```

### 8. Configuration des Hosts (Développement Local)

Ajouter dans `/etc/hosts` (Linux/Mac) ou `C:\Windows\System32\drivers\etc\hosts` (Windows) :

```
127.0.0.1   tenant1.local
127.0.0.1   company2.localhost
```

## Utilisation

### Démarrer le Serveur

```bash
php artisan serve
```

Ou avec Laravel Sail :
```bash
./vendor/bin/sail up
```

### Tester l'API

#### Super Admin Login
```bash
curl -X POST http://localhost:8000/api/superadmin/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"superadmin","password":"password"}'
```

#### Tenant Admin Login
```bash
curl -X POST http://tenant1.local:8000/api/admin/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password","application":"admin"}'
```

Ou avec header X-Tenant-ID :
```bash
curl -X POST http://localhost:8000/api/admin/auth/login \
  -H "Content-Type: application/json" \
  -H "X-Tenant-ID: company1" \
  -d '{"username":"admin","password":"password","application":"admin"}'
```

## Structure des Bases de Données

### Base Centrale (`crm_central`)
- `users` - Super admins
- `tenants` - Liste des tenants
- `domains` - Domaines des tenants
- `personal_access_tokens` - Tokens Sanctum

### Base Tenant (`tenant_*`)
- `users` - Utilisateurs admin et frontend
- `roles` - Rôles (Spatie Permission)
- `permissions` - Permissions (Spatie Permission)
- `model_has_roles` - Pivot user-roles
- `model_has_permissions` - Pivot user-permissions
- `role_has_permissions` - Pivot role-permissions

## Commandes Artisan Utiles

```bash
# Voir les modules installés
php artisan module:list

# Migrer un module spécifique
php artisan module:migrate UsersGuard

# Rollback module
php artisan module:migrate-rollback UsersGuard

# Voir les tenants
php artisan tenants:list

# Migrer tous les tenants
php artisan tenants:migrate

# Seed un tenant
php artisan tenants:seed --tenants=company1
```

## Troubleshooting

### Erreur de connexion base de données tenant
Vérifier que :
1. La base tenant existe
2. Le tenant est actif dans la table `tenants`
3. Le domaine existe dans la table `domains`

### Token invalide
Vérifier que :
1. `APP_KEY` est défini dans `.env`
2. Les tables `personal_access_tokens` existent dans les bonnes bases
3. Le guard utilisé correspond au type d'utilisateur

### Permissions ne fonctionnent pas
Vérifier que :
1. Les migrations Spatie ont été exécutées dans la base tenant
2. Le trait `HasRoles` est utilisé dans le modèle `TenantUser`
3. Le cache est vidé : `php artisan permission:cache-reset`

## Développement

### Créer un nouveau module
```bash
php artisan module:make ModuleName
```

### Créer une migration dans un module
```bash
php artisan module:make-migration create_table_name ModuleName
```

### Créer un contrôleur dans un module
```bash
php artisan module:make-controller ControllerName ModuleName
```

### Créer un modèle dans un module
```bash
php artisan module:make-model ModelName ModuleName
```
