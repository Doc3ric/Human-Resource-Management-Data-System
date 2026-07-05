<?php

$casualFile = 'c:/laragon/www/Human-Resource-Management-Data-System/resources/views/casual/index.blade.php';
$casualContent = file_get_contents($casualFile);

$casCss = '';
if (preg_match('/(\.cas-hero\s*\{.*?\/\* Filter bar \*\/)/s', $casualContent, $cssMatch)) {
    $casCss = $cssMatch[1];
    $casCss = str_replace('/* Filter bar */', '', $casCss);
}

if (empty($casCss) || strpos($casCss, '.cas-hero-badge') === false) {
    // fallback CSS
    $casCss = '
        .cas-hero { background: linear-gradient(135deg, #052c65 0%, #1e3a8a 55%, #1e40af 100%); border-radius: 14px; padding: 24px 28px; position: relative; overflow: hidden; margin-bottom: 20px; }
        .cas-hero::before { content: \'\'; position: absolute; inset: 0; background-image: radial-gradient(circle, rgba(255, 255, 255, .07) 1px, transparent 1px); background-size: 22px 22px; }
        .cas-hero-inner { position: relative; z-index: 1; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
        .cas-hero h1 { color: #fff; font-size: 22px; font-weight: 800; margin: 0; }
        .cas-hero p { color: rgba(255, 255, 255, .65); font-size: 12px; margin: 4px 0 0; }
        .cas-hero-badge { display: inline-flex; align-items: center; gap: 6px; background: rgba(255, 255, 255, .15); border: 1px solid rgba(255, 255, 255, .25); color: #fff; padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 700; }
        .cas-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px; }
        @media (max-width: 1024px) { .cas-stats { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 640px) { .cas-stats { grid-template-columns: 1fr; } }
        .cas-stat { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px 22px; box-shadow: 0 1px 2px rgba(0, 0, 0, .02); display: flex; flex-direction: column; justify-content: space-between; }
        .cas-stat-top { display: flex; justify-content: space-between; align-items: flex-start; }
        .cas-stat-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; color: #475569; margin-bottom: 4px; }
        .cas-stat-value { font-size: 30px; font-weight: 800; color: #0f172a; line-height: 1; margin: 0; }
        .cas-stat-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; }
        .cas-stat-icon.indigo { background: #e0e7ff; color: #4338ca; }
        .cas-stat-icon.blue { background: #e0f2fe; color: #0284c7; }
        .cas-stat-icon.purple { background: #f3e8ff; color: #9333ea; }
        .cas-stat-icon.red { background: #fee2e2; color: #dc2626; }
        .cas-stat-sub { margin-top: 14px; font-size: 11px; font-weight: 600; color: #94a3b8; }
    ';
}
$casCss = "\n" . trim($casCss) . "\n";

$files = [
    'job-orders' => [
        'path' => 'c:/laragon/www/Human-Resource-Management-Data-System/resources/views/job-orders/index.blade.php',
        'title' => 'Job Order Employees',
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
        'title' => 'All Data Inventory',
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

foreach ($files as $key => $config) {
    if (!file_exists($config['path'])) continue;
    $content = file_get_contents($config['path']);

    // 1. Inject CSS if not present
    if (strpos($content, '.cas-hero') === false) {
        if (strpos($content, '.cas-filter') !== false) {
            $content = str_replace('.cas-filter', trim($casCss) . "\n        .cas-filter", $content);
        } else {
            $content = preg_replace('/<style>/', "<style>\n" . trim($casCss) . "\n", $content, 1);
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

    {{-- Flash messages --}}
    @if(session(\'success\'))
        <div class="flash-success"><i class="bi bi-check-circle-fill"></i> {{ session(\'success\') }}</div>
    @endif
    @if(session(\'error\'))
        <div class="flash-error"><i class="bi bi-exclamation-circle-fill"></i> {{ session(\'error\') }}</div>
    @endif

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
                            <div class="cas-stat-label" title="{{ $office }}">{{ Str::limit($office ?: \'No Office\', 20) }}</div>
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
        // Job Orders has:
        // <div class="jo-page">
        //     <div class="jo-stats-grid">
        //         ... (including @foreach)
        //     </div>
        //     <div class="cas-filter"...
        
        $posStart = strpos($content, '<div class="jo-page">');
        $posEnd = strpos($content, '<div class="cas-filter"');
        if ($posStart !== false && $posEnd !== false && $posStart < $posEnd) {
            $before = substr($content, 0, $posStart);
            $after = substr($content, $posEnd);
            $content = $before . $newHtml . "\n    " . $after;
        }
    } elseif ($key === 'all-data') {
        $posStart = strpos($content, '{{-- Hero --}}');
        $posEnd = strpos($content, '<div class="cas-filter"');
        if ($posStart !== false && $posEnd !== false && $posStart < $posEnd) {
            $before = substr($content, 0, $posStart);
            $after = substr($content, $posEnd);
            $content = $before . $newHtml . "\n    " . $after;
        }
    } elseif ($key === 'permanent') {
        $posStart = strpos($content, '{{-- Hero --}}');
        $posEnd = strpos($content, '<div class="cas-filter"');
        if ($posStart !== false && $posEnd !== false && $posStart < $posEnd) {
            $before = substr($content, 0, $posStart);
            $after = substr($content, $posEnd);
            $content = $before . $newHtml . "\n    " . $after;
        }
    }
    
    file_put_contents($config['path'], $content);
    echo "Replaced HTML in $key\n";
}
