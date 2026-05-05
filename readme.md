# WPBoilerplate Updater Checker — GitHub

A Composer library that gives any GitHub-hosted WordPress plugin automatic update notifications inside the WordPress admin, using the [Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker) library.

---

## Requirements

- PHP 7.4+
- WordPress 5.0+
- Composer
- A GitHub repository for the plugin you want to auto-update

---

## Installation

Add the library to the `composer.json` of your **WordPress project or plugin**:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/WPBoilerplate/wpb-updater-checker-github"
        }
    ],
    "require": {
        "wpboilerplate/wpb-updater-checker-github": "dev-main"
    }
}
```

Then install:

```bash
composer install
```

Make sure Composer's autoloader is loaded in your plugin or theme:

```php
require_once __DIR__ . '/vendor/autoload.php';
```

---

## GitHub Repository Settings

The following settings must be in place on each GitHub repo you want to track:

### 1. Repository visibility
- **Public repo:** works out of the box — no token needed.
- **Private repo:** requires a GitHub Personal Access Token (PAT) with at least `repo` scope. See [GitHub PAT docs](https://docs.github.com/en/authentication/keeping-your-account-and-data-secure/managing-your-personal-access-tokens).

### 2. Plugin version header

The `Version` field in your plugin's main PHP file **must be kept up to date** — this is what WordPress (and the update checker) reads to decide whether an update is available:

```php
/**
 * Plugin Name: My Plugin
 * Version:     1.2.0
 */
```

Bump this value and push to your release branch to trigger update notifications.

### 3. Release branch

Decide which branch holds your stable releases (commonly `main` or `stable`). The checker watches this branch's `Version` header. Keep feature work on other branches and only merge to the release branch when ready.

### 4. GitHub Releases (optional — only if using `release-assets`)

If you set `'release-assets' => true`, the updater will look for a ZIP file attached to a **GitHub Release** instead of downloading the branch archive. In that case:

- Create a GitHub Release tagged to match the version (e.g. `v1.2.0`).
- Attach the plugin ZIP as a release asset.
- The release tag must exist on the release branch.

---

## Usage

### Basic — single plugin

In your plugin's main file, after loading Composer's autoloader:

```php
require_once __DIR__ . '/vendor/autoload.php';

new WPBoilerplate_Updater_Checker_Github( [
    'repo'           => 'https://github.com/YourOrg/your-plugin',
    'file_path'      => __FILE__,
    'name_slug'      => 'your-plugin',
    'release_branch' => 'main',
] );
```

### Private repository

```php
new WPBoilerplate_Updater_Checker_Github( [
    'repo'           => 'https://github.com/YourOrg/your-private-plugin',
    'file_path'      => __FILE__,
    'name_slug'      => 'your-private-plugin',
    'release_branch' => 'main',
    'token'          => 'ghp_xxxxxxxxxxxxxxxxxxxx', // GitHub PAT
] );
```

Store tokens in `wp-config.php` as constants rather than hard-coding them:

```php
// wp-config.php
define( 'MY_PLUGIN_GITHUB_TOKEN', 'ghp_xxxxxxxxxxxxxxxxxxxx' );

// plugin file
new WPBoilerplate_Updater_Checker_Github( [
    'repo'      => 'https://github.com/YourOrg/your-private-plugin',
    'file_path' => __FILE__,
    'name_slug' => 'your-private-plugin',
    'token'     => defined( 'MY_PLUGIN_GITHUB_TOKEN' ) ? MY_PLUGIN_GITHUB_TOKEN : '',
] );
```

### Using GitHub Release assets

```php
new WPBoilerplate_Updater_Checker_Github( [
    'repo'           => 'https://github.com/YourOrg/your-plugin',
    'file_path'      => __FILE__,
    'name_slug'      => 'your-plugin',
    'release_branch' => 'main',
    'release-assets' => true,
] );
```

### Multiple plugins via the filter

If you manage several GitHub-hosted plugins, register all of them through a single filter instead of creating multiple class instances:

```php
// Instantiate once (empty constructor is fine) to register the admin_init hook.
new WPBoilerplate_Updater_Checker_Github();

add_filter( 'wpboilerplate_updater_checker_github', function ( $packages ) {
    $packages[] = [
        'repo'           => 'https://github.com/YourOrg/plugin-one',
        'file_path'      => WP_PLUGIN_DIR . '/plugin-one/plugin-one.php',
        'name_slug'      => 'plugin-one',
        'release_branch' => 'main',
    ];
    $packages[] = [
        'repo'           => 'https://github.com/YourOrg/plugin-two',
        'file_path'      => WP_PLUGIN_DIR . '/plugin-two/plugin-two.php',
        'name_slug'      => 'plugin-two',
        'release_branch' => 'stable',
        'token'          => defined( 'PLUGIN_TWO_TOKEN' ) ? PLUGIN_TWO_TOKEN : '',
    ];
    return $packages;
} );
```

---

## Package Configuration Reference

| Key | Type | Required | Default | Description |
|-----|------|----------|---------|-------------|
| `repo` | string | Yes | — | Full GitHub repository URL |
| `file_path` | string | Yes | — | Absolute path to the plugin's main PHP file |
| `name_slug` | string | Yes | — | Unique plugin slug (must be unique across all registered packages) |
| `release_branch` | string | No | `'main'` | Branch the updater watches for new versions |
| `token` | string | No | `''` | GitHub PAT — required for private repos |
| `release-assets` | bool | No | `false` | When `true`, downloads update ZIPs from GitHub Release assets |

---

## How Updates Are Delivered

1. You push a commit to the release branch with a bumped `Version` header (or publish a GitHub Release with an asset if `release-assets` is enabled).
2. When an admin visits the WordPress dashboard, the Plugin Update Checker queries GitHub and compares versions.
3. WordPress shows the standard **"update available"** notice on the Plugins screen.
4. Admins can update the plugin with one click, exactly like a plugin from WordPress.org.

---

## Troubleshooting

| Symptom | Likely cause | Fix |
|---------|-------------|-----|
| No update notice after pushing a new version | `Version` header not bumped | Bump `Version:` in the main plugin file and push |
| 404 / authentication errors | Private repo without a token | Add a valid `token` to the package config |
| Update notice but download fails | Wrong `file_path` | Ensure `file_path` is the absolute path to the plugin's main PHP file |
| Update shows but wrong ZIP downloaded | `release-assets` mismatch | Set `'release-assets' => true` only if you attach ZIP assets to GitHub Releases |
| Updates checked too infrequently | Plugin Update Checker cache | Clear transients via WP-CLI: `wp transient delete --all` |

---

## License

GPL-2.0-or-later
