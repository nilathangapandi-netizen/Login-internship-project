# Internship Project – Register › Login › Profile

## Tech Stack
- **Frontend**: HTML5, Bootstrap 5, jQuery AJAX
- **Backend**: PHP 8+
- **Auth DB**: MySQL (users table, prepared statements)
- **Profile DB**: MongoDB (profile details)
- **Session**: Redis (backend) + localStorage (frontend)

---

## Prerequisites

| Tool | Version |
|------|---------|
| PHP  | 8.0+    |
| MySQL | 8.0+  |
| MongoDB | 6.0+ |
| Redis | 7.0+  |
| Composer | latest |

---

## Setup Commands

### 1 – Install PHP extensions & MongoDB driver

```bash
# Ubuntu / Debian
sudo apt update
sudo apt install -y php php-mysqli php-redis php-curl php-json unzip

# Install MongoDB PHP extension via PECL
sudo apt install -y php-pear php-dev
sudo pecl install mongodb
echo "extension=mongodb.so" | sudo tee /etc/php/$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')/cli/conf.d/20-mongodb.ini

# Install Composer (if not already installed)
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 2 – Install MongoDB PHP library (in project root)

```bash
cd internship-project
composer require mongodb/mongodb
```

### 3 – Set up MySQL database

```bash
mysql -u root -p < schema.sql
```

### 4 – Start Redis (if not running)

```bash
# Ubuntu
sudo systemctl start redis-server
sudo systemctl enable redis-server

# macOS (Homebrew)
brew services start redis
```

### 5 – Start MongoDB (if not running)

```bash
# Ubuntu
sudo systemctl start mongod
sudo systemctl enable mongod

# macOS (Homebrew)
brew services start mongodb-community
```

### 6 – Configure database credentials

Edit `php/config.php` and update:

```php
define('DB_USER', 'root');    // your MySQL username
define('DB_PASS', '');        // your MySQL password
```

### 7 – Run the PHP development server

```bash
cd internship-project
php -S localhost:8000
```

### 8 – Open in browser

```
http://localhost:8000
```

---

## Folder Structure

```
internship-project/
├── assets/
├── css/
│   └── style.css
├── js/
│   ├── login.js
│   ├── profile.js
│   └── register.js
├── php/
│   ├── config.php
│   ├── login.php
│   ├── profile.php
│   └── register.php
├── index.html
├── login.html
├── profile.html
├── register.html
├── schema.sql
└── composer.json  (auto-generated after step 2)
```

---

## Flow

1. **Register** → POST to `php/register.php` → stored in MySQL with bcrypt password hash
2. **Login** → POST to `php/login.php` → validated against MySQL → token stored in Redis → token + user info stored in `localStorage`
3. **Profile** → GET/POST `php/profile.php` → token validated via Redis → profile details read/written in MongoDB

---

## Notes

- No PHP sessions are used anywhere — all session state is Redis (backend) + localStorage (frontend)
- All MySQL queries use prepared statements
- AJAX-only — no HTML form submissions
- Bootstrap 5 provides responsive layout
