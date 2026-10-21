# Instructions de développement (GitHub Copilot)

Référence : `Cahier_des_charges_Persomy_CyberPanel_Copilot.pdf`. Lire aussi `README.md`.

## Règles générales
- PHP 8.2+ natif, `declare(strict_types=1)`, PDO + requêtes préparées. Aucun framework lourd, aucune dépendance Node/Composer obligatoire en production.
- Pas de serveur local imposé : valider avec `php -l` et `php tests/run.php`. Ne pas lancer de serveur web ou de base sur la machine du développeur sans demande explicite.
- Une étape à la fois, petites modifications cohérentes ; relancer les tests ; mettre le README à jour.
- Toute fonctionnalité hors cahier des charges doit être discutée avant ajout.

## Architecture
- `public/` est la seule racine web. Code, `.env`, `storage/`, `database/`, `bin/` restent hors de portée du web.
- MVC léger : `app/Core` (noyau), `app/Models` (accès base), `app/Controllers` (+ `Admin/`), `app/Views`, `app/Services`, `routes/web.php`.
- Contenus visibles et réglages fonctionnels : en base, modifiables via l'administration (jamais dans `.env`). `.env` : infrastructure et secrets uniquement.

## Sécurité (obligatoire)
- Jamais de valeur utilisateur concaténée dans le SQL (les noms de tables/colonnes viennent uniquement de constantes).
- Toute sortie HTML passe par `e()` ; contenus administrables rendus par `format_content()` (texte simple, pas de HTML brut).
- CSRF sur tout POST (`CsrfMiddleware`), contrôle d'accès serveur sur toute route `/admin` (`AuthMiddleware`).
- CSP stricte : pas de `<script>` ni `style=` ni `onclick=` en ligne (un test le vérifie). Le JS/CSS vont dans `public/assets/`.
- Uploads via `Uploader` uniquement (MIME réel, taille, dimensions, nom généré, extension fixe).
- Redirections uniquement vers des chemins internes (`Response::redirect`).
- Journaux sans secret ni contenu de message. Aucun secret dans Git, le chat ou les journaux.

## Contenu et langues
- FR / EN / AR (RTL) obligatoires. Toute nouvelle chaîne d'interface est ajoutée aux trois fichiers de `app/Languages/` (le test de parité échoue sinon).
- Une traduction manquante est signalée (repli explicite via `Translations::pick`), jamais présentée comme complète.
- Ne jamais inventer de clients, chiffres, témoignages, adresses, coordonnées ou projets réels. Données de démonstration : `is_demo = 1`, texte explicitement fictif. Informations officielles manquantes : marqueur `[À COMPLÉTER …]` / `[TO BE COMPLETED …]` / `[يُستكمل لاحقًا …]` (non affiché publiquement pour les coordonnées).
- Logo : emplacement temporaire remplaçable (`public/assets/img/logo.webp|svg|png`) ; ne pas inventer de logo officiel.

## Formulaire de contact
- Ne jamais annoncer un envoi qui a échoué : trois issues honnêtes (`sent`, `saved`, `saved_mail_failed`) et une erreur si rien n'a pu être enregistré ni envoyé.

## Style
- Propriétés CSS logiques (`inline-start`, `margin-inline`…) pour le RTL ; contraste accessible ; animations légères et désactivées avec `prefers-reduced-motion`.
- Palette : bleu, noir, blanc. HTML sémantique, labels de formulaire, navigation clavier, textes alternatifs.
