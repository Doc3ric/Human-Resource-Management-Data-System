<?php

$files = array_merge(
    glob('app/Exports/*.php'),
    glob('app/Exports/Reports/*.php')
);

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Skip trait files or interfaces
    if (strpos($content, 'trait ') !== false || strpos($content, 'interface ') !== false) continue;
    if (basename($file) === 'update_exports.php') continue;

    $modified = false;

    // Add imports
    $imports = [
        'use Maatwebsite\Excel\Concerns\WithDrawings;',
        'use Maatwebsite\Excel\Concerns\WithEvents;',
        'use Maatwebsite\Excel\Concerns\WithCustomStartCell;'
    ];
    
    $importBlock = implode("\n", $imports);
    
    if (strpos($content, 'WithDrawings;') === false) {
        // find a good place to insert (after namespace or last use)
        if (preg_match('/use [^;]+;/', $content)) {
            // insert after the first use statement
            $content = preg_replace('/(use [^;]+;)/', "$1\n" . $importBlock, $content, 1);
        } else {
            // insert after namespace
            $content = preg_replace('/(namespace [^;]+;)/', "$1\n\n" . $importBlock, $content, 1);
        }
        $modified = true;
    }

    // Modify implements
    if (preg_match('/class\s+([A-Za-z0-9_]+)(?:\s+extends\s+[A-Za-z0-9_]+)?\s+implements\s+([^{]+)/', $content, $matches)) {
        $implementsStr = trim($matches[2]);
        $interfaces = array_map('trim', explode(',', $implementsStr));
        
        $toAdd = ['WithDrawings', 'WithEvents', 'WithCustomStartCell'];
        foreach ($toAdd as $iface) {
            if (!in_array($iface, $interfaces)) {
                $interfaces[] = $iface;
                $modified = true;
            }
        }
        
        $newImplements = implode(', ', $interfaces);
        $content = str_replace($implementsStr, $newImplements, $content);
    } else if (preg_match('/class\s+([A-Za-z0-9_]+)(?:\s+extends\s+[A-Za-z0-9_]+)?\s*{/', $content)) {
        // If it doesn't implement anything yet
        $content = preg_replace('/class\s+([A-Za-z0-9_]+)(?:\s+extends\s+[A-Za-z0-9_]+)?\s*{/', "class $1$2 implements WithDrawings, WithEvents, WithCustomStartCell\n{", $content);
        $modified = true;
    }

    // Add the trait inside the class
    if (strpos($content, 'use \App\Exports\Traits\HasPhrmoHeader;') === false) {
        $content = preg_replace('/{/', "{\n    use \App\Exports\Traits\HasPhrmoHeader;\n", $content, 1);
        $modified = true;
    }

    if ($modified) {
        file_put_contents($file, $content);
        echo "Updated $file\n";
    }
}

echo "Done.\n";
