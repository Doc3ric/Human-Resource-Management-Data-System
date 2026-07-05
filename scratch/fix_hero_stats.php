<?php

$casualFile = 'c:/laragon/www/Human-Resource-Management-Data-System/resources/views/casual/index.blade.php';
$casualContent = file_get_contents($casualFile);

$casCss = '';
if (preg_match('/(\.cas-hero\s*\{.*?\/\* Filter bar \*\/)/s', $casualContent, $cssMatch)) {
    $casCss = $cssMatch[1];
    // Remove the /* Filter bar */ comment at the end
    $casCss = str_replace('/* Filter bar */', '', $casCss);
}

// Ensure casCss starts properly
$casCss = "\n" . trim($casCss) . "\n";

$filesToProcess = [
    'job-orders' => [
        'path' => 'c:/laragon/www/Human-Resource-Management-Data-System/resources/views/job-orders/index.blade.php',
        'title' => 'Job Order Inventory',
        'titleIcon' => 'bi bi-person-workspace',
        'desc' => 'Job Order personnel inventory',
        'labelTotal' => 'Total JO',
        'subTotal' => 'All JO employees',
        'subMale' => 'Male JO employees',
        'subFemale' => 'Female JO employees',
        'heroVar' => '$total',
        'maleVar' => '$maleCount',
        'femaleVar' => '$femaleCount',
        'hasOffices' => true
    ],
    'permanent' => [
        'path' => 'c:/laragon/www/Human-Resource-Management-Data-System/resources/views/permanent/index.blade.php',
        'title' => 'Regular Employees',
        'titleIcon' => 'bi bi-person-badge',
        'desc' => 'Regular/Permanent personnel inventory',
        'labelTotal' => 'Total Regular',
        'subTotal' => 'All regular employees',
        'subMale' => 'Male regular employees',
        'subFemale' => 'Female regular employees',
        'heroVar' => '$total',
        'maleVar' => '$maleCount',
        'femaleVar' => '$femaleCount',
        'hasOffices' => false
    ],
    'all-data' => [
        'path' => 'c:/laragon/www/Human-Resource-Management-Data-System/resources/views/all-data/index.blade.php',
        'title' => 'All Data',
        'titleIcon' => 'bi bi-table',
        'desc' => 'Consolidated personnel database',
        'labelTotal' => 'Total Records',
        'subTotal' => 'Across all sources',
        'subMale' => 'Male personnel',
        'subFemale' => 'Female personnel',
        'heroVar' => '$total',
        'maleVar' => '$maleCount',
        'femaleVar' => '$femaleCount',
        'hasOffices' => false
    ]
];

foreach ($filesToProcess as $key => $config) {
    if (!file_exists($config['path'])) continue;
    $content = file_get_contents($config['path']);

    // 1. Inject CSS if not present
    if (strpos($content, '.cas-hero') === false) {
        // inject right before .cas-filter
        if (strpos($content, '.cas-filter') !== false) {
            $content = str_replace('.cas-filter', $casCss . "\n        .cas-filter", $content);
        } else {
            $content = preg_replace('/<style>/', "<style>\n" . $casCss . "\n", $content, 1);
        }
    }

    // 2. Build the new HTML structure for Hero + Stats
    $newHtml = '    {{-- Hero --}}
    <div class="cas-hero">
        <div class="cas-hero-inner">
            <div>
                <h1><i class="' . $config['titleIcon'] . ' me-2"></i>' . $config['title'] . '</h1>
                <p>' . $config['desc'] . ' — all {{ number_format(' . $config['heroVar'] . ') }} entries</p>
            </div>
            <span class="cas-hero-badge"><i class="bi bi-database-fill"></i> {{ number_format(' . $config['heroVar'] . ') }} Records</span>
        </div>
    </div>

    {{-- Stats bar --}}
    <div class="cas-stats">
        <div class="cas-stat">
            <div class="cas-stat-top">
                <div>
                    <div class="cas-stat-label">' . $config['labelTotal'] . '</div>
                    <div class="cas-stat-value">{{ number_format(' . $config['heroVar'] . ') }}</div>
                </div>
                <div class="cas-stat-icon indigo"><i class="bi bi-people-fill"></i></div>
            </div>
            <div class="cas-stat-sub">' . $config['subTotal'] . '</div>
        </div>
        <div class="cas-stat">
            <div class="cas-stat-top">
                <div>
                    <div class="cas-stat-label">Male</div>
                    <div class="cas-stat-value">{{ number_format(' . $config['maleVar'] . ') }}</div>
                </div>
                <div class="cas-stat-icon blue"><i class="bi bi-gender-male"></i></div>
            </div>
            <div class="cas-stat-sub">' . $config['subMale'] . '</div>
        </div>
        <div class="cas-stat">
            <div class="cas-stat-top">
                <div>
                    <div class="cas-stat-label">Female</div>
                    <div class="cas-stat-value">{{ number_format(' . $config['femaleVar'] . ') }}</div>
                </div>
                <div class="cas-stat-icon" style="background:#fce7f3;color:#db2777;"><i class="bi bi-gender-female"></i></div>
            </div>
            <div class="cas-stat-sub">' . $config['subFemale'] . '</div>
        </div>';

    if ($config['hasOffices']) {
        $newHtml .= '
        @if(isset($officeCounts))
            @foreach($officeCounts as $office => $count)
                <div class="cas-stat">
                    <div class="cas-stat-top">
                        <div>
                            <div class="cas-stat-label">{{ Str::limit($office ?: \'No Office\', 20) }}</div>
                            <div class="cas-stat-value">{{ number_format($count) }}</div>
                        </div>
                        <div class="cas-stat-icon" style="background:#f1f5f9;color:#475569;"><i class="bi bi-building"></i></div>
                    </div>
                    <div class="cas-stat-sub">JO employees</div>
                </div>
            @endforeach
        @endif';
    }

    $newHtml .= '
    </div>';

    // 3. Replace old hero/stats section based on file type
    if ($key === 'job-orders') {
        // Find <div class="jo-page"> to end of jo-stats-grid
        $content = preg_replace('/<div class="jo-page">\s*<div class="jo-stats-grid">.*?<\/div>\s*<\/div>\s*<\/div>\s*@endforeach\s*<\/div>/s', "<div class=\"jo-page\">\n" . $newHtml, $content);
        // Fallback for just removing jo-stats-grid if it is alone
        $content = preg_replace('/<div class="jo-stats-grid">.*?<\/div>\s*<\/div>\s*<\/div>\s*@endforeach\s*<\/div>/s', $newHtml, $content);
        // Better fallback
        $content = preg_replace('/<div class="jo-stats-grid">.*?(<div class="cas-filter")/s', $newHtml . "\n    $1", $content);
    } elseif ($key === 'all-data') {
        $content = preg_replace('/\{\{-- Hero --\}\}\s*<div class="ad-hero">.*?<\/div>\s*<\/div>\s*\{\{-- Stats bar --\}\}\s*<div class="ad-stats">.*?<\/div>\s*<\/div>\s*<\/div>/s', $newHtml, $content);
    } elseif ($key === 'permanent') {
        $content = preg_replace('/\{\{-- Hero --\}\}\s*<div class="perm-hero">.*?<\/div>\s*<\/div>\s*\{\{-- Flash messages --\}\}.*?\{\{-- Stats bar --\}\}\s*<div class="perm-stats">.*?<\/div>\s*<\/div>\s*<\/div>/s', $newHtml, $content);
        // If it doesn't match, maybe flash messages are elsewhere
        $content = preg_replace('/\{\{-- Hero --\}\}\s*<div class="perm-hero">.*?\{\{-- Stats bar --\}\}\s*<div class="perm-stats">.*?<\/div>\s*<\/div>\s*<\/div>/s', $newHtml, $content);
    }
    
    file_put_contents($config['path'], $content);
    echo "Replaced HTML in $key\n";
}
