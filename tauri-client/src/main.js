/**
 * HDMS- Human Resource Data Management System — Tauri Desktop Client
 * main.js: Server health check with retry, real splash events, and zoom persistence.
 */

const { invoke }  = window.__TAURI__.core;
const { emit }    = window.__TAURI__.event;

// ─── Config ───────────────────────────────────────────────────────────────────
const MAX_ATTEMPTS  = 15;        // max retry attempts before showing fallback
const RETRY_DELAY   = 3000;      // ms between retries
const FETCH_TIMEOUT = 6000;      // ms before a single fetch is considered timed out
const HEALTH_PATH   = '/';       // path to ping on the Laravel server

// ─── Helpers ──────────────────────────────────────────────────────────────────

/**
 * Emit a status message to the splash screen window.
 * Fails silently if the splash is already closed.
 */
async function postStatus(message, attempt, max) {
    try {
        await emit('splash:status', { message, attempt, max });
    } catch (_) { /* splash already closed — ignore */ }
}

/**
 * Properly check if the server is responding.
 * Uses AbortController for timeout so we get a real error on failure,
 * unlike `mode:'no-cors'` which always "succeeds".
 */
async function pingServer(url) {
    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), FETCH_TIMEOUT);
    try {
        // A real HTTP request — any non-network-error response means server is up
        await fetch(url + HEALTH_PATH, {
            signal: controller.signal,
            cache:  'no-store',
            mode:   'no-cors',   // We still need no-cors for cross-origin Laravel URLs,
                                  // but we catch AbortError (timeout) & TypeError (refused).
        });
        clearTimeout(timer);
        return true;
    } catch (err) {
        clearTimeout(timer);
        // AbortError = timeout, TypeError = connection refused / network error
        return false;
    }
}

/** Restore saved zoom preference */
async function restoreZoom() {
    try {
        const zoom = parseFloat(localStorage.getItem('app-zoom') || '1.0');
        if (zoom && zoom !== 1.0) {
            await window.__TAURI__.webview.getCurrentWebview().setZoom(zoom);
        }
    } catch (_) { /* zoom API may not be available on older WRY */ }
}

/** Save zoom preference on Ctrl+scroll */
function setupZoomPersistence() {
    window.addEventListener('wheel', (e) => {
        if (e.ctrlKey) {
            // Debounce: save after wheel stops for 300ms
            clearTimeout(window._zoomSaveTimer);
            window._zoomSaveTimer = setTimeout(async () => {
                try {
                    const z = await window.__TAURI__.webview.getCurrentWebview().getZoom();
                    localStorage.setItem('app-zoom', String(z));
                } catch (_) {}
            }, 300);
        }
    }, { passive: true });
}

// ─── Main Flow ────────────────────────────────────────────────────────────────
async function main() {
    const url = localStorage.getItem('serverUrl') || 'http://10.40.2.59:8000';

    await restoreZoom();
    setupZoomPersistence();

    let serverStartAttempted = false;

    for (let attempt = 1; attempt <= MAX_ATTEMPTS; attempt++) {
        // ── Update splash with real progress ──────────────────────────────────
        if (attempt === 1) {
            await postStatus('Connecting to server…', attempt, MAX_ATTEMPTS);
        } else {
            await postStatus(
                `Waiting for server… (${attempt} / ${MAX_ATTEMPTS})`,
                attempt,
                MAX_ATTEMPTS
            );
        }

        const alive = await pingServer(url);

        if (alive) {
            // ── Success ───────────────────────────────────────────────────────
            await postStatus('Server ready! Loading application…', MAX_ATTEMPTS, MAX_ATTEMPTS);
            await new Promise(r => setTimeout(r, 600)); // let splash read the message
            await invoke('close_splashscreen');
            window.location.replace(url);
            return;
        }

        // ── Server not up yet ─────────────────────────────────────────────────
        if (!serverStartAttempted) {
            serverStartAttempted = true;
            await postStatus('Server offline — attempting auto-start…', attempt, MAX_ATTEMPTS);
            try {
                const result = await invoke('start_server');
                if (result === 'started') {
                    await postStatus('PHP server started — waiting for it to be ready…', attempt, MAX_ATTEMPTS);
                    // Give the server a moment to bind before we retry
                    await new Promise(r => setTimeout(r, 4000));
                    continue;
                }
            } catch (err) {
                // Auto-start failed (PHP not found, wrong path, etc.) — keep retrying
                await postStatus(`Auto-start failed: ${err}. Retrying…`, attempt, MAX_ATTEMPTS);
            }
        }

        if (attempt < MAX_ATTEMPTS) {
            await postStatus(
                `Server not responding. Retry in ${RETRY_DELAY / 1000}s… (${attempt}/${MAX_ATTEMPTS})`,
                attempt,
                MAX_ATTEMPTS
            );
            await new Promise(r => setTimeout(r, RETRY_DELAY));
        }
    }

    // ── All attempts exhausted ────────────────────────────────────────────────
    await postStatus('Could not reach server. Showing configuration…', MAX_ATTEMPTS, MAX_ATTEMPTS);
    await new Promise(r => setTimeout(r, 800));
    await invoke('close_splashscreen');
    window.location.replace('fallback.html');
}

main();
