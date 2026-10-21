<?php
/** Bandeau de consentement (affiché par consent.js uniquement si aucun choix n'est enregistré). */
?>
<div class="consent" role="region" aria-labelledby="consent-title" data-consent hidden>
    <div class="consent-body">
        <h2 id="consent-title" class="consent-title"><?= e(t('consent.title')) ?></h2>
        <p><?= e(t('consent.text')) ?> <a href="<?= e(lurl('privacy')) ?>"><?= e(t('consent.more')) ?></a></p>
    </div>
    <div class="consent-actions">
        <button class="btn btn-outline-light" type="button" data-consent-choice="denied"><?= e(t('consent.refuse')) ?></button>
        <button class="btn btn-primary" type="button" data-consent-choice="granted"><?= e(t('consent.accept')) ?></button>
    </div>
</div>
