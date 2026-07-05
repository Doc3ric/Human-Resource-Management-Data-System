$laws = @("R.A. 9470", "R.A. 10173", "R.A. 8792", "R.A. 7160", "R.A. 11032", "2025 ORAOHRA", "CSC Res. 2500358", "MC No. 08 s.2025", "2025 RRACCS", "CSC Res. 2500357", "CSC MC No. 41 s.1998", "DOLE-CSC", "DBM-CSC", "CSC MC No. 03 s.2001", "R.A. 7041", "R.A. 6713", "NPC Circular 16-01", "CS Form No. 11 s.2025")

foreach ($law in $laws) {
    $count = (Select-String -Path "resources/views\*\*.blade.php", "app\*\*.php" -Pattern $law -SimpleMatch).Count
    if ($count -eq 0) {
        Write-Host "MISSING CITATION: $law"
    } else {
        Write-Host "FOUND: $law ($count matches)"
    }
}
