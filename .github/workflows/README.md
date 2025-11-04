# GitHub Actions CI/CD Workflows

This directory contains GitHub Actions workflows for continuous integration and deployment of the Laravel application.

## Workflows Overview

### 1. **CI Workflow** (`ci.yml`)

The main comprehensive CI workflow that runs on every push and pull request to `main` and `develop` branches.

#### Jobs:

- **setup**: Validates `composer.json`, `composer.lock`, and `package.json`
- **php-tests**: Runs PHPUnit tests against PHP 8.2 and 8.4
- **code-quality**: Runs Laravel Pint, Prettier, ESLint, and TypeScript checks
- **security**: Performs security audits on Composer and NPM dependencies
- **build-assets**: Builds production assets with Wayfinder route generation
- **status**: Final status check for all jobs

#### Key Features:

- **Matrix Testing**: Tests against multiple PHP versions (8.2, 8.4)
- **Dependency Caching**: Caches both Composer and NPM dependencies for faster builds
- **Wayfinder Integration**: Automatically generates and verifies TypeScript routes
- **Parallel Testing**: Uses PHPUnit's parallel testing for faster execution
- **Artifact Upload**: Uploads test results and build artifacts on failure
- **Security Audits**: Checks for vulnerabilities in dependencies

#### Triggers:
```yaml
on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main, develop]
```

---

### 2. **Tests Workflow** (`tests.yml`)

Focused workflow for running the test suite.

#### Features:

- Tests against PHP 8.2 and 8.4
- Creates SQLite database for testing
- Runs migrations before tests
- Builds frontend assets with Wayfinder
- Verifies Wayfinder route generation
- Runs tests in parallel mode
- Uploads logs on failure

---

### 3. **Linter Workflow** (`lint.yml`)

Code quality and linting workflow split into multiple jobs.

#### Jobs:

- **php-style**: Runs Laravel Pint for PHP code style checking
- **frontend-quality**: Runs Prettier, ESLint, and vue-tsc type checking
- **security**: Performs NPM and Composer security audits

---

## Wayfinder Integration

All workflows include special steps to handle Laravel Wayfinder route generation:

```yaml
- name: Build frontend assets (with Wayfinder)
  run: npm run build

- name: Verify Wayfinder route generation
  run: |
    if [ ! -f "resources/js/routes/index.ts" ]; then
      echo "Error: Wayfinder routes not generated!"
      exit 1
    fi
    echo "✓ Wayfinder routes generated successfully"
```

### Why This Matters:

Wayfinder automatically generates TypeScript route definitions from your Laravel routes during the build process. The CI workflow ensures:

1. Routes are properly generated during the build
2. TypeScript compilation succeeds with the generated routes
3. No TypeScript errors exist in the compiled assets

---

## Caching Strategy

The workflows use GitHub Actions cache to speed up builds:

### Composer Cache:
```yaml
- name: Get Composer Cache Directory
  id: composer-cache
  run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

- name: Cache Composer dependencies
  uses: actions/cache@v4
  with:
    path: ${{ steps.composer-cache.outputs.dir }}
    key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
    restore-keys: |
      ${{ runner.os }}-composer-
```

### NPM Cache:
```yaml
- name: Setup Node.js
  uses: actions/setup-node@v4
  with:
    node-version: '22'
    cache: 'npm'  # Built-in npm caching
```

---

## Environment Variables

Global environment variables defined in workflows:

- `PHP_VERSION`: '8.4' - Default PHP version
- `NODE_VERSION`: '22' - Node.js version
- `DB_CONNECTION`: 'sqlite' - Database connection for testing
- `DB_DATABASE`: ':memory:' - In-memory SQLite for tests

---

## Running Tests Locally

To replicate the CI environment locally:

### Setup:
```bash
cp .env.example .env
php artisan key:generate
composer install
npm ci
```

### Run Tests:
```bash
# PHP tests
php artisan test --parallel

# Code quality
vendor/bin/pint --test
npm run format:check
npm run lint
npx vue-tsc --noEmit

# Build assets
npm run build
```

---

## Troubleshooting

### Wayfinder Routes Not Generated

If routes aren't generated:
1. Ensure `@laravel/vite-plugin-wayfinder` is in `package.json`
2. Check `vite.config.js` for Wayfinder plugin configuration
3. Verify routes are properly named in `routes/web.php`

### Cache Issues

Clear caches in GitHub Actions:
1. Go to repository Settings → Actions → Caches
2. Delete relevant caches
3. Re-run the workflow

### Dependency Installation Failures

Check:
- `composer.lock` is committed
- `package-lock.json` is committed
- Dependencies are compatible with PHP 8.2+

---

## Workflow Status Badges

Add status badges to your README:

```markdown
![CI](https://github.com/YOUR_USERNAME/YOUR_REPO/workflows/CI/badge.svg)
![Tests](https://github.com/YOUR_USERNAME/YOUR_REPO/workflows/tests/badge.svg)
![Linter](https://github.com/YOUR_USERNAME/YOUR_REPO/workflows/linter/badge.svg)
```

---

## Performance Optimization

Current optimizations in place:

1. **Parallel Testing**: PHPUnit runs tests in parallel
2. **Dependency Caching**: Both Composer and NPM dependencies are cached
3. **Build Artifact Caching**: Frontend builds are cached when possible
4. **fail-fast: false**: Matrix jobs continue even if one fails
5. **Timeout Limits**: All jobs have reasonable timeout limits

Average workflow execution times:
- **CI Workflow**: ~8-12 minutes
- **Tests Workflow**: ~5-8 minutes
- **Linter Workflow**: ~3-5 minutes

---

## Security Considerations

1. **Permissions**: Workflows use `contents: read` for minimal permissions
2. **Secrets**: Never commit `.env` files or secrets
3. **Dependency Audits**: Regular security checks on dependencies
4. **continue-on-error**: Security audits won't fail the build but will report issues

---

## Future Improvements

Potential enhancements:

- [ ] Add code coverage reporting
- [ ] Implement deployment workflows
- [ ] Add browser testing with Laravel Dusk
- [ ] Set up automatic dependency updates
- [ ] Add performance benchmarking
- [ ] Implement database seeding tests
- [ ] Add visual regression testing

---

## Contributing

When adding new workflows:

1. Follow the naming convention: `job-name.yml`
2. Add proper documentation in this README
3. Include timeout limits for all jobs
4. Use caching where appropriate
5. Test locally before committing
6. Add status badges to the main README

---

## Resources

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [Laravel Wayfinder](https://github.com/laravel/wayfinder)
- [Laravel Pint](https://laravel.com/docs/pint)
- [Vite Documentation](https://vitejs.dev/)
