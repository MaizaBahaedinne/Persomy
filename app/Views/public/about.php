<?php
/**
 * Page « À propos » : le contenu (administrable) est découpé en sections « ## Titre » puis mis en forme
 * selon sa nature : liste à puces => cartes/tuiles, premier texte seul => mission, texte suivant => présence,
 * dernière section sans puces => appel à l'action.
 *
 * @var array $page
 */
$split = split_sections((string) $page['content']);
$story = parse_blocks($split['intro']);
$sections = array_map(static fn(array $s): array => $s + ['b' => parse_blocks($s['body'])], $split['sections']);
$cta = null;
if (count($sections) >= 2 && !$sections[count($sections) - 1]['b']['items']) {
    $cta = array_pop($sections);
}

$iconSets = [['code', 'cloud', 'ai', 'automation', 'consulting'], ['target', 'shield', 'bulb', 'users', 'growth']];
$cities = ['Paris', 'Tunis'];
$para = static fn(string $text, string $class = ''): string => '<p' . ($class !== '' ? ' class="' . $class . '"' : '') . '>'
    . str_replace("\n", '<br>', format_inline($text)) . '</p>';
$gridNo = 0;
$missionDone = false;
$soft = true;
?>
<section class="page-hero about-hero">
    <div class="container">
        <h1<?= lang_attrs($page) ?>><?= e($page['title']) ?></h1>
        <?php if (trim((string) $page['intro']) !== ''): ?><p class="lead"<?= lang_attrs($page) ?>><?= e($page['intro']) ?></p><?php endif; ?>
        <p class="hero-meta">
            <span class="hero-meta-label"><?= e(t('footer.offices')) ?></span>
            <?php foreach ($cities as $city): ?><span class="pill"><?= e($city) ?></span><?php endforeach; ?>
        </p>
        <?php $row = $page; require BASE_PATH . '/app/Views/partials/fallback.php'; ?>
    </div>
</section>

<?php if ($story['paragraphs']): ?>
<section class="section about-story">
    <div class="container story"<?= lang_attrs($page) ?>>
        <div class="story-lead">
            <?= $para($story['paragraphs'][0]) ?>
        </div>
        <?php if (count($story['paragraphs']) > 1): ?>
            <div class="story-more">
                <?php foreach (array_slice($story['paragraphs'], 1) as $text): ?>
                    <?= $para($text) ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<?php foreach ($sections as $section):
    $b = $section['b'];
    $class = $soft ? 'section section-soft' : 'section';
    $soft = !$soft;
    ?>
    <?php if ($b['items']):
        $gridNo++;
        $icons = $iconSets[($gridNo - 1) % count($iconSets)];
        ?>
<section class="<?= $class ?>">
    <div class="container"<?= lang_attrs($page) ?>>
        <header class="section-head">
            <h2><?= e($section['title']) ?></h2>
            <?php if ($b['paragraphs']): ?><?= $para(implode(' ', $b['paragraphs'])) ?><?php endif; ?>
        </header>
        <?php if ($gridNo % 2 === 1): ?>
            <div class="grid-fill">
                <?php foreach ($b['items'] as $i => [$title, $desc]): ?>
                    <article class="card service-card">
                        <div class="card-icon"><?= icon($icons[$i % count($icons)]) ?></div>
                        <?php if ($title !== ''): ?><h3 class="card-title"><?= e($title) ?></h3><?php endif; ?>
                        <?= $para($desc) ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <ul class="value-list">
                <?php foreach ($b['items'] as $i => [$title, $desc]): ?>
                    <li class="value">
                        <span class="value-icon"><?= icon($icons[$i % count($icons)]) ?></span>
                        <div>
                            <?php if ($title !== ''): ?><h3><?= e($title) ?></h3><?php endif; ?>
                            <?= $para($desc) ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <?php foreach ($b['notes'] as $note): ?><p class="note"><?= format_inline($note) ?></p><?php endforeach; ?>
    </div>
</section>
    <?php elseif (!$missionDone):
        $missionDone = true;
        ?>
<section class="<?= $class ?>">
    <div class="container"<?= lang_attrs($page) ?>>
        <div class="mission">
            <div class="mission-icon"><?= icon('target') ?></div>
            <div class="mission-body">
                <h2 class="mission-label"><?= e($section['title']) ?></h2>
                <?php foreach ($b['paragraphs'] as $text): ?><?= $para($text, 'mission-text') ?><?php endforeach; ?>
                <?php foreach ($b['notes'] as $note): ?><p class="note"><?= format_inline($note) ?></p><?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
    <?php else: ?>
<section class="<?= $class ?>">
    <div class="container presence"<?= lang_attrs($page) ?>>
        <div class="presence-text">
            <h2><?= e($section['title']) ?></h2>
            <?php foreach ($b['paragraphs'] as $text): ?><?= $para($text) ?><?php endforeach; ?>
            <?php foreach ($b['notes'] as $note): ?><p class="note"><?= format_inline($note) ?></p><?php endforeach; ?>
        </div>
        <div class="presence-cities">
            <p class="presence-label"><?= e(t('footer.offices')) ?></p>
            <ul class="city-list">
                <?php foreach ($cities as $city): ?>
                    <li class="city-card">
                        <span class="city-pin"><?= icon('pin') ?></span>
                        <span class="city-name"><?= e($city) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</section>
    <?php endif; ?>
<?php endforeach; ?>

<section class="cta-band">
    <div class="container cta-inner"<?= $cta ? lang_attrs($page) : '' ?>>
        <div>
            <?php if ($cta): ?>
                <h2><?= e($cta['title']) ?></h2>
                <?php foreach ($cta['b']['paragraphs'] as $text): ?>
                    <?= $para($text, preg_match('/^\*\*.+\*\*$/u', $text) === 1 ? 'cta-strong' : '') ?>
                <?php endforeach; ?>
            <?php else: ?>
                <h2><?= e(t('cta.discuss')) ?></h2>
                <p><?= e(t('cta.discuss_text')) ?></p>
            <?php endif; ?>
        </div>
        <a class="btn btn-primary btn-arrow" href="<?= e(lurl('contact')) ?>"><?= e(t('cta.contact')) ?></a>
    </div>
</section>
