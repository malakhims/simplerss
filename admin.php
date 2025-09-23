<?php
require_once 'auth.php';
check_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $title = htmlspecialchars($_POST['title']);
    $link = htmlspecialchars($_POST['link']);
    $description = htmlspecialchars($_POST['description']);
    $pubDate = date('r', strtotime($_POST['pubdate']));

    // Create new item XML (properly escaped)
    $newItem = <<<XML
    <item>
        <title><![CDATA[$title]]></title>
        <link>$link</link>
        <description><![CDATA[$description]]></description>
        <pubDate>$pubDate</pubDate>
    </item>
XML;

    // Load or create feed
    if (file_exists('feed.xml') && filesize('feed.xml') > 0) {
        $xml = simplexml_load_file('feed.xml');
        if ($xml === false) {
            // If corrupted, create new
            $xml = new SimpleXMLElement('<?xml version="1.0"?><rss version="2.0"><channel></channel></rss>');
        }
    } else {
        $xml = new SimpleXMLElement('<?xml version="1.0"?><rss version="2.0"><channel></channel></rss>');
    }

    // Add channel info if missing
    if (!isset($xml->channel->title)) {
        $xml->channel->addChild('title', 'YOUR SITE UPDATES');
        $xml->channel->addChild('link', 'https://sitename.com/');
        $xml->channel->addChild('description', 'Recent updates from  YOURSITENAMEORWHATEVER');
    }

    // Add new item at beginning
    $newNode = dom_import_simplexml($xml->channel);
    $fragment = $newNode->ownerDocument->createDocumentFragment();
    $fragment->appendXML($newItem);
    $newNode->insertBefore($fragment, $newNode->firstChild);

    // Save with proper formatting
    $dom = new DOMDocument('1.0');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->loadXML($xml->asXML());
    file_put_contents('feed.xml', $dom->saveXML());

    header("Location: admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add RSS Item</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 20px auto; padding: 20px; }
        textarea { width: 100%; height: 150px; }
        .form-group { margin-bottom: 15px; }
    </style>
</head>
<body>
    <h1>Add RSS Item</h1>
    <form method="post">
        <div class="form-group">
            <label>Title:</label>
            <input type="text" name="title" required>
        </div>
        <div class="form-group">
            <label>Link:</label>
            <input type="url" name="link" required>
        </div>
        <div class="form-group">
            <label>Publish Date:</label>
            <input type="datetime-local" name="pubdate" id="pubdate" required>
        </div>
        <div class="form-group">
            <label>Description:</label>
            <textarea name="description" required></textarea>
        </div>
        <button type="submit">Add Item</button>
    </form>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr("#pubdate", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            defaultDate: "today"
        });
    </script>
</body>
</html>
