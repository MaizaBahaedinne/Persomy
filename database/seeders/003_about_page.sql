-- Persomy : contenu définitif de la page « À propos » (FR/EN/AR), mis en forme par la vue public/about.php.
-- Remplace le contenu initial (marqueurs « à compléter »). À appliquer une seule fois : php bin/migrate.php --seed
-- Convention de mise en forme : « ## Titre » = section ; puces « - **Titre** — texte » = cartes ; texte en *italique* = note.

UPDATE page_translations SET
  title = 'À propos de Persomy',
  meta_title = 'À propos de Persomy',
  meta_description = 'Persomy (Digital Solutions) conçoit des logiciels sur mesure, des plateformes SaaS/PaaS, des solutions d’intelligence artificielle et d’automatisation. Bureaux à Paris et à Tunis.',
  intro = 'Nous transformons vos idées en solutions numériques concrètes.',
  content = 'Persomy (Digital Solutions) accompagne les entreprises et les professionnels dans la conception, le développement et la mise en œuvre de solutions technologiques adaptées à leurs besoins.

Nous développons des logiciels sur mesure, des plateformes SaaS/PaaS, des solutions d’intelligence artificielle et des systèmes d’automatisation pour simplifier les processus, améliorer l’efficacité opérationnelle et soutenir les projets de transformation numérique.

De la réflexion initiale à la mise en œuvre, nous travaillons en collaboration avec nos clients et partenaires afin de concevoir des solutions pertinentes, évolutives et adaptées à leurs objectifs.

## Notre mission

Concevoir et mettre en œuvre des solutions numériques adaptées aux besoins de nos clients, en mobilisant le développement logiciel, l’intelligence artificielle et l’automatisation pour répondre à leurs enjeux technologiques et opérationnels.

*Cette formulation est une proposition éditoriale, à valider comme mission officielle de Persomy.*

## Nos expertises

- **Logiciels sur mesure** — Des applications conçues pour répondre aux besoins spécifiques de chaque organisation.
- **Plateformes SaaS/PaaS** — Des plateformes numériques pensées pour accompagner les usages et évoluer avec les besoins.
- **Intelligence artificielle** — Des solutions d’IA adaptées aux cas d’usage et aux objectifs métier.
- **Automatisation** — L’optimisation des tâches et des processus pour améliorer l’efficacité opérationnelle.
- **Conseil et intégration** — Un accompagnement dans le choix, la conception et la mise en œuvre des solutions technologiques.

## Nos valeurs

Nous souhaitons placer au cœur de chaque projet :

- **Compréhension des besoins** — Partir des enjeux réels du client avant de définir la solution.
- **Qualité** — Concevoir des solutions fiables, cohérentes et maintenables.
- **Innovation utile** — Mobiliser les technologies lorsqu’elles apportent une valeur concrète.
- **Collaboration** — Construire les solutions avec nos clients et partenaires.
- **Évolutivité** — Privilégier des solutions capables de s’adapter aux besoins futurs.

*Ces valeurs sont proposées pour la présentation du site et doivent être confirmées par Persomy.*

## Une présence à Paris et à Tunis

Avec des bureaux à Paris et à Tunis, Persomy s’appuie sur une présence dans deux villes pour développer ses relations professionnelles et accompagner ses projets numériques.

Pour toute demande d’information ou de collaboration, notre équipe peut être contactée via la page Contact.

## Construisons votre prochaine solution numérique

Vous avez un projet logiciel, une plateforme à développer ou un processus à automatiser ?

**Parlons de vos besoins et explorons les solutions adaptées à vos objectifs.**'
WHERE locale = 'fr' AND page_id = (SELECT id FROM pages WHERE slug = 'about');

UPDATE page_translations SET
  title = 'About Persomy',
  meta_title = 'About Persomy',
  meta_description = 'Persomy (Digital Solutions) designs custom software, SaaS/PaaS platforms, artificial intelligence and automation solutions. Offices in Paris and Tunis.',
  intro = 'We turn your ideas into concrete digital solutions.',
  content = 'Persomy (Digital Solutions) supports companies and professionals in designing, developing and implementing technology solutions tailored to their needs.

We build custom software, SaaS/PaaS platforms, artificial intelligence solutions and automation systems to simplify processes, improve operational efficiency and support digital transformation projects.

From the initial thinking to delivery, we work together with our clients and partners to design solutions that are relevant, scalable and aligned with their goals.

## Our mission

To design and deliver digital solutions tailored to our clients’ needs, drawing on software development, artificial intelligence and automation to address their technological and operational challenges.

*This wording is an editorial proposal, to be validated as Persomy’s official mission.*

## Our expertise

- **Custom software** — Applications designed to meet the specific needs of each organisation.
- **SaaS/PaaS platforms** — Digital platforms built to support real usage and grow with your needs.
- **Artificial intelligence** — AI solutions suited to use cases and business objectives.
- **Automation** — Optimising tasks and processes to improve operational efficiency.
- **Consulting and integration** — Guidance in choosing, designing and implementing technology solutions.

## Our values

We want to place at the heart of every project:

- **Understanding needs** — Starting from the client’s real challenges before defining the solution.
- **Quality** — Designing reliable, consistent and maintainable solutions.
- **Useful innovation** — Using technology when it brings concrete value.
- **Collaboration** — Building solutions together with our clients and partners.
- **Scalability** — Favouring solutions able to adapt to future needs.

*These values are proposed for the presentation of the site and must be confirmed by Persomy.*

## A presence in Paris and Tunis

With offices in Paris and Tunis, Persomy relies on a presence in two cities to develop its professional relationships and support its digital projects.

For any information or collaboration request, our team can be reached through the Contact page.

## Let’s build your next digital solution

Do you have a software project, a platform to develop or a process to automate?

**Let’s talk about your needs and explore the solutions suited to your goals.**'
WHERE locale = 'en' AND page_id = (SELECT id FROM pages WHERE slug = 'about');

UPDATE page_translations SET
  title = 'من نحن',
  meta_title = 'من نحن — Persomy',
  meta_description = 'Persomy (Digital Solutions) تصمّم برمجيات مخصصة ومنصات SaaS/PaaS وحلول ذكاء اصطناعي وأتمتة. مكاتب في باريس وتونس.',
  intro = 'نحوّل أفكاركم إلى حلول رقمية ملموسة.',
  content = 'تُرافق Persomy (Digital Solutions) المؤسسات والمهنيين في تصميم وتطوير وتنفيذ حلول تقنية تلائم احتياجاتهم.

نطوّر برمجيات مخصصة، ومنصات SaaS/PaaS، وحلول ذكاء اصطناعي، وأنظمة أتمتة لتبسيط العمليات وتحسين الكفاءة التشغيلية ودعم مشاريع التحول الرقمي.

من الفكرة الأولى إلى التنفيذ، نعمل بالتعاون مع عملائنا وشركائنا لتصميم حلول ملائمة وقابلة للتطور ومنسجمة مع أهدافهم.

## مهمتنا

تصميم وتنفيذ حلول رقمية تلائم احتياجات عملائنا، بالاعتماد على تطوير البرمجيات والذكاء الاصطناعي والأتمتة للاستجابة لتحدياتهم التقنية والتشغيلية.

*هذه الصياغة اقتراح تحريري، ينبغي اعتمادها كمهمة رسمية لـ Persomy.*

## خبراتنا

- **برمجيات مخصصة** — تطبيقات مصمّمة لتلبية الاحتياجات الخاصة بكل مؤسسة.
- **منصات SaaS/PaaS** — منصات رقمية تواكب الاستخدامات وتتطور مع الاحتياجات.
- **الذكاء الاصطناعي** — حلول ذكاء اصطناعي ملائمة لحالات الاستخدام وأهداف العمل.
- **الأتمتة** — تحسين المهام والعمليات لرفع الكفاءة التشغيلية.
- **الاستشارة والتكامل** — مرافقة في اختيار الحلول التقنية وتصميمها وتنفيذها.

## قيمنا

نريد أن نضع في صميم كل مشروع:

- **فهم الاحتياجات** — الانطلاق من التحديات الحقيقية للعميل قبل تحديد الحل.
- **الجودة** — تصميم حلول موثوقة ومتسقة وقابلة للصيانة.
- **ابتكار مفيد** — توظيف التقنيات حين تحقق قيمة ملموسة.
- **التعاون** — بناء الحلول مع عملائنا وشركائنا.
- **قابلية التطور** — تفضيل الحلول القادرة على التكيف مع الاحتياجات المستقبلية.

*هذه القيم مقترحة لعرض الموقع وتحتاج إلى تأكيد من Persomy.*

## حضور في باريس وتونس

بمكاتب في باريس وتونس، تعتمد Persomy على حضورها في مدينتين لتطوير علاقاتها المهنية ومرافقة مشاريعها الرقمية.

لأي طلب معلومات أو تعاون، يمكن التواصل مع فريقنا عبر صفحة الاتصال.

## لنبنِ معًا حلّكم الرقمي القادم

هل لديكم مشروع برمجي، أو منصة تريدون تطويرها، أو عملية ترغبون في أتمتتها؟

**لنتحدث عن احتياجاتكم ونستكشف الحلول الملائمة لأهدافكم.**'
WHERE locale = 'ar' AND page_id = (SELECT id FROM pages WHERE slug = 'about');
