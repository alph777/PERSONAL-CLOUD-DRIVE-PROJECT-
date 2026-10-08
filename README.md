# AlphCloud: personal cloud drive (PHP + MySQL + HTML + CSS + JS)

## Part 1: Set up (about 15 minutes)

1. **Install XAMPP** (free): apachefriends.org. Install to `C:\xampp`.
2. **Open XAMPP Control Panel** and click **Start** next to **Apache** and **MySQL**. Both should turn green.
3. **Copy this whole `cloud-drive` folder** into `C:\xampp\htdocs\`.
   Final path: `C:\xampp\htdocs\cloud-drive\index.php`
4. **Create the database**:
   - Open http://localhost/phpmyadmin
   - Click the **Import** tab, choose `database.sql`, click **Import** at the bottom.
   - You should now see a database called `cloud_drive` with two tables: `users` and `files`.
5. **Allow bigger uploads** (XAMPP's default is small):
   - In the XAMPP panel click **Config** next to Apache, then **PHP (php.ini)**.
   - Find and change these two lines, then save:
     `upload_max_filesize = 200M`
     `post_max_size = 210M`
   - Click **Stop** then **Start** on Apache.
6. **Open the site**: http://localhost/cloud-drive/
7. Click **Create your drive**, register, and upload a file.
8. **Open the folder in VS Code**: File > Open Folder > `C:\xampp\htdocs\cloud-drive`.
   Edit, save, refresh the browser. No build step is needed.

## Part 2: Folder map (what each file does)

| File | Job |
|---|---|
| `config.php` | Starts the session, connects to MySQL, holds helper functions and the shared page header |
| `database.sql` | Creates the `users` and `files` tables |
| `index.php` | Landing page |
| `register.php` / `login.php` / `logout.php` | Account creation, sign in, sign out |
| `dashboard.php` | Main page: storage meter, upload area, search, file grid |
| `upload.php` | Receives files from the browser, saves them, records them in the database |
| `download.php` | Checks the file belongs to you, then sends it |
| `delete.php` | Deletes the file from the disk and the database |
| `assets/style.css` | All the design and animations |
| `assets/app.js` | Drag and drop, progress bar, search, delete popup, notifications |
| `uploads/` | Where uploaded files are stored (locked from direct URL access by `.htaccess`) |

## Part 3: How it works (for your viva)

**Sessions.** After login, PHP stores your user id in `$_SESSION`. Every protected page calls `require_login()`, which sends you to the login page if no id is stored.

**Passwords.** We never store the real password. `password_hash()` turns it into a long scrambled string when you register, and `password_verify()` checks a login attempt against it.

**SQL injection protection.** Every query uses prepared statements (`prepare` + `execute` with `?`), so user input can never change the meaning of the SQL.

**Upload flow.**
1. The browser (`app.js`) sends the files to `upload.php` using `XMLHttpRequest`. That object reports progress, which drives the animated bar.
2. `upload.php` checks you are logged in, checks the CSRF token, checks your 500 MB space.
3. Each file gets a random name (like `a91f...`) and is moved into `uploads/<your id>/`. The original name, size and type go into the `files` table.
4. The page reloads and the new file appears.

**Why files are safe.** `uploads/.htaccess` blocks direct links. The only way to get a file is `download.php`, which looks up the file with `WHERE id = ? AND user_id = ?`, so one user can never download another user's file.

**CSRF token.** A random secret is stored in your session and sent with every upload and delete. A malicious site cannot guess it, so it cannot make your browser upload or delete things on your behalf.

**XSS protection.** Every file name printed on a page goes through `e()` (a wrapper for `htmlspecialchars`), so a file named `<script>...` shows as text and never runs.

**Animations (all CSS, no library).**
- Drifting clouds: `@keyframes drift` moves clouds across the screen, each at its own speed.
- Floating upload icon: `@keyframes bob`.
- Drag over the drop zone: the `.over` class (added by JS) changes color and scales it.
- File cards pop in one after another using `animation-delay` calculated from `--i`.
- Delete: the card shrinks and fades with the `.leaving` class before being removed.
- Storage bar grows on page load; toasts slide in; the popup scales in.

## Part 4: Ideas to add if you have extra time
- Rename a file (a new `rename.php` doing `UPDATE files SET original_name = ?`)
- Folders (add a `folder_id` column)
- Share link (add a random `share_token` column)

## Troubleshooting
- **"Database connection failed"**: MySQL is not started in XAMPP, or you skipped step 4.
- **Upload does nothing or says "Nothing arrived"**: file is bigger than your php.ini limit. Redo step 5 and restart Apache.
- **Port 80 busy** (Apache will not start): close Skype/IIS, or in XAMPP click Config > Apache (httpd.conf) and change `Listen 80` to `Listen 8080`, then use http://localhost:8080/cloud-drive/
- **Blank white page**: open `C:\xampp\apache\logs\error.log` to see the PHP error.
