# Documentation API CMS

## Introduction

Ce module CMS permet de gérer le contenu du frontend via une API REST. Il comprend la gestion des pages, blocs de contenu, menus, médias et paramètres.

**Base URL:** `http://api.local/api`

---

## Authentification

### Routes Superadmin (CRUD complet)
Toutes les routes superadmin nécessitent un token Bearer valide.

```
Authorization: Bearer {token}
```

### Routes Frontend (Lecture seule)
Les routes frontend sont publiques et ne nécessitent pas d'authentification.

---

## 1. Pages

### Superadmin

#### Lister les pages
```http
GET /api/superadmin/cms/pages
Authorization: Bearer {token}
```

**Paramètres query:**
| Paramètre | Type | Description |
|-----------|------|-------------|
| per_page | integer | Nombre d'éléments par page (défaut: 15) |
| search | string | Recherche dans titre, slug, contenu |
| status | string | Filtrer par statut (draft, published, archived) |
| is_active | boolean | Filtrer par état actif |
| parent_id | integer/null | Filtrer par page parente |

**Exemple de réponse:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Accueil",
      "slug": "home",
      "content": "<h1>Bienvenue</h1>",
      "template": "home",
      "status": "published",
      "is_active": true,
      "parent": null,
      "children": [],
      "blocks": []
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 3
  }
}
```

#### Créer une page
```http
POST /api/superadmin/cms/pages
Authorization: Bearer {token}
Content-Type: application/json
```

**Corps de la requête:**
```json
{
  "title": "Ma Page",
  "slug": "ma-page",
  "content": "<h1>Contenu</h1><p>Description</p>",
  "excerpt": "Résumé court",
  "template": "default",
  "meta_title": "Ma Page - Mon Site",
  "meta_description": "Description SEO",
  "meta_keywords": "mot-clé1, mot-clé2",
  "featured_image": "/images/featured.jpg",
  "parent_id": null,
  "status": "draft",
  "order": 1,
  "is_active": true
}
```

**Champs obligatoires:** `title`

**Templates disponibles:**
- `default` - Page standard
- `home` - Page d'accueil
- `contact` - Page de contact
- `landing` - Landing page
- `full-width` - Pleine largeur
- `sidebar` - Avec barre latérale

#### Afficher une page
```http
GET /api/superadmin/cms/pages/{id}
Authorization: Bearer {token}
```

#### Modifier une page
```http
PUT /api/superadmin/cms/pages/{id}
Authorization: Bearer {token}
Content-Type: application/json
```

#### Supprimer une page (soft delete)
```http
DELETE /api/superadmin/cms/pages/{id}
Authorization: Bearer {token}
```

#### Restaurer une page supprimée
```http
POST /api/superadmin/cms/pages/{id}/restore
Authorization: Bearer {token}
```

#### Supprimer définitivement
```http
DELETE /api/superadmin/cms/pages/{id}/force
Authorization: Bearer {token}
```

#### Publier une page
```http
POST /api/superadmin/cms/pages/{id}/publish
Authorization: Bearer {token}
```

#### Dépublier une page
```http
POST /api/superadmin/cms/pages/{id}/unpublish
Authorization: Bearer {token}
```

#### Dupliquer une page
```http
POST /api/superadmin/cms/pages/{id}/duplicate
Authorization: Bearer {token}
```

#### Réordonner les pages
```http
POST /api/superadmin/cms/pages/reorder
Authorization: Bearer {token}
Content-Type: application/json
```

```json
{
  "pages": [
    {"id": 1, "order": 1, "parent_id": null},
    {"id": 2, "order": 2, "parent_id": null},
    {"id": 3, "order": 1, "parent_id": 1}
  ]
}
```

#### Obtenir les templates disponibles
```http
GET /api/superadmin/cms/pages/templates
Authorization: Bearer {token}
```

### Frontend (Public)

#### Lister les pages publiées
```http
GET /api/cms/pages
```

**Paramètres query:**
| Paramètre | Type | Description |
|-----------|------|-------------|
| per_page | integer | Nombre par page (défaut: 15) |
| template | string | Filtrer par template |
| parent_id | integer/null | Filtrer par parent |

#### Obtenir la page d'accueil
```http
GET /api/cms/pages/home
```

#### Obtenir une page par slug
```http
GET /api/cms/pages/{slug}
```

#### Obtenir l'arbre des pages
```http
GET /api/cms/pages/tree
```

---

## 2. Blocs de contenu

### Superadmin

#### Lister les blocs
```http
GET /api/superadmin/cms/blocks
Authorization: Bearer {token}
```

**Paramètres query:**
| Paramètre | Type | Description |
|-----------|------|-------------|
| per_page | integer | Nombre par page (défaut: 15) |
| search | string | Recherche dans nom, identifiant |
| type | string | Filtrer par type |
| page_id | integer | Filtrer par page associée |

#### Créer un bloc
```http
POST /api/superadmin/cms/blocks
Authorization: Bearer {token}
Content-Type: application/json
```

```json
{
  "name": "Hero Section",
  "identifier": "hero-home",
  "type": "hero",
  "content": {
    "title": "Bienvenue",
    "subtitle": "Votre solution",
    "button_text": "Découvrir",
    "button_url": "/a-propos"
  },
  "settings": {
    "background_color": "#f5f5f5",
    "text_color": "#333333"
  },
  "page_id": null,
  "order": 1,
  "is_active": true
}
```

**Types de blocs disponibles:**
- `text` - Texte simple
- `html` - HTML personnalisé
- `hero` - Section héro
- `cta` - Appel à l'action
- `features` - Liste de fonctionnalités
- `testimonials` - Témoignages
- `gallery` - Galerie d'images
- `video` - Vidéo
- `contact` - Formulaire de contact
- `faq` - FAQ
- `pricing` - Tableau de prix
- `team` - Équipe
- `stats` - Statistiques
- `newsletter` - Newsletter

#### Afficher un bloc
```http
GET /api/superadmin/cms/blocks/{id}
Authorization: Bearer {token}
```

#### Modifier un bloc
```http
PUT /api/superadmin/cms/blocks/{id}
Authorization: Bearer {token}
Content-Type: application/json
```

#### Supprimer un bloc
```http
DELETE /api/superadmin/cms/blocks/{id}
Authorization: Bearer {token}
```

#### Restaurer un bloc
```http
POST /api/superadmin/cms/blocks/{id}/restore
Authorization: Bearer {token}
```

#### Supprimer définitivement
```http
DELETE /api/superadmin/cms/blocks/{id}/force
Authorization: Bearer {token}
```

#### Réordonner les blocs
```http
POST /api/superadmin/cms/blocks/reorder
Authorization: Bearer {token}
Content-Type: application/json
```

```json
{
  "blocks": [
    {"id": 1, "order": 1},
    {"id": 2, "order": 2}
  ]
}
```

#### Obtenir les types disponibles
```http
GET /api/superadmin/cms/blocks/types
Authorization: Bearer {token}
```

### Frontend (Public)

#### Lister les blocs actifs
```http
GET /api/cms/blocks
```

**Paramètres query:**
| Paramètre | Type | Description |
|-----------|------|-------------|
| per_page | integer | Nombre par page (défaut: 50) |
| type | string | Filtrer par type |

#### Obtenir un bloc par identifiant
```http
GET /api/cms/blocks/{identifier}
```

#### Obtenir plusieurs blocs
```http
GET /api/cms/blocks/multiple?identifiers[]=hero-home&identifiers[]=footer-cta
```

---

## 3. Menus

### Superadmin

#### Lister les menus
```http
GET /api/superadmin/cms/menus
Authorization: Bearer {token}
```

#### Créer un menu
```http
POST /api/superadmin/cms/menus
Authorization: Bearer {token}
Content-Type: application/json
```

```json
{
  "name": "Menu Principal",
  "identifier": "main-menu",
  "description": "Menu de navigation principal",
  "location": "header",
  "is_active": true
}
```

**Emplacements disponibles:**
- `header` - Menu principal (header)
- `footer` - Menu pied de page
- `sidebar` - Menu latéral
- `mobile` - Menu mobile

#### Afficher un menu
```http
GET /api/superadmin/cms/menus/{id}
Authorization: Bearer {token}
```

#### Modifier un menu
```http
PUT /api/superadmin/cms/menus/{id}
Authorization: Bearer {token}
Content-Type: application/json
```

#### Supprimer un menu
```http
DELETE /api/superadmin/cms/menus/{id}
Authorization: Bearer {token}
```

#### Restaurer un menu
```http
POST /api/superadmin/cms/menus/{id}/restore
Authorization: Bearer {token}
```

#### Supprimer définitivement
```http
DELETE /api/superadmin/cms/menus/{id}/force
Authorization: Bearer {token}
```

#### Obtenir les emplacements
```http
GET /api/superadmin/cms/menus/locations
Authorization: Bearer {token}
```

### Frontend (Public)

#### Obtenir un menu par identifiant
```http
GET /api/cms/menus/{identifier}
```

#### Obtenir un menu par emplacement
```http
GET /api/cms/menus/location/{location}
```

---

## 4. Éléments de menu

### Superadmin

#### Lister les éléments
```http
GET /api/superadmin/cms/menu-items
Authorization: Bearer {token}
```

**Paramètres query:**
| Paramètre | Type | Description |
|-----------|------|-------------|
| per_page | integer | Nombre par page (défaut: 50) |
| menu_id | integer | Filtrer par menu |

#### Créer un élément
```http
POST /api/superadmin/cms/menu-items
Authorization: Bearer {token}
Content-Type: application/json
```

```json
{
  "menu_id": 1,
  "parent_id": null,
  "title": "Accueil",
  "url": "/",
  "route": null,
  "route_params": null,
  "page_id": null,
  "target": "_self",
  "icon": "home",
  "css_class": "nav-link",
  "order": 1,
  "is_active": true
}
```

**Options de lien:**
- `url` - URL directe
- `page_id` - Lier à une page CMS (génère automatiquement l'URL)
- `route` + `route_params` - Utiliser une route Laravel nommée

**Targets disponibles:** `_self`, `_blank`, `_parent`, `_top`

#### Afficher un élément
```http
GET /api/superadmin/cms/menu-items/{id}
Authorization: Bearer {token}
```

#### Modifier un élément
```http
PUT /api/superadmin/cms/menu-items/{id}
Authorization: Bearer {token}
Content-Type: application/json
```

#### Supprimer un élément
```http
DELETE /api/superadmin/cms/menu-items/{id}
Authorization: Bearer {token}
```

#### Restaurer un élément
```http
POST /api/superadmin/cms/menu-items/{id}/restore
Authorization: Bearer {token}
```

#### Supprimer définitivement
```http
DELETE /api/superadmin/cms/menu-items/{id}/force
Authorization: Bearer {token}
```

#### Réordonner les éléments
```http
POST /api/superadmin/cms/menu-items/reorder
Authorization: Bearer {token}
Content-Type: application/json
```

```json
{
  "items": [
    {"id": 1, "order": 1, "parent_id": null},
    {"id": 2, "order": 2, "parent_id": null},
    {"id": 3, "order": 1, "parent_id": 2}
  ]
}
```

---

## 5. Médias

### Superadmin

#### Lister les médias
```http
GET /api/superadmin/cms/media
Authorization: Bearer {token}
```

**Paramètres query:**
| Paramètre | Type | Description |
|-----------|------|-------------|
| per_page | integer | Nombre par page (défaut: 24) |
| search | string | Recherche dans nom, filename, alt |
| type | string | Filtrer par type MIME (image, video, application) |
| folder | string | Filtrer par dossier |

#### Uploader un fichier
```http
POST /api/superadmin/cms/media
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Form data:**
| Champ | Type | Description |
|-------|------|-------------|
| file | file | Fichier à uploader (max 50MB) |
| folder | string | Dossier de destination |
| alt | string | Texte alternatif |
| title | string | Titre du média |
| caption | string | Légende |

**Exemple avec curl:**
```bash
curl -X POST "http://api.local/api/superadmin/cms/media" \
  -H "Authorization: Bearer {token}" \
  -F "file=@/path/to/image.jpg" \
  -F "folder=uploads" \
  -F "alt=Description de l'image"
```

#### Afficher un média
```http
GET /api/superadmin/cms/media/{id}
Authorization: Bearer {token}
```

#### Modifier les métadonnées
```http
PUT /api/superadmin/cms/media/{id}
Authorization: Bearer {token}
Content-Type: application/json
```

```json
{
  "name": "Nouveau nom",
  "alt": "Nouvelle description",
  "title": "Nouveau titre",
  "caption": "Nouvelle légende",
  "folder": "images"
}
```

#### Supprimer un média
```http
DELETE /api/superadmin/cms/media/{id}
Authorization: Bearer {token}
```

#### Restaurer un média
```http
POST /api/superadmin/cms/media/{id}/restore
Authorization: Bearer {token}
```

#### Supprimer définitivement
```http
DELETE /api/superadmin/cms/media/{id}/force
Authorization: Bearer {token}
```

#### Suppression en masse
```http
POST /api/superadmin/cms/media/bulk-delete
Authorization: Bearer {token}
Content-Type: application/json
```

```json
{
  "ids": [1, 2, 3]
}
```

#### Lister les dossiers
```http
GET /api/superadmin/cms/media/folders
Authorization: Bearer {token}
```

---

## 6. Paramètres

### Superadmin

#### Lister les paramètres
```http
GET /api/superadmin/cms/settings
Authorization: Bearer {token}
```

**Paramètres query:**
| Paramètre | Type | Description |
|-----------|------|-------------|
| group | string | Filtrer par groupe |

**Réponse (groupée):**
```json
{
  "data": {
    "general": [
      {"key": "site_name", "value": "Mon Site"},
      {"key": "site_description", "value": "Description"}
    ],
    "system": [
      {"key": "maintenance_mode", "value": "false", "casted_value": false}
    ]
  }
}
```

#### Créer un paramètre
```http
POST /api/superadmin/cms/settings
Authorization: Bearer {token}
Content-Type: application/json
```

```json
{
  "key": "site_name",
  "value": "Mon Site CMS",
  "group": "general",
  "type": "string",
  "options": null,
  "is_public": true
}
```

**Types disponibles:**
- `string` - Chaîne de caractères
- `integer` - Nombre entier
- `float` - Nombre décimal
- `boolean` - Booléen (true/false)
- `json` - Objet JSON
- `array` - Tableau

#### Afficher un paramètre
```http
GET /api/superadmin/cms/settings/{id}
Authorization: Bearer {token}
```

#### Modifier un paramètre
```http
PUT /api/superadmin/cms/settings/{id}
Authorization: Bearer {token}
Content-Type: application/json
```

```json
{
  "value": "Nouvelle valeur",
  "is_public": true
}
```

#### Supprimer un paramètre
```http
DELETE /api/superadmin/cms/settings/{id}
Authorization: Bearer {token}
```

#### Mise à jour en masse
```http
POST /api/superadmin/cms/settings/bulk
Authorization: Bearer {token}
Content-Type: application/json
```

```json
{
  "settings": [
    {"key": "site_name", "value": "Nouveau nom", "group": "general"},
    {"key": "site_description", "value": "Nouvelle description"}
  ]
}
```

#### Lister les groupes
```http
GET /api/superadmin/cms/settings/groups
Authorization: Bearer {token}
```

### Frontend (Public)

> **Note:** Seuls les paramètres avec `is_public: true` sont accessibles.

#### Lister les paramètres publics
```http
GET /api/cms/settings
```

**Réponse:**
```json
{
  "data": {
    "site_name": "Mon Site CMS",
    "site_description": "Description du site"
  }
}
```

#### Obtenir un paramètre
```http
GET /api/cms/settings/{key}
```

#### Obtenir plusieurs paramètres
```http
GET /api/cms/settings/multiple
Content-Type: application/json
```

```json
{
  "keys": ["site_name", "site_description"]
}
```

---

## Codes de réponse HTTP

| Code | Description |
|------|-------------|
| 200 | Succès |
| 201 | Créé avec succès |
| 400 | Requête invalide |
| 401 | Non authentifié |
| 403 | Non autorisé |
| 404 | Non trouvé |
| 422 | Erreur de validation |
| 500 | Erreur serveur |

---

## Exemples d'utilisation

### Récupérer le menu header pour le frontend
```bash
curl -s "http://api.local/api/cms/menus/location/header"
```

### Créer une page complète
```bash
curl -X POST "http://api.local/api/superadmin/cms/pages" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Nouvelle page",
    "slug": "nouvelle-page",
    "content": "<h1>Titre</h1><p>Contenu de la page</p>",
    "template": "default",
    "meta_title": "Nouvelle page - Mon Site",
    "meta_description": "Description SEO de la nouvelle page",
    "status": "published",
    "is_active": true
  }'
```

### Récupérer plusieurs blocs en une requête
```bash
curl "http://api.local/api/cms/blocks/multiple?identifiers[]=hero-home&identifiers[]=footer-cta"
```

---

## Notes importantes

1. **Soft Delete:** Les pages, blocs, menus et médias utilisent le soft delete. Ils peuvent être restaurés après suppression.

2. **Statuts des pages:**
   - `draft` - Brouillon (non visible sur le frontend)
   - `published` - Publié (visible sur le frontend)
   - `archived` - Archivé (non visible)

3. **Paramètres publics:** Seuls les paramètres avec `is_public: true` sont accessibles via l'API frontend.

4. **Liens de menu:** Les éléments de menu peuvent être liés à:
   - Une URL directe
   - Une page CMS (l'URL est générée automatiquement)
   - Une route Laravel nommée

5. **Structure hiérarchique:**
   - Les pages peuvent avoir des enfants (parent_id)
   - Les éléments de menu peuvent être imbriqués (parent_id)
