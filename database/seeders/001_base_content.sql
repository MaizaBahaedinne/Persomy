-- Persomy : contenu initial (idempotent : INSERT IGNORE, ne remplace jamais une modification faite en administration).
-- Les textes marqués [À COMPLÉTER …] / [TO BE COMPLETED …] / [يُستكمل لاحقًا …] attendent des informations officielles.

-- ---------- Réglages ----------
INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES
  ('site_name', 'Persomy'),
  ('default_locale', 'fr'),
  ('contact_email', '[À COMPLÉTER : e-mail officiel de contact]'),
  ('contact_phone', ''),
  ('contact_recipient', '[À COMPLÉTER : e-mail destinataire des demandes]'),
  ('address_paris', '[À COMPLÉTER : adresse du bureau de Paris]'),
  ('address_tunis', '[À COMPLÉTER : adresse du bureau de Tunis]'),
  ('linkedin_url', ''),
  ('contact_retention_days', '365');

-- ---------- Pages ----------
INSERT IGNORE INTO pages (slug) VALUES ('home'), ('about'), ('services'), ('portfolio'), ('contact'), ('legal'), ('privacy');

-- Accueil
INSERT IGNORE INTO page_translations (page_id, locale, title, meta_title, meta_description, intro, content) VALUES
((SELECT id FROM pages WHERE slug = 'home'), 'fr',
 'Des solutions numériques conçues pour vos besoins',
 'Persomy — Solutions numériques sur mesure',
 'Persomy conçoit des logiciels sur mesure, des plateformes SaaS/PaaS, des solutions d’intelligence artificielle et des automatisations pour les organisations et les professionnels. Bureaux à Paris et Tunis.',
 'Persomy conçoit des solutions numériques adaptées aux besoins des organisations et des professionnels : logiciels sur mesure, plateformes SaaS/PaaS, intelligence artificielle et automatisation.',
 ''),
((SELECT id FROM pages WHERE slug = 'home'), 'en',
 'Digital solutions built around your needs',
 'Persomy — Custom digital solutions',
 'Persomy designs custom software, SaaS/PaaS platforms, artificial intelligence solutions and automation for organisations and professionals. Offices in Paris and Tunis.',
 'Persomy designs digital solutions tailored to the needs of organisations and professionals: custom software, SaaS/PaaS platforms, artificial intelligence and automation.',
 ''),
((SELECT id FROM pages WHERE slug = 'home'), 'ar',
 'حلول رقمية مصممة وفق احتياجاتك',
 'Persomy — حلول رقمية مخصصة',
 'تصمّم Persomy برمجيات مخصصة ومنصات SaaS/PaaS وحلول ذكاء اصطناعي وأتمتة للمؤسسات والمهنيين. مكاتب في باريس وتونس.',
 'تصمّم Persomy حلولاً رقمية تلائم احتياجات المؤسسات والمهنيين: برمجيات مخصصة، ومنصات SaaS/PaaS، وذكاء اصطناعي، وأتمتة.',
 '');

-- À propos
INSERT IGNORE INTO page_translations (page_id, locale, title, meta_title, meta_description, intro, content) VALUES
((SELECT id FROM pages WHERE slug = 'about'), 'fr',
 'À propos',
 'À propos de Persomy',
 'Découvrez Persomy (Digital Solutions) : entreprise technologique présente à Paris et à Tunis, spécialisée dans les solutions numériques sur mesure.',
 'Persomy (Digital Solutions) est une entreprise technologique qui conçoit des solutions numériques adaptées aux besoins des organisations et des professionnels.',
 'Nous développons des logiciels sur mesure, des plateformes SaaS/PaaS, des solutions d’intelligence artificielle et des automatisations, et nous travaillons avec nos clients et partenaires pour les mettre en œuvre.

## Notre mission

[À COMPLÉTER : formulation officielle de la mission de Persomy]

## Nos valeurs

[À COMPLÉTER : valeurs officielles de Persomy]

## Où nous trouver

Persomy dispose de bureaux à Paris et à Tunis.

## Éléments de confiance

[À COMPLÉTER : références, certifications ou partenariats vérifiables que Persomy souhaite publier]'),
((SELECT id FROM pages WHERE slug = 'about'), 'en',
 'About',
 'About Persomy',
 'Discover Persomy (Digital Solutions): a technology company based in Paris and Tunis, specialised in custom digital solutions.',
 'Persomy (Digital Solutions) is a technology company that designs digital solutions tailored to the needs of organisations and professionals.',
 'We build custom software, SaaS/PaaS platforms, artificial intelligence solutions and automation, and we work with our clients and partners to put them into practice.

## Our mission

[TO BE COMPLETED: official mission statement of Persomy]

## Our values

[TO BE COMPLETED: official values of Persomy]

## Where to find us

Persomy has offices in Paris and Tunis.

## Trust elements

[TO BE COMPLETED: verifiable references, certifications or partnerships Persomy wishes to publish]'),
((SELECT id FROM pages WHERE slug = 'about'), 'ar',
 'من نحن',
 'من نحن — Persomy',
 'تعرّف على Persomy (Digital Solutions): شركة تقنية حاضرة في باريس وتونس ومتخصصة في الحلول الرقمية المخصصة.',
 'Persomy (Digital Solutions) شركة تقنية تصمّم حلولاً رقمية تلائم احتياجات المؤسسات والمهنيين.',
 'نطوّر برمجيات مخصصة ومنصات SaaS/PaaS وحلول ذكاء اصطناعي وأتمتة، ونعمل مع عملائنا وشركائنا على تطبيقها.

## مهمتنا

[يُستكمل لاحقًا: الصياغة الرسمية لمهمة Persomy]

## قيمنا

[يُستكمل لاحقًا: القيم الرسمية لـ Persomy]

## أين نوجد

لدى Persomy مكاتب في باريس وتونس.

## عناصر الثقة

[يُستكمل لاحقًا: مراجع أو شهادات أو شراكات قابلة للتحقق ترغب Persomy في نشرها]');

-- Services (introduction)
INSERT IGNORE INTO page_translations (page_id, locale, title, meta_title, meta_description, intro, content) VALUES
((SELECT id FROM pages WHERE slug = 'services'), 'fr', 'Services', 'Services — logiciels, SaaS/PaaS, IA et automatisation',
 'Logiciels sur mesure, plateformes SaaS/PaaS, intelligence artificielle, automatisation, conseil et intégration : découvrez les services de Persomy.',
 'Du cadrage à la mise en production, nous concevons des solutions numériques adaptées à votre activité.', ''),
((SELECT id FROM pages WHERE slug = 'services'), 'en', 'Services', 'Services — software, SaaS/PaaS, AI and automation',
 'Custom software, SaaS/PaaS platforms, artificial intelligence, automation, consulting and integration: discover Persomy’s services.',
 'From scoping to production, we design digital solutions tailored to your business.', ''),
((SELECT id FROM pages WHERE slug = 'services'), 'ar', 'الخدمات', 'الخدمات — برمجيات وSaaS/PaaS وذكاء اصطناعي وأتمتة',
 'برمجيات مخصصة ومنصات SaaS/PaaS وذكاء اصطناعي وأتمتة واستشارات وتكامل: اكتشف خدمات Persomy.',
 'من التحديد إلى الإنتاج، نصمّم حلولاً رقمية تلائم نشاطك.', '');

-- Portfolio (introduction)
INSERT IGNORE INTO page_translations (page_id, locale, title, meta_title, meta_description, intro, content) VALUES
((SELECT id FROM pages WHERE slug = 'portfolio'), 'fr', 'Portfolio', 'Portfolio — réalisations Persomy',
 'Une sélection de projets et réalisations Persomy présentés avec l’accord des parties concernées.',
 'Une sélection de réalisations que nous sommes autorisés à présenter.', ''),
((SELECT id FROM pages WHERE slug = 'portfolio'), 'en', 'Portfolio', 'Portfolio — Persomy projects',
 'A selection of Persomy projects presented with the consent of the parties involved.',
 'A selection of work we are authorised to showcase.', ''),
((SELECT id FROM pages WHERE slug = 'portfolio'), 'ar', 'أعمالنا', 'أعمالنا — مشاريع Persomy',
 'مجموعة من مشاريع Persomy المعروضة بموافقة الأطراف المعنية.',
 'مجموعة من الأعمال المصرّح لنا بعرضها.', '');

-- Contact (introduction)
INSERT IGNORE INTO page_translations (page_id, locale, title, meta_title, meta_description, intro, content) VALUES
((SELECT id FROM pages WHERE slug = 'contact'), 'fr', 'Contact', 'Contact — Persomy',
 'Contactez Persomy pour échanger sur votre projet de logiciel, de plateforme, d’intelligence artificielle ou d’automatisation.',
 'Parlez-nous de votre projet. Nous examinerons votre demande et reviendrons vers vous.', ''),
((SELECT id FROM pages WHERE slug = 'contact'), 'en', 'Contact', 'Contact — Persomy',
 'Contact Persomy to discuss your software, platform, artificial intelligence or automation project.',
 'Tell us about your project. We will review your request and get back to you.', ''),
((SELECT id FROM pages WHERE slug = 'contact'), 'ar', 'اتصل بنا', 'اتصل بنا — Persomy',
 'تواصل مع Persomy لمناقشة مشروعك في البرمجيات أو المنصات أو الذكاء الاصطناعي أو الأتمتة.',
 'حدّثنا عن مشروعك. سندرس طلبك ونعاود التواصل معك.', '');

-- Mentions légales
INSERT IGNORE INTO page_translations (page_id, locale, title, meta_title, meta_description, intro, content) VALUES
((SELECT id FROM pages WHERE slug = 'legal'), 'fr', 'Mentions légales', 'Mentions légales — Persomy', '', '',
 '[À COMPLÉTER : mentions légales obligatoires — éditeur du site, forme juridique, adresse, responsable de publication, hébergeur]'),
((SELECT id FROM pages WHERE slug = 'legal'), 'en', 'Legal notice', 'Legal notice — Persomy', '', '',
 '[TO BE COMPLETED: mandatory legal information — publisher, legal form, address, publication director, hosting provider]'),
((SELECT id FROM pages WHERE slug = 'legal'), 'ar', 'الإشعار القانوني', 'الإشعار القانوني — Persomy', '', '',
 '[يُستكمل لاحقًا: المعلومات القانونية الإلزامية — الناشر، الشكل القانوني، العنوان، المسؤول عن النشر، المستضيف]');

-- Politique de confidentialité
INSERT IGNORE INTO page_translations (page_id, locale, title, meta_title, meta_description, intro, content) VALUES
((SELECT id FROM pages WHERE slug = 'privacy'), 'fr', 'Politique de confidentialité', 'Politique de confidentialité — Persomy', '', '',
 '[À COMPLÉTER : politique de confidentialité — responsable du traitement, finalités, durée de conservation, droits des personnes, cookies]'),
((SELECT id FROM pages WHERE slug = 'privacy'), 'en', 'Privacy policy', 'Privacy policy — Persomy', '', '',
 '[TO BE COMPLETED: privacy policy — data controller, purposes, retention period, data subject rights, cookies]'),
((SELECT id FROM pages WHERE slug = 'privacy'), 'ar', 'سياسة الخصوصية', 'سياسة الخصوصية — Persomy', '', '',
 '[يُستكمل لاحقًا: سياسة الخصوصية — المسؤول عن المعالجة، الأغراض، مدة الاحتفاظ، حقوق الأشخاص، ملفات تعريف الارتباط]');

-- ---------- Services ----------
INSERT IGNORE INTO services (slug, icon, sort_order, is_active) VALUES
  ('custom-software', 'code', 10, 1),
  ('saas-paas', 'cloud', 20, 1),
  ('artificial-intelligence', 'ai', 30, 1),
  ('automation', 'automation', 40, 1),
  ('consulting-integration', 'consulting', 50, 1);

INSERT IGNORE INTO service_translations (service_id, locale, title, summary) VALUES
((SELECT id FROM services WHERE slug = 'custom-software'), 'fr', 'Logiciels sur mesure', 'Conception et développement d’applications métier adaptées à vos processus et à vos contraintes.'),
((SELECT id FROM services WHERE slug = 'custom-software'), 'en', 'Custom software', 'Design and development of business applications tailored to your processes and constraints.'),
((SELECT id FROM services WHERE slug = 'custom-software'), 'ar', 'برمجيات مخصصة', 'تصميم وتطوير تطبيقات مهنية تلائم عملياتك وقيودك.'),
((SELECT id FROM services WHERE slug = 'saas-paas'), 'fr', 'Plateformes SaaS / PaaS', 'Création de plateformes en ligne évolutives, du produit à son exploitation.'),
((SELECT id FROM services WHERE slug = 'saas-paas'), 'en', 'SaaS / PaaS platforms', 'Building scalable online platforms, from product to operation.'),
((SELECT id FROM services WHERE slug = 'saas-paas'), 'ar', 'منصات SaaS / PaaS', 'إنشاء منصات إلكترونية قابلة للتوسع، من المنتج إلى التشغيل.'),
((SELECT id FROM services WHERE slug = 'artificial-intelligence'), 'fr', 'Intelligence artificielle', 'Intégration de solutions d’IA pour exploiter vos données et assister vos équipes.'),
((SELECT id FROM services WHERE slug = 'artificial-intelligence'), 'en', 'Artificial intelligence', 'Integration of AI solutions to make use of your data and assist your teams.'),
((SELECT id FROM services WHERE slug = 'artificial-intelligence'), 'ar', 'الذكاء الاصطناعي', 'دمج حلول الذكاء الاصطناعي للاستفادة من بياناتك ومساندة فرقك.'),
((SELECT id FROM services WHERE slug = 'automation'), 'fr', 'Automatisation', 'Automatisation des tâches répétitives et des flux de travail pour gagner en efficacité.'),
((SELECT id FROM services WHERE slug = 'automation'), 'en', 'Automation', 'Automation of repetitive tasks and workflows to improve efficiency.'),
((SELECT id FROM services WHERE slug = 'automation'), 'ar', 'الأتمتة', 'أتمتة المهام المتكررة وسير العمل لرفع الكفاءة.'),
((SELECT id FROM services WHERE slug = 'consulting-integration'), 'fr', 'Conseil et intégration', 'Accompagnement dans le cadrage, le choix des outils et leur intégration à votre système d’information.'),
((SELECT id FROM services WHERE slug = 'consulting-integration'), 'en', 'Consulting and integration', 'Support with scoping, choosing tools and integrating them into your information system.'),
((SELECT id FROM services WHERE slug = 'consulting-integration'), 'ar', 'الاستشارات والتكامل', 'مرافقة في تحديد الاحتياجات واختيار الأدوات ودمجها في نظام معلوماتك.');

-- ---------- Catégories de projets ----------
INSERT IGNORE INTO project_categories (slug, sort_order) VALUES
  ('custom-software', 10), ('saas-paas', 20), ('artificial-intelligence', 30), ('automation', 40);

INSERT IGNORE INTO project_category_translations (category_id, locale, name) VALUES
((SELECT id FROM project_categories WHERE slug = 'custom-software'), 'fr', 'Logiciels sur mesure'),
((SELECT id FROM project_categories WHERE slug = 'custom-software'), 'en', 'Custom software'),
((SELECT id FROM project_categories WHERE slug = 'custom-software'), 'ar', 'برمجيات مخصصة'),
((SELECT id FROM project_categories WHERE slug = 'saas-paas'), 'fr', 'SaaS / PaaS'),
((SELECT id FROM project_categories WHERE slug = 'saas-paas'), 'en', 'SaaS / PaaS'),
((SELECT id FROM project_categories WHERE slug = 'saas-paas'), 'ar', 'SaaS / PaaS'),
((SELECT id FROM project_categories WHERE slug = 'artificial-intelligence'), 'fr', 'Intelligence artificielle'),
((SELECT id FROM project_categories WHERE slug = 'artificial-intelligence'), 'en', 'Artificial intelligence'),
((SELECT id FROM project_categories WHERE slug = 'artificial-intelligence'), 'ar', 'الذكاء الاصطناعي'),
((SELECT id FROM project_categories WHERE slug = 'automation'), 'fr', 'Automatisation'),
((SELECT id FROM project_categories WHERE slug = 'automation'), 'en', 'Automation'),
((SELECT id FROM project_categories WHERE slug = 'automation'), 'ar', 'الأتمتة');
