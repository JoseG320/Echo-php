# 🎸 Echo — Personal Music Dashboard

A personal music library and social music-sharing web app, originally built in 2024 for CSC335 by Jose and Mustafa. 

---

## What is Echo?

Echo is a PHP web app where users can manage their personal music library, build playlists, and connect with other users to discover shared music taste.

**Features:**
- Register and log in with a secure, hashed-password account
- Add songs (title, artist, album) to your personal library
- Create and manage playlists
- Browse other users' profiles and see songs you have in common
- Follow/connect with other users
- Admin panel to manage users, songs, and playlists

---

## Tech Stack

| Layer     | Technology          |
|-----------|---------------------|
| Backend   | PHP 8.2             |
| Server    | Apache (via Docker) |
| Database  | MySQL 8.0           |
| Frontend  | PicoCSS (pink theme)|
| Auth      | delight-im/auth     |

---

## Getting Started (Docker — Recommended)

This is the easiest way to run and develop Echo. You just need [Docker Desktop](https://www.docker.com/products/docker-desktop/) installed.

### 1. Clone the repository

```bash
git clone https://github.com/your-username/Echo-php.git
cd Echo-php
```

### 2. Set up your environment file

Copy the example env file and fill in your values:

```bash
cp .env.example .env
```

The defaults in `.env.example` work out of the box for Docker — you shouldn't need to change anything unless you want a different password.

### 3. Start the app

```bash
docker compose up --build
```

That's it. Once you see:

```
✅ Echo is running at http://localhost:8080
```

Open your browser and go to **http://localhost:8080**.

### Stopping the app

```bash
docker compose down
```

To also wipe the database (full reset):

```bash
docker compose down -v
```

---

## Getting Started (XAMPP — Legacy)

This was the original setup method. It still works, but Docker is highly recommended for ease of use.

### Requirements
- [XAMPP](https://www.apachefriends.org/) with Apache and MySQL running
- PHP 8.0+

### Steps

1. Clone the repo into XAMPP's web root:
   ```
   /XAMPP/xamppfiles/htdocs/echo/Echo-php
   ```

2. Copy `.env.example` to `.env` and leave the defaults as-is (empty password, localhost).

3. Create the database in phpMyAdmin or MySQL:
   ```sql
   CREATE DATABASE `echo-db`;
   ```

4. Import the schema:
   ```
   schema/schema.sql
   ```

5. Visit `http://localhost/echo/Echo-php`

---

## Environment Variables


| Variable             | Description                        | Docker default  | XAMPP default |
|----------------------|------------------------------------|-----------------|---------------|
| `DB_HOST`            | Database host                      | `db`            | `localhost`   |
| `DB_USER`            | Database username                  | `root`          | `root`        |
| `DB_PASSWORD`        | Database password                  | `rootpassword`  | *(empty)*     |
| `DB_NAME`            | Database name                      | `echo-db`       | `echo-db`     |
| `MYSQL_ROOT_PASSWORD`| MySQL root password (Docker only)  | `rootpassword`  | —             |

The template `.env.example` is included in the repo.

---


## Notes

- The admin panel is accessible from the landing page footer. Create an admin account using: ``docker compose exec app php scripts/create-admin.php username password``
- This project is **not** intended for production deployment as-is. Passwords in `.env` should be treated as local dev secrets only.

---

*Originally built in 2024 by Jose and Mustafa.*
