<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function escape($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function app_log_error($message, $context = []) {
    try {
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }
        $logFile = $logDir . '/app.log';
        $line = date('c') . ' ' . $message;
        if (!empty($context)) {
            $line .= ' ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        $line .= PHP_EOL;
        @error_log($line, 3, $logFile);
    } catch (Throwable $e) {
        // swallow
    }
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function set_flash($key, $message) {
    $_SESSION['flash'][$key] = $message;
}

function get_flash($key) {
    if (!empty($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}

function require_login($redirectTo = null) {
    if (empty($_SESSION['user_id'])) {
        set_flash('info', 'Vui lòng đăng nhập để tiếp tục.');
        if ($redirectTo === null) {
            $redirectTo = (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/');
        }
        $next = urlencode($redirectTo);
        redirect(base_url('login.php?next=' . $next));
    }
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Labels
function status_label($code) {
    switch ($code) {
        case 'pending_payment': return 'Chờ thanh toán';
        case 'confirmed': return 'Đã xác nhận';
        case 'cancelled': return 'Đã hủy';
        default: return (string)$code;
    }
}

// Basic input helpers
function post($key, $default = null) {
    return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}

function get($key, $default = null) {
    return isset($_GET[$key]) ? trim($_GET[$key]) : $default;
}

// URL helpers
function app_base_path_prefix() {
    $doc = rtrim(str_replace('\\','/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    $root = str_replace('\\','/', realpath(__DIR__ . '/..'));
    if ($doc && strpos($root, $doc) === 0) {
        $prefix = substr($root, strlen($doc));
    } else {
        $prefix = '';
    }
    if ($prefix === '') return '';
    return (strpos($prefix, '/') === 0) ? $prefix : ('/' . $prefix);
}

function base_url($path = '') {
    $base = app_base_path_prefix();
    if ($path === '' || $path === null) return $base ?: '/';
    if ($path[0] === '?') return ($base ?: '/') . $path; // query append
    return rtrim($base, '/') . '/' . ltrim((string)$path, '/');
}

function asset_url($path) {
    $url = base_url($path);
    // append version based on filemtime
    $fs = realpath(__DIR__ . '/..' . '/' . ltrim($path, '/'));
    if ($fs && is_file($fs)) {
        $ver = @filemtime($fs) ?: time();
        $url .= (strpos($url, '?') === false ? '?' : '&') . 'v=' . $ver;
    }
    return $url;
}

?>
