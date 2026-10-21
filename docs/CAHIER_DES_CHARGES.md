# Persomy — Cahier des charges (analyse, spécifications, migration)

Complète [README.md](../README.md) (déploiement CyberPanel, sauvegarde, recette, informations à fournir) et [COPILOT_INSTRUCTIONS.md](../COPILOT_INSTRUCTIONS.md) (règles de développement).

## 1. Analyse du site actuel

Date de l'analyse : 21 octobre 2026. Source : https://persomy.com (HTML rendu, lecture uniquement).

### 1.1 Faits vérifiés

| Élément | Constat |
|---|---|
| Langues | `/fr`, `/en`, `/ar` accessibles ; contenu accueil traduit dans les 3 langues. |
| Navigation | Accueil, À propos (`/about`), Services (`/services`), Portfolio (`/portfolio`), Contact (`/contact`). Lien d'évitement « Aller au contenu principal » présent. |
| Pied de page | Bureaux Paris · Tunis, LinkedIn, Mentions légales (`/legal`), Confidentialité (`/privacy`). |
| Positionnement | « Des solutions numériques conçues pour vos besoins » — Digital Solutions. |
| Expertises | Logiciels sur mesure ; plateformes SaaS/PaaS ; intelligence artificielle ; automatisation ; conseil et intégration. |
| Méthode | 4 étapes : écoute et cadrage, conception, réalisation, déploiement et suivi. |
| Portfolio | Vide : « Aucun projet publié pour le moment ». |
| Mentions légales / confidentialité | Pages accessibles en FR, EN et AR, mais encore des marqueurs `[À COMPLÉTER]` / `[TO BE COMPLETED]` / `[يُستكمل لاحقًا]` : éditeur, forme juridique, adresse, responsable de publication, hébergeur, responsable du traitement, finalités, durée de conservation, droits, cookies. **Non publiables en l'état.** |
| Contact | Formulaire : nom*, e-mail*, entreprise, sujet/service, message*, consentement*. Coordonnées « publiées prochainement ». |
| À propos | Mission, valeurs et éléments de confiance : marqueurs `[À COMPLÉTER]`. |
| robots.txt | `Disallow: /admin` + déclaration du sitemap. |
| Sitemap | 18 URL (6 pages × 3 langues). **À l'analyse, les URL pointaient vers `www.example.com`** : `APP_URL` du `.env` à corriger. |

### 1.2 Observations (non vérifiées techniquement)

- Le fonctionnement réel du formulaire (envoi SMTP, enregistrement) n'a pas été testé : à valider en recette.
- Couleurs, polices et composants : définis dans [public/assets/css/app.css](../public/assets/css/app.css) (palette bleu / noir / blanc). Pas de logo définitif.
- L'historique d'indexation (Search Console, anciennes URL éventuelles) n'a pas pu être consulté : les URL ci-dessous sont celles du site actuel. Si un ancien site existait, ses URL sont à fournir pour prévoir des redirections 301.
- Non inspectés : pages de fiche projet (aucun projet publié), pages EN/AR de `about`, `services`, `portfolio`, `contact`, code source (balises meta, canonical, hreflang, données structurées).

### 1.3 Hypothèses à confirmer par le propriétaire

- Logo, coordonnées publiques, textes légaux, mission et valeurs.
- Existence de projets réels publiables, avec autorisation écrite des clients.

## 2. Conserver / corriger / améliorer / créer

| Action | Éléments |
|---|---|
| Conserver | URL `/{fr,en,ar}/…`, textes d'expertises et de méthode, présence Paris/Tunis, lien LinkedIn, structure de navigation. |
| Corriger | `APP_URL` (sitemap, canonical, hreflang) ; marqueurs `[À COMPLÉTER]` ; coordonnées. |
| Améliorer | Design premium, accessibilité, RTL, performances, SEO par page et par langue. |
| Créer | Administration, portfolio administrable avec fiches projet, gestion des messages, réglages SMTP en base. |

## 3. Cahier des charges fonctionnel

- **Public** : accueil, à propos, services, portfolio (filtres par catégorie) + fiche projet, contact, mentions légales, confidentialité, 404/erreurs propres.
- **Contact** : validation serveur, CSRF, honeypot, limitation de débit en base, enregistrement avant envoi, statut d'envoi (`sent`/`failed`/`skipped`). Aucun message de succès d'envoi e-mail si le SMTP échoue.
- **Admin** : connexion, projets (brouillon/publié, mis en avant, démo), catégories, services, pages, réglages, messages (statuts, suppression, purge), compte.
- **Multilingue** : FR/EN/AR, RTL pour l'arabe, repli explicite si traduction manquante, aucune traduction automatique présentée comme validée.
- **Contenu non vérifié** : jamais de client, chiffre, témoignage ou partenaire inventé ; les projets de démonstration sont marqués `is_demo`.

## 4. Cahier des charges technique

- PHP 8.2+, PDO (requêtes préparées), MySQL/MariaDB utf8mb4, aucune dépendance Composer ni Node en production.
- Configuration par `.env` (secrets et infrastructure uniquement). Contenus visibles et réglages fonctionnels en base, modifiables dans l'admin.
- Sécurité : CSP stricte sans script/style inline, sessions durcies, mots de passe `password_hash`, limitation des tentatives de connexion, upload contrôlé (finfo + getimagesize), journaux hors web.
- SEO : title/description par page et langue, canonical, hreflang, sitemap dynamique, robots.txt, données structurées.

## 5. Architecture

```text
public/            point d'entrée (index.php), assets, uploads
app/Core/          noyau (Router, Db, Request, Response, Session, Csrf, Lang, View, Auth…)
app/Models/        accès aux données (PDO)
app/Controllers/   public + Admin/
app/Services/      Mailer (SMTP), RateLimiter, Uploader, SqlSplitter
app/Middleware/    Auth, Csrf
app/Languages/     fr, en, ar
app/Views/         layouts, partials, public, admin, erreurs
config/ routes/ database/{migrations,seeders,demo} bin/ storage/ tests/
```

Routes : [routes/web.php](../routes/web.php).

## 6. Schéma de base de données

Source : [database/migrations/001_create_schema.sql](../database/migrations/001_create_schema.sql).

| Table | Rôle |
|---|---|
| `migrations` | Suivi des migrations appliquées. |
| `admins` | Comptes admin (hash du mot de passe, rôle, actif). |
| `project_categories` / `project_category_translations` | Catégories et leurs traductions. |
| `projects` / `project_translations` | Projets (statut, démo, mis en avant, image) et textes par langue (résumé, contexte, solution, résultats). |
| `pages` / `page_translations` | Pages éditables, meta title/description, intro, contenu. |
| `services` / `service_translations` | Expertises. |
| `site_settings` | Réglages clé/valeur (contact, SMTP, réseaux). |
| `contact_submissions` | Demandes (statut, état d'envoi, IP hachée, consentement). |
| `rate_limits` | Limitation de débit (formulaire, connexion). |

## 7. Plan de migration des URL et contenus

| URL | Décision |
|---|---|
| `/fr`, `/en`, `/ar` | Conservées. `/` redirige vers la langue détectée ou par défaut. |
| `/{lang}/about`, `/services`, `/portfolio`, `/contact`, `/legal`, `/privacy` | Conservées à l'identique : aucune redirection 301 nécessaire. |
| `/{lang}/portfolio/{slug}` | Nouvelles URL (fiches projet). |
| `/admin/*` | Nouvelles, exclues de l'indexation (robots + en-têtes). |
| `/sitemap.xml`, `/robots.txt` | Conservées ; sitemap à régénérer avec `APP_URL=https://persomy.com`. |

Contenus : les textes d'expertises et de méthode actuels ont été repris tels quels ; les marqueurs `[À COMPLÉTER]` sont à remplir via l'admin.

Après correction d'`APP_URL` : vérifier `/sitemap.xml`, soumettre le sitemap dans la Search Console.

## 8. Plan de tests

- Automatisés : `php tests/run.php` (logique pure, 51 tests).
- Recette sur le VPS : checklist dans [README.md](../README.md#checklist-de-recette-à-faire-sur-le-vps-préproduction-dabord).
- Contrôles ciblés : sitemap/canonical/hreflang, accès refusé à `/.env`, `/app/`, `/storage/`, `/database/`, `/bin/`, formulaire de contact avec SMTP réel et en échec, upload invalide, limitation de connexion, RTL arabe, navigation clavier.

## 9. Informations à fournir par le propriétaire

Voir [README.md](../README.md#informations-à-fournir-avant-la-mise-en-production). En synthèse : logo, coordonnées publiques, mission/valeurs/éléments de confiance vérifiables, textes légaux (éditeur, hébergeur, RGPD), identifiants SMTP, projets réels autorisés avec visuels et traductions validées.

## 10. Prompt de démarrage pour GitHub Copilot

> Analyse d'abord le dépôt : lis `README.md`, `COPILOT_INSTRUCTIONS.md`, `docs/CAHIER_DES_CHARGES.md`, `routes/web.php` et `database/migrations/`. Résume l'architecture et les conventions avant d'écrire du code. Ne modifie pas la sécurité (CSRF, CSP, PDO préparé, uploads) sans l'expliquer. Respecte : PHP natif sans dépendance, aucun secret dans le dépôt, contenus visibles en base via l'admin, aucune donnée inventée, FR/EN/AR avec RTL. Pour chaque changement : propose un plan, implémente par petites étapes, ajoute ou adapte les tests de `tests/run.php`, puis liste les fichiers modifiés.
