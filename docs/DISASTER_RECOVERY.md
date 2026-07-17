# Disaster Recovery — Backup & Restore to New Hardware

This document describes how HRDMS recovers from a total loss of the
current server (hardware failure, disk failure, theft, etc.) onto a
different PC or server.

## What is backed up, and where

| Component | Command | Cadence | Retention | Destination |
|---|---|---|---|---|
| Database (all tables) | `backup:database` | Every 6 hours | Last 14 | Local only (see off-site status below) |
| File storage (IDCC documents, RACCS attachments, reports) | `backup:files` | Daily at 2:00 AM | Last 5 | Local only (see off-site status below) |
| Encryption key + Google service account key | `backup:export-key` | Manual, on demand | N/A | Wherever you copy it — must be off this machine |

### Off-site copy: not active yet

Both backup commands support `--driver=google_drive` (kept as an available
option, not the default), but the currently configured Google service
account **cannot actually store data**: Google rejects file uploads from a
bare service account with `"Service Accounts do not have storage quota"`.
This is a Google API limitation, not a settings mistake — folder/metadata
operations succeed, but real file content is rejected. Fixing this for real
requires one of:

- A paid Google Workspace subscription with **Shared Drives** enabled,
  with `GOOGLE_DRIVE_FOLDER_ID` pointing into that Shared Drive (the
  upload code already passes `supportsAllDrives: true` in anticipation
  of this), or
- OAuth-delegating to a real human Google account with its own storage
  (requires one-time interactive browser consent — can't be scripted), or
- A different off-site target entirely: a local network share/NAS (keeps
  SPI-bearing data on-premise, no cost, no internet dependency), or a
  conventional cloud object store (Backblaze B2, AWS S3) using ordinary
  access keys, which don't have this service-account-specific restriction.

Until one of these is set up, **backups exist only on this one machine**
and will not survive a hardware failure. This is the single biggest gap
in disaster-recovery readiness today.

Both scheduled commands are registered in `routes/console.php` and run via
the Windows Scheduled Task **"HRDMS Laravel Scheduler"**, which invokes
`php artisan schedule:run` every minute. That task runs under an
**interactive login** — it will not fire if the server is rebooted and
left at a locked/logged-out screen. Check `Get-ScheduledTaskInfo -TaskName
"HRDMS Laravel Scheduler"` periodically to confirm `LastTaskResult` is 0
and `LastRunTime` is recent.

Application code is **not** part of any backup command — it lives in the
GitHub repository (`Doc3ric/Human-Resource-Management-Data-System`) and is
recovered via `git clone`.

## The one thing that makes everything else useless if skipped

**`APP_KEY` is not included in the database or file backups.** It only
lives in `.env` on the live machine. Every backup is encrypted with it —
lose `.env` without a separate copy of `APP_KEY`, and every backup file
you have, however carefully kept, becomes permanently unreadable. This is
also the key that decrypts SPI fields encrypted at the application layer.

**Action required, now and after any credential change:**

```
php artisan backup:export-key
```

This prompts for a passphrase (not `APP_KEY` itself — a separate
passphrase you choose), then writes a single encrypted file containing
`.env`'s full contents plus the Google Drive service account JSON.

1. Copy the resulting file to a USB drive or other medium **not** on this
   machine.
2. Write the passphrase down somewhere separate from the file (e.g., the
   PHRMO office safe).
3. Re-run this command whenever `.env` or the Google service account key
   changes — old exports become stale.

Without this step, steps 4–5 of the restore procedure below cannot be
completed.

## Restoring onto a new machine

1. **Provision the new machine.** Install Laragon (or PHP 8.3 + MySQL 8.x +
   Apache/Nginx directly), matching the current stack.

2. **Get the code.**
   ```
   git clone https://github.com/Doc3ric/Human-Resource-Management-Data-System.git
   composer install
   npm install && npm run build
   ```

3. **Restore the encryption key package** (from the USB/offline copy made
   by `backup:export-key`):
   ```
   php artisan backup:import-key /path/to/hrdms_key_export_*.enc
   ```
   Enter the passphrase when prompted. This writes `.env.restored` and
   `google-sa-key.restored.json` into the current directory. Review both,
   then:
   - Rename `.env.restored` to `.env`, and update `DB_HOST`, `DB_DATABASE`,
     and any machine-specific paths for the new server.
   - Move `google-sa-key.restored.json` to wherever the new `.env`'s
     `GOOGLE_SA_KEY_PATH` points.

4. **Restore the database.** Off-site copy is not active yet (see above),
   so the encrypted backup file itself must be recovered by some other
   means — e.g. periodically copying `storage/app/private/backups/` to a
   USB drive/NAS alongside the key export, until a real off-site target is
   configured. Once the `.sql.enc` file and its matching `backup_logs` row
   exist on the new machine, log in as a super admin, go to
   **Backup & Recovery**, click **Restore** next to it, and type `CONFIRM`.
   Alternatively, restore directly from the CLI by decrypting a backup
   file and piping it into MySQL — see `DatabaseBackupService::decryptData()`
   for the exact AES-256-GCM parameters if scripting this.

5. **Restore file storage.** Same flow as step 4, but for the most recent
   `backup:files` archive — the **Restore** action on a Files-scope backup
   row extracts the zip back into `storage/app/private/idcc` and
   `storage/app/reports`.

6. **Re-register the Windows Scheduled Task** on the new machine so
   backups resume automatically:
   ```
   schtasks /Create /TN "HRDMS Laravel Scheduler" /TR "\"C:\path\to\php.exe\" \"C:\path\to\artisan\" schedule:run" /SC MINUTE /MO 1 /RL LIMITED
   ```
   (Adjust paths. Run once manually first to confirm no errors.)

7. **Verify.** Log in, confirm a sample of employee records, leave
   applications, and IDCC documents render correctly. Run
   `php artisan backup:database --driver=local` once to confirm the new
   machine can produce a fresh backup before considering the migration
   complete.

## Known limitations (as of this writing)

- **No off-site copy is active** (see above) — this is the critical gap.
  Everything currently lives only on this one machine's disk.
- The Windows Scheduled Task requires an active login session — it is not
  a true background service. A locked/logged-out server silently stops
  producing new backups until someone logs back in.
- There is no automated way to move backup files off this machine; until
  an off-site target is chosen and wired in, this has to be done manually
  (e.g., periodically copying `storage/app/private/backups/` to a USB
  drive or NAS).
- File-storage backups (`backup:files`) use streaming AES-256-CBC +
  HMAC-SHA256 rather than the database backup's AES-256-GCM, specifically
  so encrypting a 700MB+ archive doesn't require holding multiple copies
  of it in memory at once (this server has 7.7GB total RAM) — see
  `DatabaseBackupService::streamEncryptFile()`/`streamDecryptFile()`. The
  database dump itself (currently ~275MB) still uses the original
  in-memory GCM approach; if the database grows several times larger,
  the same memory risk could resurface there too.
