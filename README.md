# Persomy — site vitrine (PHP natif, CyberPanel)

Site vitrine premium multilingue (FR / EN / AR en RTL) avec portfolio, formulaire de contact et administration.
PHP 8.2+, MySQL/MariaDB, PDO, architecture MVC légère, aucune dépendance Node.js ni Composer en production.

Le développement s'est fait **sans serveur local** : le code est vérifié par `php -l` et `php tests/run.php` (logique pure, sans base).
Le premier vrai test se fait sur le VPS (voir la checklist de recette).

## Contenu du dépôt

```
app/            Controllers, Models, Services, Middleware, Core, Languages (fr/en/ar), Views
bin/            Scripts SSH : migrate, create-admin, check, purge-submissions
config/         app.php (lit .env)
database/       migrations/ (schéma), seeders/ (contenu initial FR/EN/AR), demo/ (projets fictifs, optionnel)
public/         Seule racine web : index.php, assets/, uploads/
routes/         web.php
storage/        logs, sessions, cache (jamais publics)
tests/          run.php (tests essentiels sans base)
.env.example    Modèle de configuration (aucun secret)
.htaccess       Repli si le document root ne peut pas pointer vers public/
```

## Prérequis serveur

- PHP **8.2 ou plus** (vérifier : `php -v`), extensions : `pdo_mysql`, `mbstring`, `fileinfo`, `openssl` (requises) ; `intl`, `gd` (recommandées).
- MySQL 5.7+/8 ou MariaDB 10.4+, encodage `utf8mb4`.
- OpenLiteSpeed fourni par CyberPanel, HTTPS actif sur le domaine.

Aucune supposition n'est faite sur SMTP, cron ou les extensions : `php bin/check.php` vérifie tout.

## Déploiement CyberPanel (pas à pas)

### 1. Créer le site et la base
1. CyberPanel > **Websites > Create Website** : domaine, version PHP 8.2+ (ou plus), cocher SSL si proposé.
2. **Databases > Create Database** : nom, utilisateur, mot de passe fort. Notez-les pour le `.env`.
3. **SSL > Issue SSL** pour le domaine (Let's Encrypt), puis forcez la redirection HTTP vers HTTPS (vHost Conf ou règle de réécriture).

### 2. Envoyer le code
Par SSH (recommandé) depuis le dossier du site, par exemple `/home/votre-domaine.com/` :

```bash
cd /home/votre-domaine.com
git clone <url-du-depot> persomy      # ou envoi SFTP du dossier
```

La structure finale attendue : `/home/votre-domaine.com/persomy/` (code) avec `public/` comme racine web.

### 3. Document root vers `public/` (configuration recommandée)
CyberPanel > **Websites > List Websites > Manage > vHost Conf** (ou *Websites > Modify Website*) et définir :

```
docRoot   /home/votre-domaine.com/persomy/public
```

Redémarrez OpenLiteSpeed si nécessaire. Ainsi `.env`, `app/`, `storage/`, `database/` ne sont **jamais** accessibles par le web.

#### Réécriture d'URL (OpenLiteSpeed)
Le fichier `public/.htaccess` est lu par OpenLiteSpeed si l'option « Auto Load from .htaccess » est active. Sinon, dans **vHost Conf > Rewrite** :

```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [L]
```

(Activer « Enable Rewrite » dans le contexte.)

#### Repli sécurisé si le document root ne peut pas pointer vers `public/`
Placez **le contenu du dépôt** directement dans le document root du domaine (ex. `public_html/`). Le `.htaccess` à la racine redirige tout vers `public/` et ne sert jamais `.env`, `app/`, `storage/`, etc.
Vérifiez impérativement (voir checklist) que ces URL renvoient 403/404 : `/.env`, `/app/bootstrap.php`, `/storage/logs/`, `/database/migrations/001_create_schema.sql`, `/bin/check.php`.
Si l'un d'eux répond 200, **n'ouvrez pas le site** : ajoutez des contextes d'interdiction dans vHost Conf ou changez le document root.

### 4. Configuration (`.env`)
```bash
cd /home/votre-domaine.com/persomy
cp .env.example .env
nano .env
```
- `APP_URL=https://votre-domaine.com`, `APP_ENV=production`, `APP_DEBUG=false`
- `APP_KEY` : `php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"`
- `DB_*` : valeurs de l'étape 1
- `SMTP_*`, `MAIL_FROM_ADDRESS`, `MAIL_TO` (voir ci-dessous)

Protégez le fichier : `chmod 640 .env`. Il n'est jamais versionné (`.gitignore`).

### 5. Droits des fichiers
L'utilisateur du site (celui du vhost) doit pouvoir écrire dans :
```bash
chmod -R u+rwX,g+rX,o-rwx storage
chmod -R u+rwX public/uploads/projects public/uploads/blog
```
Les téléversements ne sont jamais exécutés (`public/uploads/.htaccess` ; vérifier aussi la checklist).

### 6. Installer la base
```bash
php bin/migrate.php --seed          # schéma + contenu initial FR/EN/AR
php bin/create-admin.php vous@exemple.com "Votre Nom"   # mot de passe demandé (non affiché, 12 car. min.)
php bin/check.php                   # diagnostic complet
```
- `--demo` (facultatif) ajoute 3 projets **fictifs** (badge « démonstration ») pour voir le rendu ; à supprimer avant la mise en ligne (Administration > Projets).
- Alternative sans SSH : importez `database/migrations/001_create_schema.sql` puis `database/seeders/001_base_content.sql` dans phpMyAdmin, puis créez l'admin avec `bin/create-admin.php` (SSH requis pour ce script).

### 7. SMTP
Renseignez `SMTP_HOST`, `SMTP_PORT` (587 + `tls`, ou 465 + `ssl`), `SMTP_USERNAME`, `SMTP_PASSWORD`, `MAIL_FROM_ADDRESS`.
Test : `php bin/check.php --smtp-test=adresse@exemple.com`.
Si le port sortant est bloqué par l'hébergeur, le test échoue clairement et le formulaire fonctionne quand même en mode « enregistré sans e-mail » (message honnête au visiteur et statut visible en administration).
Le destinataire des demandes se règle dans **Administration > Réglages** (à défaut `MAIL_TO`).

### 8. Cron (optionnel)
Aucun cron n'est nécessaire. Si disponible, la purge des demandes expirées peut être planifiée :
`0 3 * * * php /home/votre-domaine.com/persomy/bin/purge-submissions.php`.
Sinon : bouton « Purger » dans Administration > Messages.

## Administration

`https://votre-domaine.com/admin` : projets (publication / brouillon, image, traductions FR/EN/AR), catégories, **blog** (articles, catégories du blog), services, textes des pages, réglages (nom, coordonnées, destinataire, langue par défaut, conservation), messages reçus (statuts, suppression, purge), changement de mot de passe.

### Blog
Administration > **Blog** : articles multilingues (titre, chapeau, contenu, SEO, image de couverture), brouillon / publié, **publication programmée** (date future, statut « Publié »), catégories. Public : `/{langue}/blog` (filtre par catégorie, pagination), `/{langue}/blog/{slug}`, flux RSS `/{langue}/blog/rss.xml`, entrées du sitemap, données structurées `BlogPosting`. Mise en forme du contenu : `## Titre`, `### Sous-titre`, `- liste`, `**gras**`, `[texte](https://…)` ou `[texte](/fr/contact)` (tout le reste est échappé, aucun HTML brut). Après mise à jour : `php bin/migrate.php --seed` (applique `002_create_blog.sql` et la page « Blog »), puis `chown` sur `public/uploads/blog` pour l'utilisateur du site.

Les contenus visibles (coordonnées, textes, services…) sont en base et modifiables sans toucher au code. Le `.env` ne contient que l'infrastructure et les secrets.

### SEO et GEO (moteurs de réponse IA)
Fichiers générés automatiquement (aucun fichier statique à maintenir, à partir de `APP_URL` et de la base) :
- `/robots.txt` : `/admin` interdit, lien vers le sitemap et `llms.txt`. La politique des robots IA (GPTBot, ClaudeBot, OAI-SearchBot, ChatGPT-User, PerplexityBot, Google-Extended…) se règle dans **Administration > Réglages > Robots IA** : autoriser tout (défaut), assistants seulement (entraînement bloqué) ou tout bloquer.
- `/sitemap.xml` : pages, projets, articles, avec `hreflang` FR/EN/AR et `x-default`.
- `/llms.txt` (résumé et liens) et `/llms-full.txt` (contenu complet en Markdown) : langue par défaut ; les contenus « à compléter » et les projets de démonstration en sont exclus.
- `/site.webmanifest` (statique) et `/{langue}/blog/rss.xml`.
- Dans chaque page : `canonical`, `hreflang`, Open Graph, Twitter Card, `robots` (`max-image-preview:large`), et données structurées JSON-LD (`Organization` + `WebSite` sur l'accueil, `BreadcrumbList` partout, `Service` sur Services, `BlogPosting` sur les articles).

Après mise en ligne : soumettre `https://votre-domaine.com/sitemap.xml` dans Google Search Console et Bing Webmaster Tools.

### Marqueurs « à compléter »
Les informations officielles manquantes (e-mail, adresses Paris/Tunis, mission, valeurs, mentions légales, confidentialité) sont initialisées avec `[À COMPLÉTER …]`, `[TO BE COMPLETED …]`, `[يُستكمل لاحقًا …]`.
Les coordonnées contenant un marqueur ne sont pas affichées publiquement ; les pages en contiennent encore tant qu'elles ne sont pas rédigées : le tableau de bord et la liste des pages les signalent. **Ne mettez pas en ligne avec des marqueurs restants** (en particulier mentions légales et confidentialité).

### Logo
Déposez le logo officiel dans `public/assets/img/logo.webp` (ou `logo.svg` / `logo.png`) : il remplace automatiquement le texte provisoire. Le logo doit être **sombre sur fond transparent** (l'en-tête et le pied de page sont clairs) ; `logo-light.webp` est la variante blanche pour fonds sombres. Favicons : `favicon-32.png`, `icon-192.png`, `icon-512.png`, `apple-touch-icon.png`.

## Langues et RTL

- URLs `/fr/`, `/en/`, `/ar/` ; la racine `/` redirige vers la langue par défaut (réglage admin).
- L'arabe utilise `lang="ar" dir="rtl"` sur toute la page (navigation, formulaires, alignements via propriétés CSS logiques).
- Une traduction absente n'est jamais présentée comme complète : le contenu s'affiche dans la langue par défaut (puis une autre langue), avec un bandeau explicite et des attributs `lang`/`dir` corrects sur le bloc concerné. Une page sans aucune traduction renvoie 404.
- Interface (menus, libellés) : `app/Languages/*.php` (un test vérifie la parité des clés).

## Sécurité (résumé)

PDO + requêtes préparées ; échappement HTML systématique (`e()`), contenus administrables en texte simple (aucun HTML brut) ; mots de passe `password_hash` ; CSRF sur tous les POST ; sessions `HttpOnly`/`SameSite`/`Secure` (HTTPS), ID régénéré à la connexion ; limitation des tentatives de connexion (IP et compte) et du formulaire de contact (en base, sans cron) ; champ piège + délai minimal anti-spam ; uploads : MIME réel, dimensions, taille, nom aléatoire, exécution interdite ; CSP stricte (aucun script/style en ligne), HSTS, `X-Frame-Options`, etc. ; erreurs centralisées, journaux dans `storage/logs` sans secrets ni contenu de messages, debug impossible en production ; IP stockée uniquement sous forme d'empreinte HMAC.

`TRUST_PROXY_HEADERS` reste `false` sauf derrière un proxy/CDN de confiance.

## Mise à jour, sauvegarde, restauration

```bash
# Sauvegarde (avant toute mise à jour)
mysqldump -u USER -p NOM_BASE > ~/backup-$(date +%F).sql      # hors du dossier public
tar czf ~/uploads-$(date +%F).tgz public/uploads/projects

# Mise à jour
git pull
php bin/migrate.php          # applique les nouvelles migrations seulement
php bin/check.php

# Restauration
mysql -u USER -p NOM_BASE < ~/backup-YYYY-MM-DD.sql
tar xzf ~/uploads-YYYY-MM-DD.tgz
```
Ne stockez jamais les sauvegardes dans `public/`.

## Dépannage

| Symptôme | Piste |
|---|---|
| Page blanche / erreur 500 | `storage/logs/app-AAAA-MM-JJ.log` et `storage/logs/php-error.log` ; `php bin/check.php` |
| 404 sur toutes les pages sauf `/` | Réécriture non active (voir étape 3) |
| « Service momentanément indisponible » au login | Base inaccessible ou migrations non appliquées |
| Demande enregistrée mais pas d'e-mail | `php bin/check.php --smtp-test=...`, port sortant bloqué, identifiants SMTP |
| Impossible de téléverser une image | Droits sur `public/uploads/projects`, `upload_max_filesize`/`post_max_size` PHP ≥ 5 Mo |
| Déconnexion immédiate | `storage/sessions` non inscriptible ; HTTPS mal détecté derrière un proxy (`TRUST_PROXY_HEADERS`) |

## Tests

```bash
php tests/run.php        # 51 tests de logique pure : routeur, échappement, e-mails, langues, SQL, CSP...
```
Les tests nécessitant la base et le serveur web (parcours complet) sont décrits dans la checklist ci-dessous et se font sur le VPS.

## Checklist de recette (à faire sur le VPS, préproduction d'abord)

**Installation**
- [ ] `php bin/check.php` sans point bloquant
- [ ] `/.env`, `/app/bootstrap.php`, `/storage/`, `/database/`, `/bin/check.php`, `/public/index.php` (via `/public/`) : inaccessibles (403/404)
- [ ] `/uploads/projects/` : pas de listage ; un fichier `.php` déposé dans uploads n'est pas exécuté

**Public**
- [ ] `/`, `/fr/`, `/en/`, `/ar/` répondent ; l'arabe est en RTL (menu, formulaire, alignements)
- [ ] Pages Accueil, À propos, Services, Portfolio, Contact lisibles sur mobile, tablette et ordinateur
- [ ] Projet publié visible ; brouillon introuvable (404) ; filtre par catégorie OK
- [ ] Projet sans traduction arabe : bandeau de repli visible sur `/ar/portfolio/...`
- [ ] 404 propre dans chaque langue ; aucune trace d'erreur PHP/SQL visible
- [ ] `/sitemap.xml`, `/robots.txt` et `/llms.txt` corrects (URL en https), balises canonical/hreflang présentes, JSON-LD valide (validator.schema.org)

**Contact**
- [ ] Champs invalides refusés avec messages clairs ; valeurs conservées
- [ ] Soumission sans jeton CSRF : refusée (419)
- [ ] 6 envois consécutifs : le 6e est refusé (429)
- [ ] SMTP valide : message reçu, confirmation « envoyé »
- [ ] SMTP coupé : confirmation « enregistré, e-mail non envoyé » ; jamais « envoyé »
- [ ] Demande visible en administration avec son statut de notification

**Administration**
- [ ] `/admin` redirige vers la connexion si non connecté ; toutes les routes `/admin/...` protégées
- [ ] 5 mauvais mots de passe : blocage temporaire
- [ ] Création / modification / publication / dépublication / suppression (avec confirmation) d'un projet
- [ ] Upload : refus d'un `.php` renommé en `.jpg`, d'un fichier trop lourd ; image valide acceptée
- [ ] Modification des textes de pages et des réglages sans toucher au code
- [ ] Aucun marqueur `[À COMPLÉTER …]` restant ; projets de démonstration supprimés

## Informations à fournir avant la mise en production

Domaine et configuration CyberPanel (version PHP, accès SSH/FTP) ; logo et polices ; e-mails officiels, serveur SMTP et destinataire ; coordonnées vérifiées Paris et Tunis ; projets réellement publiables avec visuels et autorisations ; mentions légales, politique de confidentialité, cookies (le site n'utilise qu'un cookie de session technique) et responsable du traitement.
