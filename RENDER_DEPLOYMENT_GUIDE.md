# Render.com Deployment Guide for InvoiceGO

This repository is pre-configured with a production-ready **Docker container** (`Dockerfile`), dynamic port binding (`$PORT`), automated Laravel caching, database migration scripts, and a `render.yaml` blueprint for one-click deployment on **Render.com**.

---

## Prerequisites
1. A [Render.com](https://render.com) free account.
2. A GitHub account with this repository pushed to it.
3. A MySQL 8 Database (e.g. Free TiDB Cloud, Aiven, Railway, or any remote MySQL provider).

---

## Option A: One-Click Blueprint Deployment (Recommended)

1. Push this project to your GitHub account:
   ```bash
   git remote add origin https://github.com/<your-username>/invoicego.git
   git branch -M main
   git push -u origin main
   ```
2. Log in to [dashboard.render.com](https://dashboard.render.com).
3. Click **"New +"** in the top right and select **"Blueprint"**.
4. Connect your `invoicego` GitHub repository.
5. Render will detect `render.yaml` automatically.
6. Provide your remote MySQL database credentials when prompted (`DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).
7. Click **"Apply"**. Render will build the Docker image, run migrations, and deploy the live URL!

---

## Option B: Manual Web Service Deployment

1. On the Render Dashboard, click **"New +"** > **"Web Service"**.
2. Select **"Build and deploy from a Git repository"** and choose your repository.
3. Choose the **Docker** runtime (Render automatically detects the `Dockerfile`).
4. Select the **Free** instance type.
5. Under **Environment Variables**, add the following:

| Key | Recommended Value | Note |
|---|---|---|
| `APP_NAME` | `InvoiceGO` | Application title |
| `APP_ENV` | `production` | Production environment |
| `APP_DEBUG` | `false` | Disable debug mode |
| `APP_KEY` | `base64:gpZCNnYIx0ZVcjjfiw2HqnRJzek4OSywuUdShEuXi10=` | Or click "Generate" |
| `APP_URL` | `https://<your-service-name>.onrender.com` | Your live Render domain |
| `LOG_CHANNEL` | `stderr` | Render streaming logs |
| `SESSION_DRIVER` | `cookie` | Zero-dependency session storage |
| `CACHE_STORE` | `file` | File-based cache |
| `QUEUE_CONNECTION` | `sync` | Synchronous execution |
| `RUN_MIGRATIONS` | `true` | Runs `php artisan migrate --force` on startup |
| `DB_CONNECTION` | `mysql` | MySQL 8 driver |
| `DB_HOST` | `<your-mysql-host>` | e.g. `gateway01.ap-southeast-1.prod.aws.tidbcloud.com` |
| `DB_PORT` | `3306` | Default MySQL port |
| `DB_DATABASE` | `invoicego` | Your database name |
| `DB_USERNAME` | `<your-db-username>` | Database user |
| `DB_PASSWORD` | `<your-db-password>` | Database password |

6. Click **"Create Web Service"**.

---

## Automated Entrypoint Actions
When the container boots on Render, `/usr/local/bin/entrypoint.sh` automatically:
1. Dynamically binds Apache to Render's internal `${PORT}`.
2. Ensures all Laravel permissions (`storage` & `bootstrap/cache`) are set to `www-data`.
3. Runs `php artisan config:cache`, `php artisan route:cache`, and `php artisan view:cache`.
4. Executes `php artisan migrate --force` to migrate database tables.
5. Starts the Apache server.

