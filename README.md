# Smart Complaint Management System

A PHP + MySQL complaint portal with user registration/login, complaint tracking, admin management, responsive UI and automatic escalation after 3 days.

## XAMPP setup
1. Copy the project folder into `htdocs`.
2. Start Apache and MySQL.
3. Open phpMyAdmin and import `database.sql`.
4. Open `includes/create_accounts.php` once to create demo accounts, then delete that file.
5. Visit `http://localhost/<folder>/`.

### Demo accounts
- User: `yash@example.com` / `yash123`
- Admin: `rajesh` / `rajesh123`

## Structure
- `includes/` configuration, shared layout and security helpers
- `assets/` CSS and JavaScript
- `admin/` administrator portal
- root PHP files for user workflow
- `database.sql` database schema
