# 🍈 Honeymelon Platform

Control plane for Honeymelon: marketing pages, update manifests for the Tauri app, license-gated downloads, payment webhooks, and release administration. Single app, low cost, Cloudflare in front, GitHub Releases for artifacts. Optional R2/S3 when bandwidth grows.

## Tech Stack

- **Backend**: Laravel 12 (PHP 8.5)
- **Frontend**: Inertia.js v2 + Vue 3 + TypeScript
- **Styling**: Tailwind CSS v4
- **Auth**: Laravel Fortify + Sanctum
- **Type Safety**: Laravel Wayfinder for TypeScript route generation
- **Testing**: PHPUnit
- **Code Quality**: Laravel Pint

## Table of Contents

- [Tech Stack](#tech-stack)
- [Table of Contents](#table-of-contents)
- [Features](#features)
- [Architecture](#architecture)
- [Data Model](#data-model)
- [API](#api)
  - [Public Endpoints](#public-endpoints)
  - [Webhooks](#webhooks)
  - [Protected Endpoints (Client Auth)](#protected-endpoints-client-auth)
  - [Admin UI Routes](#admin-ui-routes)
- [Client (Tauri) Configuration](#client-tauri-configuration)
- [Requirements](#requirements)
- [Setup](#setup)
  - [Issuing Licenses](#issuing-licenses)
  - [.env Template](#env-template)
- [Release Workflow](#release-workflow)
  - [Option 1: GitHub Webhook (Automatic)](#option-1-github-webhook-automatic)
  - [Option 2: Manual Sync](#option-2-manual-sync)
  - [Option 3: CI Webhook Trigger](#option-3-ci-webhook-trigger)
  - [Release Process Details](#release-process-details)
- [Admin UI](#admin-ui)
- [Cloudflare](#cloudflare)
- [Security](#security)
- [Deployment](#deployment)
  - [Option A: VPS (lowest cost)](#option-a-vps-lowest-cost)
  - [Option B: Serverless (AWS Bref)](#option-b-serverless-aws-bref)
- [CI Snippets](#ci-snippets)
  - [Trigger publish from `app-macos`](#trigger-publish-from-app-macos)
  - [Typical post-deploy cache warmers](#typical-post-deploy-cache-warmers)
- [Scheduler](#scheduler)
  - [Available Artisan Commands](#available-artisan-commands)
- [Roadmap](#roadmap)
- [Contributing](#contributing)

## Features

- **Auto-updater API** for Tauri with channel support (stable/beta/alpha/rc)
- **License-gated downloads** with 302 redirects to GitHub assets or signed R2/S3 URLs
  - Offline validation (licenses embed allowed major version)
  - Device activation tracking
  - No phone-home required after download
- **Release Management**
  - Multi-product support
  - Multiple channels (stable, beta, alpha, rc)
  - Auto-sync from GitHub Releases (fetches real commit SHAs)
  - Manual and webhook-triggered publishing
  - Release notes with Markdown support
- **Payment Integration**
  - Stripe webhook handling for orders and licenses
  - Automatic license issuance on successful payment
- **Admin Dashboard**
  - Release, artifact, license, and order management
  - Analytics (downloads, page visits, revenue)
  - Built with Inertia.js and Vue 3
- **SEO & Marketing**
  - Server-side rendering support
  - Dynamic robots.txt and sitemap.xml
  - Page visit analytics
- **Developer Experience**
  - Type-safe routing with Wayfinder
  - Comprehensive test coverage
  - GitHub Actions CI/CD ready

## Architecture

```

Cloudflare (TLS/WAF/Cache)
│
▼
Laravel "platform" (PHP 8.5 + Laravel 12)
├─ Frontend (Inertia.js v2 + Vue 3)
│  ├─ Marketing pages with SSR
│  └─ Admin dashboard
├─ API
│  ├─ Downloads (license-gated)
│  ├─ License activation
│  └─ Artifact upload (CI)
├─ Webhooks
│  ├─ Stripe (payments → licenses)
│  └─ GitHub (release → sync)
└─ Background Jobs
   ├─ GitHub release sync (hourly)
   └─ Stripe product sync (daily)
│
├─ GitHub Releases (primary artifacts)
└─ R2/S3 (optional, configured)

```

## Data Model

- **products**: `name`, `slug`, `stripe_product_id`, `stripe_price_id`, `price_cents`, `is_active`
- **releases**: `product_id`, `version`, `tag`, `commit_hash`, `channel` (`stable|beta|alpha|rc`), `notes`, `published_at`, `is_downloadable`, `major`
- **artifacts**: `release_id`, `platform` (e.g., `darwin-aarch64`), `source` (`github|r2|s3`), `url`, `path`, `filename`, `sha256`, `signature`, `size`, `notarized`
- **licenses**: `product_id`, `order_id`, `key`, `key_plain`, `status` (`active|revoked`), `max_major_version`, `can_access_prereleases`, `device_id`, `activated_at`, `activation_count`
- **orders**: `product_id`, `provider` (`stripe|manual`), `stripe_checkout_id`, `amount_cents`, `currency`, `customer_email`, `status`
- **webhook_events**: `provider` (`stripe|github`), `type`, `payload`, `processed_at`
- **downloads**: `license_id`, `artifact_id`, `ip_address`, `user_agent`, `downloaded_at`
- **page_visits**: `url`, `referrer`, `user_agent`, `ip_address`, `visited_at`

## API

### Public Endpoints

```bash
# License-gated download
GET /api/download?license=XXXX-XXXX&artifact=uuid
→ 302 redirect to GitHub asset or signed R2/S3 URL

# License activation (device tracking)
POST /api/licenses/activate
Body: { "license_key": "XXXX-XXXX", "device_id": "..." }

# Stripe checkout session
POST /api/checkout
Body: { "product_id": "uuid", "success_url": "...", "cancel_url": "..." }
```

### Webhooks

```bash
# Stripe webhook (payment events)
POST /api/webhooks/stripe
→ Creates orders and issues licenses automatically

# GitHub release webhook (requires client auth)
POST /api/webhooks/github/release
Body: GitHub release webhook payload
→ Syncs release and artifacts to database
```

### Protected Endpoints (Client Auth)

```bash
# Upload artifact from CI
POST /api/artifacts/upload
Headers: Authorization: Bearer {token}
Body: multipart/form-data with artifact file
```

### Admin UI Routes

All admin routes require authentication via Laravel Fortify:

- `/admin` - Dashboard with analytics
- `/admin/releases` - Release management
- `/admin/licenses` - License management
- `/admin/orders` - Order management
- `/admin/artifacts` - Artifact management

## Client (Tauri) Configuration

```jsonc
// src-tauri/tauri.conf.json
{
  "updater": {
    "active": true,
    "dialog": true,
    "endpoints": [
      "https://www.honeymelon.app/api/updates/stable/latest.json",
      "https://www.honeymelon.app/api/updates/beta/latest.json"
    ],
    "pubkey": "<ED25519_PUBLIC_KEY_FOR_SIGNATURE_VERIFICATION>"
  }
}
```

Supported channels: `stable`, `beta`, `alpha`, `rc`

## Requirements

- PHP 8.5+, Composer 2.x
- Node 20+ (for Vite, TypeScript, and Wayfinder)
- Redis (cache/queue/sessions)
- Database: PostgreSQL or MySQL (SQLite for development)
- GitHub personal access token with `repo` scope
- Stripe account and webhook secret
- Optional: Cloudflare R2 or AWS S3 for artifact storage
- Optional: Mail provider (SES/SMTP) for license emails

## Setup

```bash
git clone https://github.com/honeymelon-app/platform.git
cd platform
cp .env.example .env

# Install dependencies
composer install
npm ci

# Generate application key and build assets
php artisan key:generate
npm run build

# Set up database
php artisan migrate --seed

# Generate client credentials for API access
php artisan client:generate

# Start development server
composer run dev  # Runs both Laravel and Vite
```

### Issuing Licenses

First, generate Ed25519 signing keys:

```bash
php artisan license:generate-keys
# Copy the output to LICENSE_SIGNING_PUBLIC_KEY and LICENSE_SIGNING_PRIVATE_KEY in .env
```

Then issue licenses:

```bash
# Issue a license for a specific order
php artisan license:issue {order-uuid} --major=1

# Create a manual order and license in one step
php artisan license:issue --email=customer@example.com --major=1 --json
```

License features:

- **Offline validation**: Licenses are cryptographically signed and validated client-side
- **Major version locking**: Each license specifies the maximum major version (e.g., 1 = all 1.x releases)
- **Device activation tracking**: Optional tracking of device IDs for seat management
- **Prerelease access**: Can be toggled per license for beta/alpha access
- **No phone-home**: After initial download, no server communication required

### .env Template

```dotenv
APP_NAME=Honeymelon
APP_URL=https://www.honeymelon.app
APP_ENV=production
APP_DEBUG=false

# Database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_DATABASE=honeymelon
DB_USERNAME=...
DB_PASSWORD=...

# Cache, Queue, Sessions
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_URL=redis://localhost:6379

# GitHub Integration
GITHUB_TOKEN=ghp_...
GITHUB_OWNER=honeymelon-app
GITHUB_REPO=app-macos

# Stripe
STRIPE_KEY=pk_...
STRIPE_SECRET=sk_...
STRIPE_WEBHOOK_SECRET=whsec_...

# License Signing (generate with: php artisan license:generate-keys)
LICENSE_SIGNING_PUBLIC_KEY=...
LICENSE_SIGNING_PRIVATE_KEY=...

# Optional: Cloudflare R2 / AWS S3
FILESYSTEM_DISK=local
R2_ENDPOINT=https://<account>.r2.cloudflarestorage.com
R2_BUCKET=honeymelon-artifacts
R2_KEY=...
R2_SECRET=...
R2_PUBLIC_URL=https://cdn.honeymelon.app

# Optional: Mail
MAIL_MAILER=smtp
MAIL_FROM_ADDRESS=support@honeymelon.app
MAIL_FROM_NAME=Honeymelon

# Inertia SSR (optional, for better SEO)
INERTIA_SSR_ENABLED=true
INERTIA_SSR_URL=http://127.0.0.1:13714
```

## Release Workflow

### Option 1: GitHub Webhook (Automatic)

1. Create a GitHub webhook pointing to `/api/webhooks/github/release`
2. Generate client credentials: `php artisan client:generate`
3. Configure webhook to send `Bearer {token}` in Authorization header
4. When you publish a release on GitHub, it's automatically synced to the platform

### Option 2: Manual Sync

Run the sync command to fetch all releases from GitHub:

```bash
php artisan github:sync-releases
```

This runs automatically every hour via the scheduler.

### Option 3: CI Webhook Trigger

From your app's CI pipeline:

```yaml
- name: Notify platform of new release
  run: |
    curl -X POST https://www.honeymelon.app/api/webhooks/github/release \
      -H "Authorization: Bearer ${{ secrets.PLATFORM_CLIENT_TOKEN }}" \
      -H "Content-Type: application/json" \
      -d @github-release-webhook-payload.json
```

### Release Process Details

1. **In app repository**: Tag, build, sign, notarize, attach artifacts to GitHub Release
2. **Platform receives webhook or runs sync**:
   - Fetches release metadata and asset list
   - Resolves actual commit SHA (not branch name)
   - Determines channel from tag (alpha/beta/rc/stable)
   - Auto-links release to first active product
   - Creates artifact records with download URLs
3. **Releases are immediately available** via admin UI for publishing
4. **Mark as downloadable** to make available to customers

## Admin UI

Built with Inertia.js and Vue 3, featuring:

**Dashboard** (`/admin`)

- Download analytics (last 7 days, with sparkline)
- Revenue tracking
- Recent orders and licenses
- Quick stats

**Release Management** (`/admin/releases`)

- List all releases with filters by channel and search
- Create new releases manually
- Edit release notes and metadata
- Mark releases as downloadable
- Delete releases (cascades to artifacts)

**License Management** (`/admin/licenses`)

- View all licenses with status and activation info
- Issue new licenses
- Revoke licenses
- Track device activations

**Order Management** (`/admin/orders`)

- View all orders from Stripe and manual sources
- See associated licenses
- Track payment status

**Artifact Management** (`/admin/artifacts`)

- View all artifacts by platform
- Track download counts
- Manage artifact availability

All routes use Wayfinder for type-safe navigation and are protected by Laravel Fortify authentication.

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
- **Rate limiting** is enforced on all public API endpoints:
  - Downloads: 10 requests/min per IP+license combination
  - General API: 60 requests/min per IP
  - Customize limits in `bootstrap/app.php` if needed

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

## Scheduler

The platform includes scheduled tasks for keeping data in sync. Add the Laravel scheduler to your server's crontab:

```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

**Scheduled commands:**

| Command                | Frequency | Description                                                   |
| ---------------------- | --------- | ------------------------------------------------------------- |
| `github:sync-releases` | Hourly    | Syncs releases and artifacts from GitHub, fetches commit SHAs |
| `stripe:sync`          | Daily     | Syncs product and price details from Stripe                   |

View the schedule with `php artisan schedule:list`.

### Available Artisan Commands

```bash
# License Management
php artisan license:generate-keys      # Generate Ed25519 signing keys
php artisan license:issue              # Issue a new license

# Client Credentials (for CI/webhooks)
php artisan client:generate            # Generate API client credentials
php artisan token:personal             # Generate personal access token

# GitHub Sync
php artisan github:sync-releases       # Manually sync releases from GitHub

# Stripe
php artisan stripe:sync                # Sync products and prices from Stripe
```

## Roadmap

- R2/S3 artifact storage with signed downloads (configured)
- Device activation tracking (implemented)
- Stripe integration (implemented)
- GitHub release sync with commit SHA resolution (implemented)
- Multi-channel support (stable/beta/alpha/rc)
- Admin dashboard with analytics
- Rate limiting for download endpoints (10 requests/min per IP+license)
- Email notifications for license issuance
- Public metrics page (downloads, version distribution)
- SBOM and provenance attachment to releases
- Webhook retry mechanism

## Contributing

Issues and pull requests are welcome. Include a clear problem statement, steps to reproduce, proposed change and rationale, and tests where applicable.
