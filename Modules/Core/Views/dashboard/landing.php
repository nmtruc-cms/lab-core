<?= $this->extend('layout/main_no_sidebar') ?>

<?= $this->section('content') ?>
<div class="module-landing-shell">
    <div class="module-landing-head">
        <span class="admin-kicker">WORKSPACES</span>
        <h1><?= esc($pageTitle ?? 'Module Dashboard') ?></h1>
        <p><?= esc($pageSubtitle ?? 'Choose the workspace you want to enter.') ?></p>
        <?php if (! empty($pageIntro)) : ?>
            <div class="module-landing-intro"><?= esc($pageIntro) ?></div>
        <?php endif ?>
    </div>

    <?php if (! empty($summaryCards)) : ?>
        <div class="module-summary-grid">
            <?php foreach ($summaryCards as $summary) : ?>
                <section class="module-summary-card <?= esc($summary['tone']) ?>">
                    <span><?= esc($summary['label']) ?></span>
                    <strong><?= esc((string) $summary['value']) ?></strong>
                </section>
            <?php endforeach ?>
        </div>
    <?php endif ?>

    <div class="module-card-grid">
        <?php foreach ($cards as $card) : ?>
            <article class="module-card <?= esc($card['accent']) ?>">
                <div class="module-card-top">
                    <div class="module-card-identity">
                        <div class="module-icon-wrap" aria-hidden="true">
                            <i class="fa-solid <?= esc($card['icon']) ?>"></i>
                        </div>
                        <div>
                            <span class="module-code"><?= esc($card['title']) ?></span>
                            <h2><?= esc($card['subtitle']) ?></h2>
                        </div>
                    </div>
                    <span class="module-status <?= esc($card['statusTone'] ?? 'muted') ?>"><?= esc($card['status']) ?></span>
                </div>

                <p><?= esc($card['description']) ?></p>

                <?php if (! empty($card['meta'])) : ?>
                    <div class="module-meta-grid">
                        <?php foreach ($card['meta'] as $meta) : ?>
                            <div class="module-meta-item">
                                <span><?= esc($meta['label']) ?></span>
                                <strong><?= esc($meta['value']) ?></strong>
                            </div>
                        <?php endforeach ?>
                    </div>
                <?php endif ?>

                <?php if (! empty($card['highlights'])) : ?>
                    <ul class="module-highlight-list">
                        <?php foreach ($card['highlights'] as $highlight) : ?>
                            <li><?= esc($highlight) ?></li>
                        <?php endforeach ?>
                    </ul>
                <?php endif ?>

                <?php if (! empty($card['note'])) : ?>
                    <div class="module-note"><?= esc($card['note']) ?></div>
                <?php endif ?>

                <div class="module-card-footer">
                    <?php if ($card['url']) : ?>
                        <a href="<?= esc($card['url']) ?>" class="admin-btn primary"><?= esc($card['cta'] ?? 'Open Workspace') ?></a>
                    <?php else : ?>
                        <span class="module-disabled"><?= esc($card['cta'] ?? 'Coming Soon') ?></span>
                    <?php endif ?>
                </div>
            </article>
        <?php endforeach ?>
    </div>
</div>
<?= $this->endSection() ?>
