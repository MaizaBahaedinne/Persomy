<?php
/**
 * @var array       $page
 * @var array       $services
 * @var array       $old
 * @var array       $errors
 * @var string|null $formError
 * @var string|null $result
 */
$email = setting('contact_email');
$phone = setting('contact_phone');
$paris = setting('address_paris');
$tunis = setting('address_tunis');
$hasDetails = !is_placeholder($email) || !is_placeholder($phone) || !is_placeholder($paris) || !is_placeholder($tunis);
$val = static fn(string $k): string => (string) ($old[$k] ?? '');
$privacyUrl = lurl('privacy');
?>
<section class="page-hero">
    <div class="container">
        <h1<?= lang_attrs($page) ?>><?= e($page['title']) ?></h1>
        <?php if (trim((string) $page['intro']) !== ''): ?><p class="lead"<?= lang_attrs($page) ?>><?= e($page['intro']) ?></p><?php endif; ?>
    </div>
</section>

<section class="section section-soft">
    <div class="container contact-grid">
        <div class="form-card">
            <?php $row = $page; require BASE_PATH . '/app/Views/partials/fallback.php'; ?>

            <?php if ($result !== null): ?>
                <div class="alert alert-success" role="status">
                    <h2><?= e(t('contact.success_title')) ?></h2>
                    <p><?= e(t('contact.success_' . $result)) ?></p>
                    <p><a class="btn btn-outline" href="<?= e(lurl('contact')) ?>"><?= e(t('contact.back')) ?></a></p>
                </div>
            <?php else: ?>
                <?php if ($formError !== null): ?>
                    <div class="alert alert-error" role="alert"><p><?= e($formError) ?></p></div>
                <?php elseif ($errors): ?>
                    <div class="alert alert-error" role="alert" tabindex="-1" data-autofocus>
                        <p><?= e(t('contact.form_errors')) ?></p>
                        <ul>
                            <?php foreach ($errors as $field => $message): ?>
                                <li><a href="#f-<?= e($field) ?>"><?= e($message) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form class="form" method="post" action="<?= e(lurl('contact')) ?>" novalidate>
                    <?= csrf_field() ?>
                    <p class="form-note"><?= e(t('contact.required_note')) ?></p>

                    <div class="hp" aria-hidden="true">
                        <label>Website <input type="text" name="website" value="" tabindex="-1" autocomplete="off"></label>
                    </div>

                    <div class="field">
                        <label for="f-name"><?= e(t('contact.name')) ?> *</label>
                        <input id="f-name" name="name" type="text" maxlength="100" required autocomplete="name"
                               value="<?= e($val('name')) ?>"<?= isset($errors['name']) ? ' aria-invalid="true" aria-describedby="e-name"' : '' ?>>
                        <?php if (isset($errors['name'])): ?><p class="field-error" id="e-name"><?= e($errors['name']) ?></p><?php endif; ?>
                    </div>

                    <div class="field">
                        <label for="f-email"><?= e(t('contact.email')) ?> *</label>
                        <input id="f-email" name="email" type="email" maxlength="190" required autocomplete="email" dir="ltr"
                               value="<?= e($val('email')) ?>"<?= isset($errors['email']) ? ' aria-invalid="true" aria-describedby="e-email"' : '' ?>>
                        <?php if (isset($errors['email'])): ?><p class="field-error" id="e-email"><?= e($errors['email']) ?></p><?php endif; ?>
                    </div>

                    <div class="field">
                        <label for="f-company"><?= e(t('contact.company')) ?> <span class="optional"><?= e(t('contact.optional')) ?></span></label>
                        <input id="f-company" name="company" type="text" maxlength="150" autocomplete="organization"
                               value="<?= e($val('company')) ?>"<?= isset($errors['company']) ? ' aria-invalid="true" aria-describedby="e-company"' : '' ?>>
                        <?php if (isset($errors['company'])): ?><p class="field-error" id="e-company"><?= e($errors['company']) ?></p><?php endif; ?>
                    </div>

                    <div class="field">
                        <label for="f-subject"><?= e(t('contact.subject')) ?> <span class="optional"><?= e(t('contact.optional')) ?></span></label>
                        <select id="f-subject" name="subject"<?= isset($errors['subject']) ? ' aria-invalid="true" aria-describedby="e-subject"' : '' ?>>
                            <option value=""><?= e(t('contact.subject_none')) ?></option>
                            <?php foreach ($services as $service): ?>
                                <option value="<?= e($service['slug']) ?>"<?= $val('subject') === $service['slug'] ? ' selected' : '' ?>><?= e($service['t']['title']) ?></option>
                            <?php endforeach; ?>
                            <option value="other"<?= $val('subject') === 'other' ? ' selected' : '' ?>><?= e(t('contact.subject_other')) ?></option>
                        </select>
                        <?php if (isset($errors['subject'])): ?><p class="field-error" id="e-subject"><?= e($errors['subject']) ?></p><?php endif; ?>
                    </div>

                    <div class="field">
                        <label for="f-message"><?= e(t('contact.message')) ?> *</label>
                        <textarea id="f-message" name="message" rows="7" maxlength="5000" required
                                  <?= isset($errors['message']) ? 'aria-invalid="true" aria-describedby="e-message"' : '' ?>><?= e($val('message')) ?></textarea>
                        <?php if (isset($errors['message'])): ?><p class="field-error" id="e-message"><?= e($errors['message']) ?></p><?php endif; ?>
                    </div>

                    <div class="field field-check">
                        <input id="f-consent" name="consent" type="checkbox" value="1" required<?= isset($errors['consent']) ? ' aria-invalid="true" aria-describedby="e-consent"' : '' ?>>
                        <label for="f-consent">
                            <?= e(t('contact.consent')) ?> *
                            <a href="<?= e($privacyUrl) ?>"><?= e(t('contact.privacy_link')) ?></a>
                        </label>
                        <?php if (isset($errors['consent'])): ?><p class="field-error" id="e-consent"><?= e($errors['consent']) ?></p><?php endif; ?>
                    </div>

                    <button class="btn btn-primary" type="submit"><?= e(t('contact.submit')) ?></button>
                </form>
            <?php endif; ?>
        </div>

        <aside class="contact-aside" aria-labelledby="contact-details">
            <h2 id="contact-details"><?= e(t('contact.details')) ?></h2>
            <?php if (!$hasDetails): ?>
                <p><?= e(t('contact.no_details')) ?></p>
            <?php else: ?>
                <dl class="details">
                    <?php if (!is_placeholder($email)): ?>
                        <dt><?= e(t('contact.email_label')) ?></dt>
                        <dd><a href="mailto:<?= e($email) ?>" dir="ltr"><?= e($email) ?></a></dd>
                    <?php endif; ?>
                    <?php if (!is_placeholder($phone)): ?>
                        <dt><?= e(t('contact.phone_label')) ?></dt>
                        <dd><a href="tel:<?= e(preg_replace('/[^+0-9]/', '', (string) $phone)) ?>" dir="ltr"><?= e($phone) ?></a></dd>
                    <?php endif; ?>
                    <?php if (!is_placeholder($paris)): ?>
                        <dt>Paris</dt>
                        <dd><?= nl2br(e($paris)) ?></dd>
                    <?php endif; ?>
                    <?php if (!is_placeholder($tunis)): ?>
                        <dt>Tunis</dt>
                        <dd><?= nl2br(e($tunis)) ?></dd>
                    <?php endif; ?>
                </dl>
            <?php endif; ?>
        </aside>
    </div>
</section>
