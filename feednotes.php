<?php
header('Content-Type: text/html; charset=utf-8');

function getFeedItems() {
    $feedPath = $_SERVER['DOCUMENT_ROOT'] . '/rss/feed.xml';

    if (!file_exists($feedPath) || filesize($feedPath) == 0) {
        return [];
    }

    libxml_use_internal_errors(true);
    $xml = simplexml_load_file($feedPath);
    if ($xml === false) {
        return [];
    }

    $items = [];
    foreach ($xml->channel->item as $item) {
        $timestamp = strtotime((string)$item->pubDate);

        $items[] = [
            'timestamp'   => $timestamp, // ✅ REAL date for sorting
            'date'        => date('n.j.y', $timestamp), // ✅ display only
            'title'       => html_entity_decode((string)$item->title, ENT_QUOTES, 'UTF-8'),
            'link'        => (string)$item->link,
            'description' => html_entity_decode((string)$item->description, ENT_QUOTES, 'UTF-8')
        ];
    }

    usort($items, function ($a, $b) {
        return $b['timestamp'] <=> $a['timestamp']; // ✅ bulletproof
    });

    return $items;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>updates in plaintext</title>
    <style>

    </style>
</head>
<body>
    <div class="rss-feed">
        <?php $items = getFeedItems(); ?>
        <?php if (empty($items)): ?>
            <div class="rss-empty">No updates yet. Check back soon!</div>
        <?php else: ?>
            <?php foreach ($items as $item): ?>
            <div class="rss-item">
                <div class="rss-header">
                    <span class="rss-date"> - <b><?= $item['date'] ?></b></span>
                    <span class="rss-title"> 
                        <b>
                        <a href="<?= htmlspecialchars($item['link']) ?>"><?= htmlspecialchars($item['title']) ?></a>
                        </b> -
                    </span>
                    <span class="rss-description"><?= htmlspecialchars($item['description']) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>
</body>
</html>
