# Agent Reference: wpb-updater-checker-github

## Purpose

This is a Composer library (`wpboilerplate/wpb-updater-checker-github`) that wires GitHub-hosted WordPress plugins into WordPress's native update system. It wraps the [yahnis-elsts/plugin-update-checker](https://github.com/YahnisElsts/plugin-update-checker) library and exposes a minimal, filter-based API for registering one or more plugins for automatic update checking.

## File Map

| File | Role |
|------|------|
| `index.php` | Defines the single class; autoloaded by Composer via `files` autoload |
| `composer.json` | Package metadata; declares `yahnis-elsts/plugin-update-checker` and `automattic/jetpack-autoloader` (`^5.0`) dependencies |
| `.gitignore` | Standard WordPress/Composer ignores |

## Class: `WPBoilerplate_Updater_Checker_Github`

**Namespace:** global (no namespace)  
**Defined in:** `index.php:28`  
**Guard:** wrapped in `class_exists` check — safe to include multiple times.

### Property

```php
public array $packages = [];
```

Stores package configurations added via the constructor. Additional packages can be injected at runtime through the filter (see below).

### `__construct( array $package = [] )`  — `index.php:42`

- Appends `$package` to `$this->packages` if non-empty.
- Registers `$this->updater` on `admin_init` at priority **1000** (very late, after all plugins are loaded).
- Instantiate once per logical group of packages, or once per plugin that depends on this library.

### `get_packages() : array`  — `index.php:57`

Returns the package list after passing it through the filter:

```
apply_filters( 'wpboilerplate_updater_checker_github', $this->packages )
```

Use this filter to register packages without instantiating the class yourself (e.g. from a mu-plugin or another plugin).

### `updater()`  — `index.php:64`

Runs on `admin_init` (priority 1000). For every package returned by `get_packages()`:

1. Calls `PucFactory::buildUpdateChecker( $repo, $file_path, $name_slug )`.
2. Sets the release branch (defaults to `'main'`).
3. If `token` is set → calls `setAuthentication( $token )`.
4. If `release-assets` is truthy → calls `getVcsApi()->enableReleaseAssets()`.

Only executes when `is_admin()` is true.

## Package Array Schema

```php
[
    'repo'            => 'https://github.com/ORG/REPO',  // required
    'file_path'       => plugin_dir_path(__FILE__) . 'my-plugin.php', // required — absolute path to main plugin file
    'name_slug'       => 'my-plugin',                    // required — unique slug
    'release_branch'  => 'main',                         // optional, defaults to 'main'
    'token'           => 'ghp_xxxxxxxxxxxx',             // optional — GitHub PAT for private repos
    'release-assets'  => false,                          // optional — set true to use GitHub Release ZIP assets
]
```

## WordPress Hooks

| Hook | Type | Priority | What it does |
|------|------|----------|--------------|
| `admin_init` | action | 1000 | Calls `updater()` to register all packages with Plugin Update Checker |
| `wpboilerplate_updater_checker_github` | filter | — | Filters the `$packages` array inside `get_packages()` |

## Dependencies

| Package | Constraint | Purpose |
|---------|-----------|---------|
| `yahnis-elsts/plugin-update-checker` | `dev-master` | GitHub API communication, version comparison, WordPress update UI integration |
| `automattic/jetpack-autoloader` | `^5.0` | Composer autoloader for Jetpack-style multi-version class loading |

## How Update Detection Works

Plugin Update Checker reads the `Version` header from the plugin's main PHP file. To trigger an update notification:

1. Bump the `Version` header in the plugin's main file and commit/push to the configured branch **or** create a GitHub Release.
2. WordPress's update system (or an admin page refresh) polls the checker.
3. The checker compares the installed version against the version in the branch/release and shows the standard WordPress "update available" notice.

## Adding Packages via Filter (no class instantiation required)

```php
add_filter( 'wpboilerplate_updater_checker_github', function( $packages ) {
    $packages[] = [
        'repo'           => 'https://github.com/MyOrg/my-plugin',
        'file_path'      => WP_PLUGIN_DIR . '/my-plugin/my-plugin.php',
        'name_slug'      => 'my-plugin',
        'release_branch' => 'main',
    ];
    return $packages;
} );
```

This only works if at least one instance of `WPBoilerplate_Updater_Checker_Github` has already been constructed (so the `admin_init` hook is registered).
