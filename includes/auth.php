<?php
/**
 * Session bootstrap + authentication / authorization / CSRF helpers.
 * Every protected page includes this once instead of repeating
 * session_start() + role checks inline.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
    ]);
}

/** Redirect to login if nobody is authenticated. */
function require_login(): void {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

/** Redirect to login if not authenticated, hard-stop if not Admin. */
function require_admin(): void {
    require_login();
    if (($_SESSION['role'] ?? '') !== 'Admin') {
        http_response_code(403);
        die("Access Denied! Admin Only.");
    }
}

function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function is_admin(): bool {
    return ($_SESSION['role'] ?? '') === 'Admin';
}

/** Returns the current CSRF token, generating one if needed. */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Hidden input to drop inside every state-changing <form>. */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

/** Call at the top of every POST handler. Halts on failure. */
function verify_csrf(): void {
    $sent = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $sent)) {
        http_response_code(400);
        die("Your session expired or the form was resubmitted. Please go back and try again.");
    }
}
