#!/usr/bin/env bash
#
# Builds a clean, distributable oxy-ai-readiness.zip from the current
# working tree: a production-only Composer install (no dev tooling),
# the already-built admin SPA (assets/react -> dist/, via `npm run
# build`, run separately/beforehand), and none of this repo's own
# development files (tests, docs, .project, CI config, node_modules,
# source .ts/.tsx, lint/build config).
#
# Per docs/28-Testing-Strategy.md's "PACKAGE TESTING" checklist: this
# script is what makes "Development Files Excluded"/"Vendor
# Dependencies Included"/"Assets Built"/"No Secrets"/"Correct
# Version"/"Correct Checksums" true, and is itself checked by
# tests/Integration/PackagingTest.php.
#
# Usage: bash bin/build-release.sh
# Output: build/oxy-ai-readiness-<version>.zip (+ .sha256 checksum)

set -euo pipefail

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$repo_root"

version="$(grep -m1 -E '^[[:space:]]*\*[[:space:]]*Version:' oxy-ai-readiness.php \
    | sed -E 's/^[[:space:]]*\*[[:space:]]*Version:[[:space:]]*//' \
    | tr -d '\r')"

if [ -z "$version" ]; then
    echo "error: could not read Version: from oxy-ai-readiness.php" >&2
    exit 1
fi

if [ ! -f dist/.vite/manifest.json ]; then
    echo "error: dist/.vite/manifest.json is missing — run 'npm run build' before packaging" >&2
    exit 1
fi

build_dir="$repo_root/build"
stage_root="$build_dir/stage"
plugin_dir="$stage_root/oxy-ai-readiness"

rm -rf "$stage_root"
mkdir -p "$plugin_dir"

# Only the files WordPress actually loads at runtime — no dev tooling,
# no test suite, no documentation/control files, no frontend source
# (only its built dist/ output).
cp -R app "$plugin_dir/app"
cp -R routes "$plugin_dir/routes"
cp -R dist "$plugin_dir/dist"
cp oxy-ai-readiness.php "$plugin_dir/oxy-ai-readiness.php"
cp uninstall.php "$plugin_dir/uninstall.php"
cp composer.json "$plugin_dir/composer.json"

# A production-only autoloader for the staged copy — this project has
# zero runtime Composer packages today (every dependency in
# composer.json's require-dev is dev/test/lint tooling), so this
# regenerates just vendor/autoload.php + vendor/composer/*, nothing else.
composer install --no-dev --optimize-autoloader --no-interaction --working-dir="$plugin_dir"

# Real, mechanical checks — not aspirational ones this script can't
# verify (WordPress.org review, PHP lint of every file, etc.).
for forbidden in tests .project docs node_modules assets .git .github .env; do
    if [ -e "$plugin_dir/$forbidden" ]; then
        echo "error: '$forbidden' leaked into the staged package" >&2
        exit 1
    fi
done

if [ -d "$plugin_dir/vendor/phpunit" ] || [ -d "$plugin_dir/vendor/brain" ]; then
    echo "error: dev-only Composer packages leaked into the staged package" >&2
    exit 1
fi

zip_path="$build_dir/oxy-ai-readiness-$version.zip"
rm -f "$zip_path"

php -r '
    $stageRoot = $argv[1];
    $pluginDir = $argv[2];
    $zipPath = $argv[3];

    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
        fwrite(STDERR, "error: could not create zip archive\n");
        exit(1);
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($pluginDir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $path) {
        $localName = substr($path->getPathname(), strlen($stageRoot) + 1);
        $localName = str_replace(DIRECTORY_SEPARATOR, "/", $localName);

        if ($path->isDir()) {
            $zip->addEmptyDir($localName);
        } else {
            $zip->addFile($path->getPathname(), $localName);
        }
    }

    $zip->close();
' "$stage_root" "$plugin_dir" "$zip_path"

checksum="$(php -r 'echo hash_file("sha256", $argv[1]);' "$zip_path")"
echo "$checksum  $(basename "$zip_path")" > "$zip_path.sha256"

rm -rf "$stage_root"

echo "Built: $zip_path"
echo "SHA256: $checksum"
