<?php
$file = 'c:/laragon/www/Human-Resource-Management-Data-System/resources/views/all-data/index.blade.php';
$content = file_get_contents($file);

if (preg_match('/<form[^>]*id="search-form"[^>]*>(.*?)<\/form>/s', $content, $formMatch)) {
    $newFormInner = '
    <div class="cas-filter" style="display: flex; flex-direction: column; gap: 12px; align-items: stretch; padding: 14px 18px; margin-bottom: 16px; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, .04);">
        <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
            <input type="text" name="search" value="{{ request(\'search\') }}" class="cas-input" placeholder="🔍 Search name, position, office…" autocomplete="off">
            
            <select name="office[]" class="cas-select" id="office-select" multiple="multiple" style="min-width:250px;" title="Office">
                @php $selectedOffices = (array) request(\'office\', []); @endphp
                <option value="" disabled>— Office —</option>
                @foreach($offices as $office)
                    <option value="{{ $office }}" {{ in_array($office, $selectedOffices) ? \'selected\' : \'\' }}>{{ Str::limit($office, 45) }}</option>
                @endforeach
            </select>

            <select name="status" class="cas-select" style="min-width:140px;">
                <option value="">— Status —</option>
                <option value="P" {{ request(\'status\') === \'P\' ? \'selected\' : \'\' }}>Regular</option>
                <option value="C" {{ request(\'status\') === \'C\' ? \'selected\' : \'\' }}>Casual</option>
                <option value="JO" {{ request(\'status\') === \'JO\' ? \'selected\' : \'\' }}>Job Order</option>
            </select>

            <select name="sex" class="cas-select" style="min-width:110px;">
                <option value="">— Gender —</option>
                <option value="M" {{ request(\'sex\') === \'M\' ? \'selected\' : \'\' }}>Male</option>
                <option value="F" {{ request(\'sex\') === \'F\' ? \'selected\' : \'\' }}>Female</option>
            </select>
            
            <button type="submit" class="cas-btn primary"><i class="bi bi-search"></i> Search</button>
            <a href="{{ route(\'all-data.index\') }}" class="cas-btn reset"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
        </div>
        
        <!-- Bottom Row: Actions (Far Left) -->
        <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center; justify-content: flex-start;">
            @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                <a href="{{ route(\'all-data.create\') }}" class="cas-btn add"><i class="bi bi-plus-lg"></i> Add Record</a>
                <button type="button" class="cas-btn import" onclick="document.getElementById(\'import-overlay\').classList.add(\'active\')"><i class="bi bi-upload"></i> Import Excel</button>
            @endif
            <a href="{{ route(\'all-data.import.history\') }}" class="cas-btn" style="background:#ffffff;color:#334155;border:1px solid #cbd5e1;"><i class="bi bi-clock-history"></i> Import History</a>
            <button type="button" class="cas-btn" style="background:#ffffff;color:#334155;border:1px solid #cbd5e1;cursor:pointer;" onclick="new bootstrap.Modal(document.getElementById(\'exportModal\')).show()"><i class="bi bi-file-earmark-arrow-down-fill"></i> Export Settings</button>
            @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                <button type="button" class="cas-btn" id="toggle-select-multiple" style="background:#ffffff;color:#334155;border:1px solid #cbd5e1;"><i class="bi bi-ui-checks-grid"></i> Select Multiple</button>
            @endif
            @if(auth()->user()->isSuperAdmin())
                <button type="button" id="delete-all-btn" class="cas-btn" style="display:inline-flex;align-items:center;gap:5px;padding:8px 16px;border-radius:8px;font-size:12px;font-weight:700;border:none;cursor:pointer;background:#ffffff;color:#334155;border:1px solid #cbd5e1;transition:all .15s;" onclick="document.getElementById(\'delete-all-overlay\').classList.add(\'active\')"><i class="bi bi-trash3-fill"></i> Delete All Data</button>
            @endif
        </div>
    </div>';
    
    $content = str_replace($formMatch[1], $newFormInner, $content);
    file_put_contents($file, $content);
    echo "All Data updated\n";
}
