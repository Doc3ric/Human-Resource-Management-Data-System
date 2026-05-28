use std::{path::PathBuf, sync::Mutex};
use tauri::{
    menu::{MenuBuilder, MenuItemBuilder, PredefinedMenuItem},
    tray::{MouseButton, MouseButtonState, TrayIconBuilder, TrayIconEvent},
    Manager,
};

// ─── Constants ────────────────────────────────────────────────────────────────
const SERVER_DIR: &str = "C:\\CSC PLANTILLA\\CSC_PLANTILLA";
const SERVER_PORT: u16 = 8000;

#[cfg(target_os = "windows")]
const CREATE_NO_WINDOW: u32 = 0x08000000;

// ─── Managed State ────────────────────────────────────────────────────────────
struct ServerProcess(Mutex<Option<std::process::Child>>);

// ─── Helpers ──────────────────────────────────────────────────────────────────
fn is_server_running() -> bool {
    std::net::TcpStream::connect(format!("127.0.0.1:{}", SERVER_PORT)).is_ok()
}

/// Scan Laragon's php dir for any php.exe, fallback to PATH "php".
fn find_php() -> String {
    // Try PATH first
    let probe = std::process::Command::new("php").arg("-r").arg("echo 1;").output();
    if probe.map(|o| o.status.success()).unwrap_or(false) {
        return "php".to_string();
    }
    // Scan common Laragon path
    let laragon_php = PathBuf::from(r"C:\laragon\bin\php");
    if laragon_php.exists() {
        if let Ok(entries) = std::fs::read_dir(&laragon_php) {
            let mut exes: Vec<PathBuf> = entries
                .flatten()
                .filter(|e| e.file_type().map(|t| t.is_dir()).unwrap_or(false))
                .map(|e| e.path().join("php.exe"))
                .filter(|p| p.exists())
                .collect();
            exes.sort();
            if let Some(exe) = exes.last() {
                return exe.to_string_lossy().to_string();
            }
        }
    }
    "php".to_string()
}


// ─── Tauri Commands ───────────────────────────────────────────────────────────

/// Called from main.js to close splash + reveal main window.
#[tauri::command]
async fn close_splashscreen(app: tauri::AppHandle) {
    if let Some(splash) = app.get_webview_window("splashscreen") {
        let _ = splash.close();
    }
    if let Some(main) = app.get_webview_window("main") {
        let _ = main.show();
        let _ = main.set_focus();
    }
}

/// Called from main.js when server isn't up — tries to spawn `php artisan serve`.
#[tauri::command]
async fn start_server(state: tauri::State<'_, ServerProcess>) -> Result<String, String> {
    if is_server_running() {
        return Ok("already_running".to_string());
    }

    let php = find_php();
    let mut cmd = std::process::Command::new(&php);
    cmd.args(["artisan", "serve", "--host=0.0.0.0", "--port=8000"])
        .current_dir(SERVER_DIR);

    #[cfg(target_os = "windows")]
    {
        use std::os::windows::process::CommandExt;
        cmd.creation_flags(CREATE_NO_WINDOW);
    }

    match cmd.spawn() {
        Ok(child) => {
            *state.0.lock().unwrap() = Some(child);
            Ok("started".to_string())
        }
        Err(e) => Err(format!("Failed to start server: {}", e)),
    }
}

/// Reload the main window's current page.
#[tauri::command]
async fn reload_app(app: tauri::AppHandle) {
    if let Some(window) = app.get_webview_window("main") {
        let _ = window.eval("window.location.reload()");
    }
}

// ─── App Entry Point ──────────────────────────────────────────────────────────
#[cfg_attr(mobile, tauri::mobile_entry_point)]
pub fn run() {
    tauri::Builder::default()
        // ── Managed state
        .manage(ServerProcess(Mutex::new(None)))
        // ── Plugins
        .plugin(tauri_plugin_opener::init())
        .plugin(
            tauri_plugin_window_state::Builder::new()
                .with_state_flags(tauri_plugin_window_state::StateFlags::all())
                .build(),
        )
        // ── Setup hook
        .setup(|app| {
            // ── Download handler: saves to ~/Downloads, opens folder when done
            if let Some(_main_win) = app.get_webview_window("main") {
                // To be implemented: initialization script injection
            }

            // ── System Tray
            let open_i  = MenuItemBuilder::with_id("open",   "Open CSC Plantilla").build(app)?;
            let reload_i = MenuItemBuilder::with_id("reload", "Reload App").build(app)?;
            let sep      = PredefinedMenuItem::separator(app)?;
            let quit_i  = MenuItemBuilder::with_id("quit",   "Quit").build(app)?;

            let tray_menu = MenuBuilder::new(app)
                .items(&[&open_i, &reload_i, &sep, &quit_i])
                .build()?;

            let _tray = TrayIconBuilder::new()
                .icon(app.default_window_icon().unwrap().clone())
                .menu(&tray_menu)
                .tooltip("CSC Plantilla — PHRMO Portal")
                .show_menu_on_left_click(false)
                // Left-click the tray icon → show window
                .on_tray_icon_event(|tray, event| {
                    if let TrayIconEvent::Click {
                        button: MouseButton::Left,
                        button_state: MouseButtonState::Up,
                        ..
                    } = event
                    {
                        let app = tray.app_handle();
                        if let Some(w) = app.get_webview_window("main") {
                            let _ = w.show();
                            let _ = w.unminimize();
                            let _ = w.set_focus();
                        }
                    }
                })
                // Tray menu item clicks
                .on_menu_event(|app, event| match event.id.as_ref() {
                    "open" => {
                        if let Some(w) = app.get_webview_window("main") {
                            let _ = w.show();
                            let _ = w.unminimize();
                            let _ = w.set_focus();
                        }
                    }
                    "reload" => {
                        if let Some(w) = app.get_webview_window("main") {
                            let _ = w.eval("window.location.reload()");
                        }
                    }
                    "quit" => {
                        // Kill the server process we may have spawned
                        if let Some(state) = app.try_state::<ServerProcess>() {
                            if let Ok(mut guard) = state.0.lock() {
                                if let Some(child) = guard.as_mut() {
                                    let _ = child.kill();
                                }
                            }
                        }
                        app.exit(0);
                    }
                    _ => {}
                })
                .build(app)?;

            Ok(())
        })
        // ── Minimize to tray on window close (instead of quitting)
        .on_window_event(|window, event| {
            if window.label() == "main" {
                if let tauri::WindowEvent::CloseRequested { api, .. } = event {
                    let _ = window.hide();
                    api.prevent_close();
                }
            }
        })
        .invoke_handler(tauri::generate_handler![
            close_splashscreen,
            start_server,
            reload_app,
        ])
        .run(tauri::generate_context!())
        .expect("error while running tauri application");
}
