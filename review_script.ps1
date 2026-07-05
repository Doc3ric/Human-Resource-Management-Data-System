$ErrorActionPreference = "Stop"

function Find-HardcodedHex {
    Write-Host "=== HARDCODED HEX (Excluding SVG/Paths) ==="
    Get-ChildItem -Path "resources/views", "public" -Recurse -Include *.blade.php, *.css | ForEach-Object {
        $matches = Select-String -Path $_.FullName -Pattern "#[0-9a-fA-F]{3,6}\b"
        foreach ($match in $matches) {
            $line = $match.Line.Trim()
            if ($_.Name -ne "theme.css" -and $line -notmatch "<path" -and $line -notmatch "<svg" -and $line -notmatch "<g" -and $line -notmatch "<rect" -and $line -notmatch "<circle" -and $line -notmatch "<polygon") {
                Write-Host "$($_.Name):$($match.LineNumber): $line"
            }
        }
    }
}

function Find-Deletes {
    Write-Host "`n=== DELETE/DROP STATEMENTS ==="
    Get-ChildItem -Path "app", "database", "routes" -Recurse -Include *.php | ForEach-Object {
        $matches = Select-String -Path $_.FullName -Pattern "->delete\(", "->forceDelete\(", "Schema::drop", "DB::statement\('DROP"
        foreach ($match in $matches) {
            # exclude migrations down() methods usually
            if ($_.Name -notmatch "create_" -or $match.Line -notmatch "Schema::drop") {
                Write-Host "$($_.Name):$($match.LineNumber): $($match.Line.Trim())"
            }
        }
    }
}

function Find-MissingAuditLogs {
    Write-Host "`n=== POTENTIAL MISSING AUDIT LOGS ==="
    Get-ChildItem -Path "app/Http/Controllers" -Recurse -Filter *.php | ForEach-Object {
        $content = Get-Content $_.FullName -Raw
        if ($content -match "function (store|update|destroy)") {
            if ($content -notmatch "ActivityLog::create") {
                Write-Host "$($_.Name) has store/update/destroy but no ActivityLog::create"
            }
        }
    }
}

function Find-AES256 {
    Write-Host "`n=== AES-256 ENCRYPTION (SPI) ==="
    Get-ChildItem -Path "app/Models" -Recurse -Filter *.php | ForEach-Object {
        $content = Get-Content $_.FullName -Raw
        if ($content -match "Crypt::" -or $content -match "encrypted") {
            Write-Host "$($_.Name) uses encryption"
        }
    }
}

Find-HardcodedHex
Find-Deletes
Find-MissingAuditLogs
Find-AES256
