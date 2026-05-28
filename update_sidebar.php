<?php
$dir = new RecursiveDirectoryIterator('resources/views');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/.*\.blade\.php$/', RegexIterator::GET_MATCH);

$newCss = <<<CSS
        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .sidebar-logo {
            width: 48px;
            height: 48px;
            object-fit: contain;
            border-radius: 10px;
            flex-shrink: 0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            background: #ffffff;
            padding: 2px;
            transition: transform 0.2s ease;
        }

        .sidebar-logo:hover {
            transform: scale(1.05);
        }

        .sidebar-title-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .sidebar-title-main {
            color: #2563eb;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        .sidebar-title-sub {
            color: #64748b;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
        }
CSS;

$newHtml = <<<HTML
        <div class="sidebar-header">
            <img src="{{ asset('img/logo.png') }}" alt="CSC Logo" class="sidebar-logo" onerror="this.style.display='none'">
            <div class="sidebar-title-container">
                <span class="sidebar-title-main">CSC</span>
                <span class="sidebar-title-sub">Plantilla System</span>
            </div>
        </div>
HTML;

$htmlSearch = '/<div class="sidebar-header">\s*<img src="\{\{ asset\(\'img\/logo\.png\'\) \}\}" alt="CSC Logo" class="sidebar-logo"(?:[^>]*)>\s*<div class="sidebar-title">CSC PLANTILLA<\/div>\s*<\/div>/is';

$cssSearch = '/        \.sidebar-header \{(?:.*?)\.sidebar-title \{.*?}/is';

foreach ($files as $match) {
    if (strpos($match[0], 'components\dashboard-app.blade.php') !== false || strpos($match[0], 'components/dashboard-app.blade.php') !== false) {
        continue;
    }

    $file = $match[0];
    $content = file_get_contents($file);
    $originalContent = $content;

    // Some lines might not have onerror="this.style.display='none'"
    $content = preg_replace($htmlSearch, $newHtml, $content);

    // Also try without the onerror, depending on how they copy-pasted it
    $htmlSearch2 = '/<div class="sidebar-header">\s*<img src="\{\{ asset\(\'img\/logo\.png\'\) \}\}" alt="CSC Logo" class="sidebar-logo">\s*<div class="sidebar-title">CSC PLANTILLA<\/div>\s*<\/div>/is';
    $content = preg_replace($htmlSearch2, $newHtml, $content);

    $content = preg_replace($cssSearch, $newCss, $content);

    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        echo "Updated $file\n";
    }
}
?>