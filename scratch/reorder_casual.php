<?php

$file = 'c:/laragon/www/Human-Resource-Management-Data-System/resources/views/casual/index.blade.php';
$content = file_get_contents($file);

// Find the cas-filter form
$start = strpos($content, '<form method="GET" action="{{ route(\'casual.index\') }}" id="cas-search-form">');
$end = strpos($content, '</form>', $start) + 7;
$formContent = substr($content, $start, $end - $start);

// we need to extract the buttons and inputs.
$searchBtn = '<button type="submit" class="cas-btn primary"><i class="bi bi-search"></i> Search</button>';
$resetBtn = '<a href="{{ route(\'casual.index\') }}" class="cas-btn reset"><i class="bi bi-arrow-counterclockwise"></i>
                Reset</a>';
// Export Button
preg_match('/<button[^>]*data-bs-toggle="modal"[^>]*>.*?<\/button>/s', $formContent, $exportMatches);
if (!$exportMatches) {
    preg_match('/<button[^>]*onclick="new bootstrap.Modal\(document.getElementById\(\'exportModal\'\)\)\.show\(\)"[^>]*>.*?<\/button>/s', $formContent, $exportMatches);
}

// Add button
preg_match('/@if\(auth\(\)->user\(\)->isSuperAdmin\(\) \|\| auth\(\)->user\(\)->isInventoryAdmin\(\)\)\s*<a href="[^"]*casual\.create[^"]*" class="cas-btn add">.*?<\/a>/s', $formContent, $addMatches);

// Import button
preg_match('/<button type="button" class="cas-btn import".*?<\/button>/s', $formContent, $importMatches);

// Select Multiple
preg_match('/@if\(auth\(\)->user\(\)->isSuperAdmin\(\) \|\| auth\(\)->user\(\)->isInventoryAdmin\(\)\)\s*<button type="button" class="cas-btn" id="toggle-select-multiple".*?<\/button>\s*@endif/s', $formContent, $selectMatches);

// Import History
preg_match('/<a href="[^"]*casual\.import\.history[^"]*" class="cas-btn"[^>]*>.*?<\/a>/s', $formContent, $historyMatches);

// Delete All
preg_match('/@if\(auth\(\)->user\(\)->isSuperAdmin\(\)\)\s*<button type="button" id="delete-all-btn".*?<\/button>\s*@endif/s', $formContent, $deleteMatches);

// Inputs (Search, Office, Detail, Sex, Per Page)
// We'll just regex all inputs/selects that are not buttons
preg_match('/<input type="text" name="search".*?>/s', $formContent, $searchInput);
preg_match('/<select name="office\[\]".*?<\/select>/s', $formContent, $officeSelect);
preg_match('/<select name="position".*?<\/select>/s', $formContent, $posSelect);
preg_match('/<select name="detail".*?<\/select>/s', $formContent, $detailSelect);
preg_match('/<select name="sex".*?<\/select>/s', $formContent, $sexSelect);
preg_match('/<select name="per_page".*?<\/select>/s', $formContent, $perPageSelect);

// Build new form content
$newForm = '<form method="GET" action="{{ route(\'casual.index\') }}" id="cas-search-form">
        <div class="cas-filter" style="display: flex; flex-direction: column; gap: 12px; align-items: stretch; padding: 14px 18px;">
            <!-- Action Buttons (Left Aligned by Frequency) -->
            <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center; justify-content: flex-start;">
                ' . $searchBtn . '
                ' . $resetBtn . '
                ' . ($exportMatches[0] ?? '') . '
                ' . ($addMatches[0] ?? '') . '
                ' . ($importMatches[0] ?? '') . '
                ' . ($selectMatches[0] ?? '') . '
                ' . ($historyMatches[0] ?? '') . '
                ' . ($deleteMatches[0] ?? '') . '
            </div>
            
            <!-- Filters -->
            <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                ' . ($searchInput[0] ?? '') . '
                ' . ($officeSelect[0] ?? '') . '
                ' . ($posSelect[0] ?? '') . '
                ' . ($detailSelect[0] ?? '') . '
                ' . ($sexSelect[0] ?? '') . '
                ' . ($perPageSelect[0] ?? '') . '
            </div>
        </div>
    </form>';

$content = str_replace($formContent, $newForm, $content);
file_put_contents($file, $content);
echo "Casual modified\n";

