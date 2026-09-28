# Running the Login & Registration backend

The site itself (all the `.html` pages) is static and works anywhere. The
**Login** button and the registration form now talk to a small PHP + MySQL
backend, so those two features need a PHP server with MySQL — they will
**not** work if you just double-click the HTML files, and they can't run
inside a chat preview.

## 1. Set up the database
1. Create the database and table by importing `joy_db.sql` (e.g. in
   phpMyAdmin, or `mysql -u root -p < joy_db.sql`).

## 2. Configure the connection
Edit `includes/db.php` and set your MySQL host/username/password:
```php
const DB_HOST = "localhost";
const DB_NAME = "joy_db";
const DB_USER = "root";
const DB_PASS = "";
```

## 3. Run it
- **Local testing (quickest):** with PHP installed, run this from the
  project folder:
  ```
  php -S localhost:8000
  ```
  then open `http://localhost:8000/index.html`.
- **XAMPP/WAMP/MAMP:** drop this whole folder into `htdocs` and visit it
  through `http://localhost/...`.
- **Real hosting:** upload everything to any PHP + MySQL host (most shared
  hosting works).

## How it works
- `register.php`, `login.php`, `logout.php`, `session_check.php` are the
  backend endpoints. Passwords are hashed with `password_hash()` — never
  stored in plain text.
- `js/nav-auth.js` runs on every page, asks `session_check.php` whether
  someone is logged in, and swaps the **Login** button for **"Hi, Name" +
  Log Out** when they are.
- If the PHP backend isn't running (e.g. you're just viewing the files
  locally), the Login button still shows — it just links to `login.html`,
  and that page will tell you it can't reach the server if you try to
  submit the form.

## AI Chatbox

Every page now has a chat bubble in the bottom-right corner, powered by
Claude (Anthropic's AI). It also needs the PHP server from above, plus your
own Anthropic API key:

1. Get a key at [console.anthropic.com](https://console.anthropic.com) →
   Settings → API Keys. (Usage is billed per message to your Anthropic
   account — check current pricing there.)
2. Open `includes/ai_config.php` and replace `YOUR_API_KEY_HERE` with your
   key.
3. That's it — `chat.php` calls the Anthropic API from the server, so the
   key never reaches visitors' browsers.

**Never** put this key in any `.html` or `.js` file, and never commit it to
a public GitHub repo — anyone who sees it can spend your API credits.

If the key isn't set, the chatbox still opens but tells the visitor it
isn't configured yet, instead of failing silently.

## Troubleshooting: "Server error" when registering
Open **`setup_check.php`** in your browser (same address as your site). It
tests PHP, the `pdo_mysql` extension, and the MySQL connection, and tells you
what to fix. The usual causes:
- **MySQL isn't started** (start it in the XAMPP/WAMP/MAMP control panel)
- **Wrong password/port** in `includes/db.php` (MAMP uses port 8889 and
  password `root`)
- **Opening the page as a file** (`file:///...`) or through a server without
  PHP. Use `http://localhost/...` instead.

The database and table are created automatically on first use. Before going
live, set `DEBUG = false` in `includes/db.php` and delete `setup_check.php`.
