<?php
/** Shortcut for safe HTML output (prevents XSS from stored/user data). */
function h($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/** Fine rate in Taka per day late. Single source of truth — used at
 *  return time (return_book.php) AND for live "accruing" estimates
 *  shown before a book is returned. */
if (!defined('FINE_PER_DAY')) {
    define('FINE_PER_DAY', 20);
}

/**
 * Days between $due_date and $as_of (defaults to today), clamped to >= 0.
 * This is the single place "how late is it" gets computed, so the
 * return-time fine and the live "accruing" estimate can never drift apart.
 */
function days_late(string $due_date, ?string $as_of = null): int {
    $as_of = $as_of ?? date('Y-m-d');
    $diff  = (int) floor((strtotime($as_of) - strtotime($due_date)) / 86400);
    return max(0, $diff);
}

/**
 * The status a borrow record should actually be showing right now.
 * The `status` column in the DB only ever holds 'Borrowed' or 'Returned'
 * (nothing sets 'Overdue' on write), so this derives the real-time
 * status for display without needing a cron job to keep the column in sync.
 */
function effective_borrow_status(string $status, string $due_date): string {
    if ($status === 'Borrowed' && days_late($due_date) > 0) {
        return 'Overdue';
    }
    return $status;
}
