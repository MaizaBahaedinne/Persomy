-- Persomy : page d'index du blog (titre, introduction et métadonnées modifiables dans Admin > Pages).
-- Idempotent : INSERT IGNORE ne remplace jamais une modification faite en administration.
-- Aucun article n'est créé ici : les contenus du blog sont rédigés par Persomy.

INSERT IGNORE INTO pages (slug) VALUES ('blog');

INSERT IGNORE INTO page_translations (page_id, locale, title, meta_title, meta_description, intro, content) VALUES
((SELECT id FROM pages WHERE slug = 'blog'), 'fr',
 'Blog',
 'Blog — Persomy',
 'Articles de Persomy sur les logiciels sur mesure, les plateformes SaaS/PaaS, l’intelligence artificielle et l’automatisation.',
 'Articles sur les logiciels sur mesure, les plateformes SaaS/PaaS, l’intelligence artificielle et l’automatisation.',
 ''),
((SELECT id FROM pages WHERE slug = 'blog'), 'en',
 'Blog',
 'Blog — Persomy',
 'Articles from Persomy on custom software, SaaS/PaaS platforms, artificial intelligence and automation.',
 'Articles on custom software, SaaS/PaaS platforms, artificial intelligence and automation.',
 ''),
((SELECT id FROM pages WHERE slug = 'blog'), 'ar',
 'المدوّنة',
 'المدوّنة — Persomy',
 'مقالات من Persomy حول البرمجيات المخصصة ومنصات SaaS/PaaS والذكاء الاصطناعي والأتمتة.',
 'مقالات حول البرمجيات المخصصة ومنصات SaaS/PaaS والذكاء الاصطناعي والأتمتة.',
 '');
