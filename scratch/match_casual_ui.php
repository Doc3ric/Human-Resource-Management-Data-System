<?php

function getCasualFormStyle() {
    return 'class="cas-filter" style="display: flex; flex-direction: column; gap: 12px; align-items: stretch; padding: 14px 18px; margin-bottom: 16px; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, .04);"';
}

function processJobOrders() {
    $file = 'c:/laragon/www/Human-Resource-Management-Data-System/resources/views/job-orders/index.blade.php';
    if (!file_exists($file)) return;
    $content = file_get_contents($file);
    
    // Convert filter form to cas-filter
    if (preg_match('/<form[^>]*id="jo-filter-form"[^>]*>(.*?)<\/form>/s', $content, $formMatch)) {
        $formInner = $formMatch[1];
        
        $newFormInner = '
        <div ' . getCasualFormStyle() . '>
            <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                <input type="text" name="search" id="jo-search" value="{{ request(\'search\') }}" class="cas-input" placeholder="🔍 Search Name / Position...">
                
                <select name="charges" id="jo-charges" class="cas-select">
                    <option value="">— All Charges —</option>
                    @foreach($chargesList as $c)
                        <option value="{{ $c }}" {{ request(\'charges\') == $c ? \'selected\' : \'\' }}>{{ $c }}</option>
                    @endforeach
                </select>
                
                <select name="office" id="jo-office" class="cas-select">
                    <option value="">— All Offices —</option>
                    @foreach($offices as $o)
                        <option value="{{ $o }}" {{ request(\'office\') == $o ? \'selected\' : \'\' }}>{{ $o }}</option>
                    @endforeach
                </select>
                
                <select name="detail" id="jo-detail" class="cas-select">
                    <option value="">— Detailed / Reassigned —</option>
                    @foreach($detailList as $d)
                        <option value="{{ $d }}" {{ request(\'detail\') == $d ? \'selected\' : \'\' }}>{{ $d }}</option>
                    @endforeach
                </select>
                
                <button type="submit" class="cas-btn primary" id="btn-apply-filter"><i class="bi bi-search"></i> Search</button>
                <a href="{{ route(\'job-orders.index\') }}" class="cas-btn reset"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
            </div>
            
            <!-- Bottom Row: Actions (Far Left) -->
            <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center; justify-content: flex-start;">
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                    <a href="{{ route(\'job-orders.create\') }}" class="cas-btn add" id="btn-create-jo"><i class="bi bi-plus-lg"></i> Add Record</a>
                    <button type="button" class="cas-btn import" id="btn-open-import" onclick="document.getElementById(\'import-modal-bg\').classList.add(\'open\')"><i class="bi bi-upload"></i> Import Excel</button>
                @endif
                <a href="{{ route(\'job-orders.import.history\') }}" class="cas-btn" style="background:#ffffff;color:#334155;border:1px solid #cbd5e1;"><i class="bi bi-clock-history"></i> Import History</a>
                <button type="button" class="cas-btn" style="background:#ffffff;color:#334155;border:1px solid #cbd5e1;cursor:pointer;" onclick="new bootstrap.Modal(document.getElementById(\'exportModal\')).show()"><i class="bi bi-file-earmark-arrow-down-fill"></i> Export Settings</button>
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                    <button type="button" class="cas-btn" id="toggle-select-multiple" style="background:#ffffff;color:#334155;border:1px solid #cbd5e1;"><i class="bi bi-ui-checks-grid"></i> Select Multiple</button>
                @endif
                @if(auth()->user()->isSuperAdmin())
                    <button type="button" id="delete-all-btn" class="cas-btn" style="display:inline-flex;align-items:center;gap:5px;padding:8px 16px;border-radius:8px;font-size:12px;font-weight:700;border:none;cursor:pointer;background:#ffffff;color:#334155;border:1px solid #cbd5e1;transition:all .15s;" onclick="document.getElementById(\'jo-delete-all-overlay\').classList.add(\'active\')"><i class="bi bi-trash3-fill"></i> Delete All Data</button>
                @endif
            </div>
        </div>';
        
        $content = str_replace($formMatch[1], $newFormInner, $content);
        
        // We also must remove the old action buttons from jo-action-row!
        if (preg_match('/<div class="jo-action-row">\s*<div class="jo-title">.*?<\/div>\s*<div style="display:flex;gap:8px;flex-wrap:wrap;">(.*?)<\/div>\s*<\/div>/s', $content, $actionMatch)) {
            $replacement = preg_replace('/<div style="display:flex;gap:8px;flex-wrap:wrap;">.*?<\/div>/s', '', $actionMatch[0]);
            $content = str_replace($actionMatch[0], $replacement, $content);
        }
        
        file_put_contents($file, $content);
        echo "Job Orders updated\n";
    }
}

function processPermanent() {
    $file = 'c:/laragon/www/Human-Resource-Management-Data-System/resources/views/permanent/index.blade.php';
    if (!file_exists($file)) return;
    $content = file_get_contents($file);
    
    if (preg_match('/<form[^>]*id="perm-search-form"[^>]*>(.*?)<\/form>/s', $content, $formMatch)) {
        
        $newFormInner = '
        <div ' . getCasualFormStyle() . '>
            <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                <input type="text" name="search" value="{{ request(\'search\') }}" class="cas-input" placeholder="🔍 Search name, position, office…" autocomplete="off">
                
                <select name="office" class="cas-select" style="min-width:200px;">
                    <option value="">— All Offices —</option>
                    @foreach($offices as $office)
                        <option value="{{ $office }}" {{ request(\'office\') === $office ? \'selected\' : \'\' }}>{{ Str::limit($office, 40) }}</option>
                    @endforeach
                </select>

                <select name="status" class="cas-select" style="min-width:140px;">
                    <option value="">— All Status —</option>
                    <option value="P"  {{ request(\'status\') === \'P\'  ? \'selected\' : \'\' }}>Regular</option>
                    <option value="CT" {{ request(\'status\') === \'CT\' ? \'selected\' : \'\' }}>Co-Terminous</option>
                    <option value="E"  {{ request(\'status\') === \'E\'  ? \'selected\' : \'\' }}>Elected</option>
                </select>

                <select name="sex" class="cas-select" style="min-width:110px;">
                    <option value="">— All Genders —</option>
                    <option value="M" {{ request(\'sex\') === \'M\' ? \'selected\' : \'\' }}>Male</option>
                    <option value="F" {{ request(\'sex\') === \'F\' ? \'selected\' : \'\' }}>Female</option>
                </select>
                
                <button type="submit" class="cas-btn primary"><i class="bi bi-search"></i> Search</button>
                <a href="{{ route(\'permanent.index\') }}" class="cas-btn reset"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
            </div>
            
            <!-- Bottom Row: Actions (Far Left) -->
            <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center; justify-content: flex-start;">
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                    <a href="{{ route(\'permanent.create\') }}" class="cas-btn add"><i class="bi bi-plus-lg"></i> Add Record</a>
                @endif
                <a href="{{ route(\'archives.index\') }}" class="cas-btn" style="background:#ffffff;color:#334155;border:1px solid #cbd5e1;"><i class="bi bi-archive-fill"></i> View Archives</a>
                <button type="button" class="cas-btn" style="background:#ffffff;color:#334155;border:1px solid #cbd5e1;gap:5px;" onclick="new bootstrap.Modal(document.getElementById(\'exportModal\')).show()"><i class="bi bi-file-earmark-arrow-down-fill"></i> Export Settings</button>
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin())
                    <button type="button" class="cas-btn" id="toggle-select-multiple" style="background:#ffffff;color:#334155;border:1px solid #cbd5e1;"><i class="bi bi-ui-checks-grid"></i> Select Multiple</button>
                @endif
                @if(auth()->user()->isSuperAdmin())
                    <button type="button" id="perm-delete-all-btn" class="cas-btn" style="display:inline-flex;align-items:center;gap:5px;padding:8px 16px;border-radius:8px;font-size:12px;font-weight:700;border:none;cursor:pointer;background:#ffffff;color:#334155;border:1px solid #cbd5e1;transition:all .15s;" onclick="document.getElementById(\'perm-delete-all-overlay\').classList.add(\'active\')"><i class="bi bi-trash3-fill"></i> Delete All Data</button>
                @endif
            </div>
        </div>';
        
        $content = str_replace($formMatch[1], $newFormInner, $content);
        // Clean up REGULAR to Regular again just in case
        $content = str_replace('>REGULAR<', '>Regular<', $content);
        
        file_put_contents($file, $content);
        echo "Permanent updated\n";
    }
}

function processAllData() {
    $file = 'c:/laragon/www/Human-Resource-Management-Data-System/resources/views/all-data/index.blade.php';
    if (!file_exists($file)) return;
    $content = file_get_contents($file);
    
    if (preg_match('/<form[^>]*id="ad-search-form"[^>]*>(.*?)<\/form>/s', $content, $formMatch)) {
        
        $newFormInner = '
        <div ' . getCasualFormStyle() . '>
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
}

processJobOrders();
processPermanent();
processAllData();
