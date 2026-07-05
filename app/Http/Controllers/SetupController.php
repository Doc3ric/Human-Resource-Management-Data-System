<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\Rules\Password;
use App\Models\User;

class SetupController extends Controller
{
    // Guard: redirect to dashboard if any user already exists
    public function guard()
    {
        if (User::exists()) {
            return redirect()->route('dashboard');
        }
        return null;
    }

    public function index()
    {
        if ($redir = $this->guard()) return $redir;
        return view('setup.wizard', ['step' => 1]);
    }

    public function step(int $step)
    {
        if ($redir = $this->guard()) return $redir;
        return view('setup.wizard', compact('step'));
    }

    // Step 1 → 2: system requirements check (read-only, just display)
    public function checkRequirements()
    {
        if ($redir = $this->guard()) return $redir;
        $checks = $this->runRequirements();
        return view('setup.wizard', ['step' => 2, 'checks' => $checks]);
    }

    // Step 2 → 3: database connection test
    public function testDatabase(Request $request)
    {
        if ($redir = $this->guard()) return $redir;
        $request->validate([
            'db_host'     => 'required|string',
            'db_port'     => 'required|numeric',
            'db_name'     => 'required|string',
            'db_user'     => 'required|string',
            'db_password' => 'nullable|string',
        ]);

        $ok = false;
        $msg = '';
        try {
            $dsn = "mysql:host={$request->db_host};port={$request->db_port};dbname={$request->db_name};charset=utf8mb4";
            new \PDO($dsn, $request->db_user, $request->db_password ?? '', [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
            $ok = true;
            $msg = 'Connection successful.';
        } catch (\PDOException $e) {
            $msg = 'Connection failed: ' . $e->getMessage();
        }

        session(['setup_db' => $request->only('db_host','db_port','db_name','db_user','db_password')]);

        return view('setup.wizard', ['step' => 3, 'dbOk' => $ok, 'dbMsg' => $msg, 'dbData' => $request->all()]);
    }

    // Step 3 → 4: run migrations
    public function runMigrations()
    {
        if ($redir = $this->guard()) return $redir;
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();
            $ok = true;
        } catch (\Throwable $e) {
            $output = $e->getMessage();
            $ok = false;
        }
        return view('setup.wizard', ['step' => 4, 'migrateOk' => $ok, 'migrateOutput' => $output]);
    }

    // Step 4 → 5: seed optional data
    public function seedData(Request $request)
    {
        if ($redir = $this->guard()) return $redir;
        return view('setup.wizard', ['step' => 5]);
    }

    // Step 5 → 6: create super admin account
    public function createAdmin(Request $request)
    {
        if ($redir = $this->guard()) return $redir;

        $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'password'              => ['required', 'confirmed', Password::min(10)->letters()->numbers()->symbols()],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'super_admin',
        ]);

        return view('setup.wizard', ['step' => 6, 'admin' => $user]);
    }

    // ── Private helpers ────────────────────────────────────────────────────
    private function runRequirements(): array
    {
        return [
            ['label' => 'PHP Version (≥ 8.1)',       'ok' => version_compare(PHP_VERSION, '8.1.0', '>='), 'value' => PHP_VERSION],
            ['label' => 'OpenSSL Extension',          'ok' => extension_loaded('openssl'),  'value' => extension_loaded('openssl') ? 'Enabled' : 'Missing'],
            ['label' => 'PDO MySQL Extension',        'ok' => extension_loaded('pdo_mysql'),'value' => extension_loaded('pdo_mysql') ? 'Enabled' : 'Missing'],
            ['label' => 'mbstring Extension',         'ok' => extension_loaded('mbstring'), 'value' => extension_loaded('mbstring') ? 'Enabled' : 'Missing'],
            ['label' => 'ZIP Extension',              'ok' => extension_loaded('zip'),       'value' => extension_loaded('zip') ? 'Enabled' : 'Missing'],
            ['label' => 'storage/ Writable',          'ok' => is_writable(storage_path()),  'value' => is_writable(storage_path()) ? 'Writable' : 'Not Writable'],
            ['label' => 'bootstrap/cache/ Writable',  'ok' => is_writable(base_path('bootstrap/cache')), 'value' => is_writable(base_path('bootstrap/cache')) ? 'Writable' : 'Not Writable'],
            ['label' => '.env File Exists',           'ok' => file_exists(base_path('.env')), 'value' => file_exists(base_path('.env')) ? 'Found' : 'Missing'],
        ];
    }
}
