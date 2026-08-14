# Security & Structure Notes

This project was hardened from an initial working prototype. Summary of what
changed and why — useful for the report's "Tools & Technologies" / "Conclusion"
sections.

## Fixed
1. **SQL injection** — every query that touched user input (`$_GET`, `$_POST`)
   now uses `mysqli` prepared statements with bound parameters, instead of
   interpolating strings directly into SQL. `delete_book.php` in particular
   used to run `DELETE FROM books WHERE book_id='$id'` with the raw GET value
   pasted straight into the query — that is now `intval()` + a bound parameter.
2. **Plaintext passwords** — `users.password` now stores a bcrypt hash
   (`password_hash()`/`password_verify()` in PHP terms) instead of the raw
   password. `login.php` uses `password_verify()`.
3. **Missing authorization** — `pay_fine.php` had **no login/role check at
   all**, meaning anyone with the URL could mark any fine as paid. It now
   requires an authenticated Admin, same as the other write actions.
4. **Exposed personal data** — `view_members.php` had no login check, so
   member names, phone numbers, and emails were publicly viewable. It now
   requires Admin login.
5. **Direct URL access ("forced browsing")** — `view_books.php` used to be
   reachable by anyone who typed its URL, logged in or not, because it never
   called `require_login()`. Every page (not just admin-only ones) must now
   have an active session; typing a page's URL directly redirects an
   unauthenticated visitor to `login.php` instead of rendering the page.
   A root-level `.htaccess` also blocks direct download of non-PHP files
   (`database.sql`, `README.md`, `SECURITY.md`, `.gitignore`, ...) so they
   can't be fetched straight from the browser even though they sit in the
   web root.
6. **CSRF** — all state-changing actions (add/edit/delete book & member,
   borrow, return, pay fine) are POST-only and require a per-session CSRF
   token (`includes/auth.php: csrf_field()` / `verify_csrf()`). Delete/Return/
   Pay actions were previously plain `<a href="...">` GET links, which are
   forgeable from any external page.
7. **XSS** — all dynamic output goes through `h()`
   (`htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`) before being echoed into HTML.
8. **Race conditions on stock count** — `borrow_book.php` wraps the
   "check available copies → insert borrow → decrement copies" sequence in a
   transaction with `SELECT ... FOR UPDATE`, so two simultaneous borrow
   requests can't both succeed when only one copy is left.
9. **Error handling** — `mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT)`
   is enabled so DB errors raise exceptions instead of failing silently;
   raw driver errors are no longer echoed straight to the browser.

## Structural changes
- Centralized `require_login()` / `require_admin()` / CSRF helpers in
  `includes/auth.php` instead of repeating the same session/role block at the
  top of every file.
- Centralized DB connection + charset (`utf8mb4`) in `includes/db_connect.php`.
- `includes/.htaccess` blocks direct web access to the `includes/` folder.
- Session cookie is set `HttpOnly` + `SameSite=Lax`; `session_regenerate_id()`
  is called on successful login to prevent session fixation.

## Known simplifications (acceptable for a course project)
- HTTPS/TLS is a deployment concern, not something the app enforces itself.
- No rate limiting on login attempts (would need a persistence layer for
  attempt counters).
- No password-reset flow; accounts are provisioned via the seed data / an
  admin adding a `members` row against an existing `users` row.
