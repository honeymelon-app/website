# 🍈 Honeymelon Platform

Control plane for Honeymelon: marketing pages, update manifests for the Tauri app, license-gated downloads, payment webhooks, and release administration. Single app, low cost, Cloudflare in front, GitHub Releases for artifacts. Optional R2/S3 when bandwidth grows.

## Table of Contents

- [Table of Contents](#table-of-contents)
- [Features](#features)
- [Architecture](#architecture)
- [Data Model](#data-model)
- [API](#api)
  - [Updates](#updates)
  - [Downloads](#downloads)
  - [Webhooks](#webhooks)
  - [Admin](#admin)
- [Client (Tauri) Configuration](#client-tauri-configuration)
- [Requirements](#requirements)
- [Setup](#setup)
  - [.env Template](#env-template)
- [Release Workflow](#release-workflow)
- [Admin UI](#admin-ui)
- [Cloudflare](#cloudflare)
- [Security](#security)
- [Deployment](#deployment)
  - [Option A: VPS (lowest cost)](#option-a-vps-lowest-cost)
  - [Option B: Serverless (AWS Bref)](#option-b-serverless-aws-bref)
- [CI Snippets](#ci-snippets)
  - [Trigger publish from `app-macos`](#trigger-publish-from-app-macos)
  - [Typical post-deploy cache warmers](#typical-post-deploy-cache-warmers)
- [Roadmap](#roadmap)
- [Contributing](#contributing)

## Features

- Updates API for the Tauri auto-updater.
- License-gated downloads with 302 redirects to GitHub assets (or signed R2/S3 URLs).
- Release administration (channels, notes, rollback).
- Payments and licensing via Lemon Squeezy or Stripe webhooks.
- Marketing and legal pages (Blade/Inertia) or proxied statics.
- Cache-friendly endpoints for CDN.

## Architecture

```

Cloudflare (TLS/WAF/Cache)
│
▼
Laravel "platform" (PHP 8.3)
├─ Marketing (Blade/Inertia)
├─ API: updates, downloads, licenses
├─ Admin: releases, artifacts, licenses
├─ Webhooks: LS/Stripe → orders/licenses
└─ Storage: DB + cache + artifact pointers
│
├─ GitHub Releases (primary artifacts)
└─ R2/S3 (optional, later)

```

## Data Model

- **releases**: `version`, `channel` (`stable|beta`), `notes_md`, `pub_date`, `critical`
- **artifacts**: `release_id`, `platform` (e.g., `darwin-aarch64`), `source` (`github|r2|s3`), `url/path`, `sha256`, `size`
- **manifests**: `channel`, `version`, `json`, `is_latest`
- **licenses**: `key`, `status` (`active|revoked`), `seats`, `meta`
- **orders**: `provider` (`ls|stripe`), `external_id`, `email`, `license_id`
- **webhook_events**: `provider`, `type`, `payload`, `processed_at`
- **activations** (optional): `license_id`, `device_id_hash`, `app_ver`, `os_ver`, `last_seen_at`

## API

### Updates

```

GET /api/updates/{channel}/latest.json
GET /api/updates/{channel}/{version}.json

````

Response (Tauri Updater):

```json
{
  "version": "1.3.2",
  "notes": "…",
  "pub_date": "2025-11-01T02:10:00Z",
  "platforms": {
    "darwin-aarch64": {
      "signature": "<ed25519>",
      "url": "https://github.com/honeymelon-app/app-macos/releases/download/v1.3.2/honeymelon-1.3.2.dmg",
      "sha256": "<sha256>"
    }
  }
}
````

### Downloads

```
GET /download?license=XXXX-XXXX-XXXX-XXXX&version=1.3.2&platform=darwin-aarch64
→ 302 redirect to GitHub asset (or signed R2/S3 URL)
```

### Webhooks

```
POST /api/webhooks/lemonsqueezy
POST /api/webhooks/stripe
```

Creates `orders` and issues `licenses`, then emails the key.

### Admin

```
POST /api/admin/releases/publish   # { "tag": "v1.3.2", "channel": "stable" }
POST /api/admin/releases/rollback  # { "version": "1.3.2", "channel": "stable" }
```

Protected via signed routes or basic auth.

## Client (Tauri) Configuration

```jsonc
// src-tauri/tauri.conf.json
{
  "updater": {
    "active": true,
    "dialog": true,
    "endpoints": ["https://www.honeymelon.app/api/updates/stable/latest.json"],
    "pubkey": "<ED25519_PUBLIC_KEY>"
  }
}
```

## Requirements

- PHP 8.3, Composer
- Node 20+ (if using Inertia/Vite)
- Redis (cache/queue) or database cache for bootstrap
- Database: Postgres or MySQL (SQLite acceptable for dev)
- GitHub token with `repo:read` for release ingestion
- Optional R2/S3 credentials for signed downloads
- Mail provider (SES/SMTP) for license emails

## Setup

```bash
git clone https://github.com/honeymelon-app/platform.git
cd platform
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
npm ci && npm run build   # only if using front-end assets
php artisan serve         # or run via PHP-FPM/Nginx
```

### Issuing Licenses

```
php artisan license:generate-keys
  # prints a base64 public/private pair for LICENSE_SIGNING_* env vars

php artisan license:issue 8ee1d9d7-... --major=1

php artisan license:issue --email=you@example.com --major=2 --json
  # creates an ad-hoc order (provider=manual) and emits the signed key as JSON
```

Set `LICENSE_SIGNING_PUBLIC_KEY` and `LICENSE_SIGNING_PRIVATE_KEY` (base64 Ed25519 values) in your
environment before issuing licenses. You can generate a pair locally with
`php artisan license:generate-keys` and copy the output into `.env`.

### .env Template

```dotenv
APP_URL=https://www.honeymelon.app
APP_ENV=production

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_DATABASE=honeymelon
DB_USERNAME=...
DB_PASSWORD=...

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_URL=redis://localhost:6379

GITHUB_TOKEN=ghp_...
GITHUB_OWNER=honeymelon-app
GITHUB_REPO=app-macos

LEMONSQUEEZY_SIGNING_SECRET=...
LEMONSQUEEZY_PRODUCT_ID=...

FILESYSTEM_DISK=local
R2_ENDPOINT=https://<account>.r2.cloudflarestorage.com
R2_BUCKET=honeymelon-artifacts
R2_KEY=...
R2_SECRET=...

MAIL_MAILER=ses
MAIL_FROM_ADDRESS=support@honeymelon.app
MAIL_FROM_NAME=Honeymelon
```

## Release Workflow

1. In `app-macos`: tag `v1.3.2`, build, sign, notarize, staple, attach `.dmg` to GitHub Release, upload `SHA256SUMS.txt`.
2. From CI in `app-macos`, call the platform endpoint:

```bash
curl -u "$HM_USER:$HM_PASS" \
  -X POST https://www.honeymelon.app/api/admin/releases/publish \
  -H 'Content-Type: application/json' \
  -d '{"tag":"v1.3.2","channel":"stable"}'
```

3. Platform fetches the GitHub Release, records `releases` and `artifacts`, writes the manifest, marks `latest`, and optionally clears CDN cache for `/api/updates/stable/latest.json`.

## Admin UI

Use Filament or Laravel Nova to manage:

- Releases, Artifacts, Channels
- Licenses, Orders, Revocations
- Webhook events and retries

## Cloudflare

- Put Cloudflare in front of the app domain.
- Cache rules:

  - `/api/updates/*` — cache 60–300 seconds, serve stale while revalidating.
  - `/download` — do not cache; rate-limit.
- Enable HSTS and automatic HTTPS.

## Security

- Use signed routes or basic auth over HTTPS for `/api/admin/*`.
- Verify webhook signatures (Lemon Squeezy and Stripe).
- Store secrets in GitHub Actions or your secret manager.
- Redact license keys and PII in logs.
- If using R2/S3, generate short-lived signed URLs only.

## Deployment

### Option A: VPS (lowest cost)

- Nginx + PHP-FPM 8.3 + Redis + Postgres/MySQL
- Supervisor for queues (if used)
- Point Cloudflare to Nginx; configure cache and WAF rules

### Option B: Serverless (AWS Bref)

- API Gateway + Lambda (PHP) + S3/R2
- Replace Redis with DynamoDB or ElastiCache as needed
- Pay per use; near-zero idle cost

## CI Snippets

### Trigger publish from `app-macos`

```yaml
- name: Publish to platform
  run: |
    curl -u "${{ secrets.HM_USER }}:${{ secrets.HM_PASS }}" \
      -X POST ${{ secrets.PLATFORM_URL }}/api/admin/releases/publish \
      -H 'Content-Type: application/json' \
      -d '{"tag":"${{ github.ref_name }}","channel":"stable"}'
```

### Typical post-deploy cache warmers

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Roadmap

- R2/S3 artifact storage with signed downloads
- Staged rollouts (percentage, cohorts)
- Device activations and seat management
- Stripe support via Cashier (if not using Lemon Squeezy)
- Public metrics page (downloads, versions)
- SBOM and provenance attachment to releases

## Contributing

Issues and pull requests are welcome. Include a clear problem statement, steps to reproduce, proposed change and rationale, and tests where applicable.
