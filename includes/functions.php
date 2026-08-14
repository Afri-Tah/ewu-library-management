<?php
/** Shortcut for safe HTML output (prevents XSS from stored/user data). */
function h($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
