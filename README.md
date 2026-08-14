# EWU Library Management System

A database-backed Library Management System built for **CSE302 — Database Systems**
(East West University). Manages books, members, borrowing, returning, and fine
collection, with two roles: **Admin** and **Member**.

## Tech Stack
- PHP 8 (procedural, mysqli with prepared statements)
- MySQL / MariaDB (InnoDB, 8 tables, foreign keys enforced)
- Plain HTML forms (no framework) — a simple, functional server-rendered GUI

## Features
- Admin: full CRUD on books and members, borrow/return workflow, fine collection
- Member: view own borrow history and fine status
- Public: read-only book catalog (no login required)
- Session-based auth, bcrypt password hashing, CSRF-protected forms, prepared
  statements throughout (see `SECURITY.md`)

## Setup
1. Create the database and load the schema + seed data:
   ```bash
   mysql -u root -p < database.sql
   ```
2. Copy the project into your web server's document root (e.g. `htdocs/`, `www/`).
3. Edit `includes/db_connect.php` if your MySQL user/password differ from the
   XAMPP/WAMP defaults (`root` / empty password).
4. Visit `index.php` in the browser.

## Seed Logins
| Username | Password    | Role   |
|----------|-------------|--------|
| admin    | admin123    | Admin  |
| john     | john123     | Member |
| sarah    | sarah123    | Member |
| tanvir   | tanvir123   | Member |

(Passwords are stored as bcrypt hashes in the database — the table above is for
testing only.)

## Database Schema (8 tables)
`users`, `members`, `authors`, `categories`, `publishers`, `books`, `borrows`, `fines`

See `SECURITY.md` and `docs/` for the ER diagram and a mapping of ER-modeling /
schema-conversion concepts (Chapters 6–7) to this design.

## Project Structure
```
ewu-library-management/
├── includes/
│   ├── db_connect.php   # DB connection (credentials isolated here)
│   ├── auth.php         # session, login/role guards, CSRF helpers
│   └── functions.php    # output-escaping helper h()
├── index.php, login.php, logout.php
├── admin_dashboard.php, member_dashboard.php
├── view_books.php, add_book.php, edit_book.php, delete_book.php
├── view_members.php, add_member.php, edit_member.php, delete_member.php
├── borrow_book.php, return_book.php, view_borrows.php
├── pay_fine.php, view_fines.php
├── header.php, footer.php
├── database.sql
└── SECURITY.md
```

## Team Contribution
_Fill in each member's name and contribution % here before submission
(minimum 10-point gap between highest and lowest, per the project guidelines)._

Developed for CSE302 – Database Systems, East West University.
