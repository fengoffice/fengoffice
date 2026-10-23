# Feng Office API

NestJS REST API that runs as a separate Node.js process alongside the Feng Office PHP application. Both share the same MySQL database. Apache reverse-proxies public requests to the Node process.

```
Browser → Apache → /fengoffice/api/* → http://127.0.0.1:3000/*     (NestJS)
                 → /fengoffice/*      → <FENG_ROOT>/                 (PHP)
```

---

## Table of Contents

1. [Architecture](#architecture)
2. [Prerequisites](#prerequisites)
3. [Environment Variables](#environment-variables)
4. [Local Development](#local-development)
5. [Production Deployment](#production-deployment)
   - [1. Build](#1-build)
   - [2. Environment File](#2-environment-file)
   - [3. Apache Virtual Host](#3-apache-virtual-host)
   - [4. Process Manager (PM2)](#4-process-manager-pm2)
   - [5. Firewall](#5-firewall)
   - [6. Verify](#6-verify)
6. [API Reference](#api-reference)
7. [Authentication Flow](#authentication-flow)
8. [Idempotent Project Creation](#idempotent-project-creation)
9. [Troubleshooting](#troubleshooting)

---

## Architecture

| Concern | Technology |
|---------|-----------|
| HTTP server | Apache 2.4 (existing Feng server) |
| Reverse proxy | `mod_proxy` + `mod_proxy_http` |
| API runtime | Node.js 18+ / NestJS 11 |
| ORM | Prisma 6 (raw queries only — no schema migrations) |
| Auth | Passport-JWT + rotating refresh tokens |
| Database | Shared Feng MySQL database (read/write `fo_*` tables) |
| Config bridge | `bootstrapRuntimeConfig()` reads `config/config.php` via PHP CLI at startup |

The API **never migrates the database**. It only reads and writes existing Feng tables using the same credentials as the PHP application.

---

## Prerequisites

| Requirement | Version | Notes |
|-------------|---------|-------|
| Node.js | 18+ | Use [nvm](https://github.com/nvm-sh/nvm) to manage versions |
| npm | 9+ | Bundled with Node.js |
| PHP CLI | 7.4+ | Must be in `PATH` or set `PHP_BINARY` in `.env` |
| Apache | 2.4+ | With `mod_proxy` and `mod_proxy_http` enabled |
| MySQL | 5.7+ | Shared with Feng Office (no extra setup needed) |

**Check Node.js version:**
```bash
node --version   # must be v18.x or higher
```

**Check PHP CLI:**
```bash
php --version    # must be PHP 7.4 or higher
which php        # confirms it is in PATH
```

**Enable Apache modules (Linux):**
```bash
a2enmod proxy proxy_http headers
systemctl reload apache2
```

---

## Environment Variables

The API reads database credentials automatically from Feng's `config/config.php` at startup. You only need to set **`JWT_SECRET`** to get started. All other variables have sensible defaults or are auto-detected.

Copy the example file and edit it:
```bash
cp api/.env.example api/.env
```

### Full Variable Reference

| Variable | Required | Default | Description |
|----------|----------|---------|-------------|
| `JWT_SECRET` | **Yes** | — | Random secret for signing JWT tokens. Generate with `openssl rand -base64 64`. |
| `PORT` | No | `3000` | Internal port the Node.js process listens on. Never expose publicly. |
| `NODE_ENV` | No | — | Set to `production` on the server. Affects error verbosity. |
| `FENG_ROOT_URL` | No | Auto from `config.php` | Public-facing root URL, e.g. `https://yourserver.com/fengoffice`. Used for Swagger server path. |
| `CORS_ORIGINS` | No | _(disabled)_ | Comma-separated allowed origins. Leave unset for same-origin only. |
| `DATABASE_URL` | No | Auto from `config.php` | Prisma connection string. Override only if auto-detection fails. |
| `TABLE_PREFIX` | No | Auto from `config.php` | Feng table prefix (e.g. `fo_`). Override only if auto-detection fails. |
| `APP_NAME` | No | `EVX Software` | Product name shown in Swagger UI title and description. Set to `Feng Office` for standard deployments. |
| `APP_LOGO_URL` | No | Auto-detected | Public URL of the logo image. Auto-detected from Feng root (`custom_logo.svg` → `custom_logo.png` → bundled fallback). |
| `PHP_BINARY` | No | `php` | Absolute path to PHP CLI when `php` is not in `PATH`. |
| `FENG_CONFIG_PATH` | No | `../../config/config.php` | Absolute path to Feng's `config.php` when the API is outside the Feng directory tree. |

> **Note:** `JWT_EXPIRES_IN` (7 days) and the refresh token TTL (30 days) are currently hardcoded. They can be made configurable if needed.

### Generating `JWT_SECRET`

```bash
# Linux / macOS / WSL
openssl rand -base64 64

# Node.js (any platform)
node -e "console.log(require('crypto').randomBytes(64).toString('base64'))"

# PowerShell (Windows)
[Convert]::ToBase64String((1..64 | ForEach-Object { [byte](Get-Random -Max 256) }))
```

> Generate a **different secret for each environment** (local, testing, production). Reusing secrets across environments is a security risk.

---

## Local Development

```bash
cd api

# Install dependencies
npm install

# Generate the Prisma client (required after npm install or schema changes)
npx prisma generate

# Start in watch mode (hot reload)
npm run start:dev
```

The API will be available at:
- Direct: `http://localhost:3000`
- Via Apache proxy: `http://localhost/fengoffice/api`
- Swagger UI: `http://localhost/fengoffice/api/docs`

Minimal `api/.env` for local:
```env
JWT_SECRET=any-long-random-string-for-local-dev
PORT=3000
NODE_ENV=development
```

---

## Production Deployment

> **Path note:** The commands below use `$FENG_ROOT` as a placeholder for the Feng Office installation directory. The actual path depends on how your DevOps team deployed Feng (common values: `/var/www/fengoffice`, `/opt/fengoffice`, `/srv/www/feng`, etc.). Set it once before running the commands:
> ```bash
> FENG_ROOT=/var/www/fengoffice   # ← ask your DevOps team for the correct path
> ```

### 1. Build

Build the TypeScript source into JavaScript. Run this on the server or build locally and deploy the `build/` folder.

```bash
cd $FENG_ROOT/api

# Install all dependencies (including devDependencies needed for build)
npm install

# Generate the Prisma client (required after npm install or schema changes)
npx prisma generate

# Compile TypeScript → build/
npm run build

# Optionally remove devDependencies after build
npm prune --omit=dev
```

> **Never run `npm run start:dev` or use `ts-node` in production.** Always use the compiled `build/`.

---

### 2. Environment File

Create `$FENG_ROOT/api/.env`:

```env
# REQUIRED
JWT_SECRET=<output of: openssl rand -base64 64>

# Server
PORT=3000
NODE_ENV=production

# Public URL (if different from what config.php declares)
# FENG_ROOT_URL=https://yourserver.com/fengoffice

# CORS — only if cross-origin clients need access
# CORS_ORIGINS=https://yourserver.com
```

Set restrictive permissions so only the app user can read it:
```bash
chmod 640 $FENG_ROOT/api/.env
chown www-data:www-data $FENG_ROOT/api/.env
```

---

### 3. Apache Virtual Host

Add the proxy directives inside your existing Feng Office `<VirtualHost>` block.

**Option A — Feng is in a subdirectory** (e.g. `https://yourserver.com/fengoffice`):

```apacheconf
<VirtualHost *:443>
    ServerName yourserver.com
    DocumentRoot /path/to/fengoffice   # ← your actual Feng root, ask DevOps

    # Required modules: mod_proxy, mod_proxy_http, mod_headers
    ProxyPreserveHost On
    ProxyRequests     Off

    # Tell the upstream app the original public mount point
    RequestHeader set X-Forwarded-Prefix "/fengoffice/api"

    # Proxy /fengoffice/api/* → NestJS on port 3000
    ProxyPass        "/fengoffice/api/" "http://127.0.0.1:3000/"
    ProxyPassReverse "/fengoffice/api/" "http://127.0.0.1:3000/"

    # SSL, DocumentRoot, Directory, etc. — keep your existing config below
</VirtualHost>
```

**Option B — Feng is at the document root** (e.g. `https://yourserver.com`):

```apacheconf
<VirtualHost *:443>
    ServerName yourserver.com
    DocumentRoot /path/to/fengoffice   # ← your actual Feng root, ask DevOps

    ProxyPreserveHost On
    ProxyRequests     Off
    RequestHeader set X-Forwarded-Prefix "/api"

    ProxyPass        "/api/" "http://127.0.0.1:3000/"
    ProxyPassReverse "/api/" "http://127.0.0.1:3000/"
</VirtualHost>
```

In this case set `FENG_ROOT_URL=https://yourserver.com` (no `/fengoffice` suffix).

**Verify Apache config and reload:**
```bash
apache2ctl configtest   # Must print "Syntax OK"
systemctl reload apache2
```

---

### 4. Process Manager (PM2)

PM2 keeps the Node.js process running and restarts it automatically after crashes or reboots.

```bash
# Install PM2 globally
npm install -g pm2

# Start the API  (replace $FENG_ROOT with the actual path if not already set)
pm2 start $FENG_ROOT/api/build/main.js \
  --name feng-api \
  --cwd $FENG_ROOT/api

# Save the process list so PM2 restores it after reboot
pm2 save

# Generate and install the startup script (run the command it outputs)
pm2 startup
```

**Day-to-day commands:**

```bash
pm2 status                  # List all processes and their state
pm2 logs feng-api           # Stream live logs
pm2 logs feng-api --lines 100  # Show last 100 log lines
pm2 restart feng-api        # Restart (e.g. after updating .env)
pm2 reload feng-api         # Zero-downtime reload
pm2 stop feng-api           # Stop the process
pm2 delete feng-api         # Remove from PM2
```

**Redeploy after a code update:**
```bash
cd $FENG_ROOT/api
npm run build
pm2 reload feng-api
```

---

### 5. Firewall

Port 3000 must **not** be reachable from the internet. Only Apache (on 80/443) should be publicly accessible.

```bash
# UFW (Ubuntu/Debian)
ufw deny 3000
ufw allow 80
ufw allow 443
ufw status

# firewalld (RHEL/CentOS)
firewall-cmd --permanent --remove-port=3000/tcp
firewall-cmd --reload
```

---

### 6. Verify

Run these checks after every deployment:

```bash
# 1. Is PM2 running?
pm2 status

# 2. Does NestJS respond locally?
curl -s http://127.0.0.1:3000/auth/login \
  -X POST -H "Content-Type: application/json" \
  -d '{"identifier":"admin","password":"wrong"}' | python3 -m json.tool

# 3. Does it respond through Apache?
curl -s https://yourserver.com/fengoffice/api/auth/login \
  -X POST -H "Content-Type: application/json" \
  -d '{"identifier":"admin","password":"wrong"}' | python3 -m json.tool

# 4. Check logs if something is wrong
pm2 logs feng-api --lines 50
```

Expected response (even with wrong password):
```json
{
  "error": true,
  "message": "Invalid credentials"
}
```
If you get a 502 Bad Gateway, the Node.js process is not running. Check `pm2 status`.
If you get a 404 from Apache, the proxy block is wrong. Check the VirtualHost config.

---

## API Reference

All endpoints are prefixed with the proxy path (e.g. `/fengoffice/api`).

Interactive documentation is available at **`/fengoffice/api/docs`** (Swagger UI).

### Authentication

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| `POST` | `/auth/login` | Public | Login with Feng username or email + password |
| `POST` | `/auth/refresh` | Public | Exchange a refresh token for a new token pair |
| `POST` | `/auth/logout` | Bearer | Invalidate the current refresh token |

### Projects

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| `POST` | `/projects` | Bearer | Create a project (idempotent via `project_number`) |
| `GET` | `/projects` | Bearer | Paginated list of active projects |
| `GET` | `/projects/:id` | Bearer | Get a single project by its **project number** (custom property) |

**Required permission for `POST /projects`:** `can_manage_dimension_members` in Feng's system permissions.

> `GET /projects/:id` resolves the project by its **project number** (a custom property value), not the internal database id — the segment is treated as text, so a non-existent number returns **404**. Responses use `project_number` as the identifier and do **not** expose the internal object id, `customer_id`, `manager_id`, or `external_id`.

---

## Authentication Flow

```
1. POST /auth/login  { identifier, password }
        ↓
   Returns: { access_token, refresh_token, expires_in }

2. Use access_token as Bearer in every request:
   Authorization: Bearer <access_token>

3. When access_token expires (7 days), call:
   POST /auth/refresh  { refresh_token }
   → Returns a new token pair (old refresh token is invalidated)

4. On logout:
   POST /auth/logout  { refresh_token }
   → Deletes the refresh token from the database
```

**Token storage recommendation for Power Automate:**
- Store `access_token` and `refresh_token` in a secure variable or Key Vault.
- Refresh before each flow run if the token might have expired.
- Always include `project_number` in project creation requests to prevent duplicates on retry.

---

## Idempotent Project Creation

`POST /projects` is idempotent on `project_number` and safe for **sequential** Power Automate retries.

**How it works:**

1. Send `project_number` with a stable business key from your source system (e.g. the MS project number).
2. On first call → project is created, returns **201 Created**.
3. On retry with the same `project_number` → the existing project is returned, **200 OK** — no duplicate created.

```json
POST /projects
{
  "name": "Acme Corp Onboarding",
  "project_number": "MS-0001",
  "description": "Created from Power Automate."
}
```

**Field mapping:** `name` → project title · `project_number` → `project_id` custom property · `description` → `description_special` custom property. The internal database id is never exposed.

| Call | `project_number` exists? | HTTP Status | Result |
|------|--------------------------|-------------|--------|
| First | No | `201 Created` | Project created |
| Retry | Yes | `200 OK` | Same project returned |

> **Best-effort idempotency.** The existence check protects against *sequential* retries (the common Power Automate case). It does **not** guarantee uniqueness under two *simultaneous* requests with the same number — a DB `UNIQUE` constraint isn't feasible because `fo_custom_property_values` is shared by every custom property. If strict concurrency safety is required, add a dedicated project-number table with a `UNIQUE` key (or an advisory lock around the check + create).

---

## Troubleshooting

### API won't start — `JWT_SECRET is required`
`api/.env` is missing or `JWT_SECRET` is not set. Check file exists and has the correct value.

### API won't start — `Feng config not found`
The default relative path `../../config/config.php` doesn't resolve correctly. Set `FENG_CONFIG_PATH` to the absolute path of Feng's `config.php`.

### API won't start — `FengIdsResolver: cannot find dimension with code='customer_project'`
The Feng database is not fully initialised or the table prefix is wrong. Verify `TABLE_PREFIX` matches your database.

### 502 Bad Gateway from Apache
The Node.js process is not running. Check:
```bash
pm2 status
pm2 logs feng-api --lines 30
```

### 404 on all API routes via Apache
The `ProxyPass` rule doesn't match. Confirm that:
- The path in `ProxyPass` exactly matches the URL you're calling.
- `mod_proxy` and `mod_proxy_http` are enabled (`a2enmod proxy proxy_http`).
- Apache was reloaded after the config change (`systemctl reload apache2`).

### Project not visible in Feng dimension panel
Ensure the project was created after all fixes are deployed (the project must have `fo_contact_member_cache` entries). If using an older project, rebuild the member cache from Feng's Administration panel.

### Logo 404 in Swagger UI
If accessing Swagger via Apache proxy, ensure the API is running and the static assets path `/public/` is being forwarded. The logo uses a relative URL (`./public/logo.png`) which resolves within the proxied path.

### `php: command not found` at startup
Set `PHP_BINARY` in `api/.env` to the absolute path of the PHP CLI binary:
```bash
which php8.2    # → /usr/bin/php8.2
```
Then add to `.env`:
```env
PHP_BINARY=/usr/bin/php8.2
```
