#!/usr/bin/env bash
set -euo pipefail

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$repo_root"

version="$(grep -m1 -E '^[[:space:]]*\*[[:space:]]*Version:' oxy-ai-readiness.php | sed -E 's/^[[:space:]]*\*[[:space:]]*Version:[[:space:]]*//' | tr -d '\r')"
if [ -z "$version" ]; then
    echo "error: could not read Version: from oxy-ai-readiness.php" >&2
    exit 1
fi
if [ ! -f dist/.vite/manifest.json ]; then
    echo "error: dist/.vite/manifest.json is missing — run 'npm run build' before packaging" >&2
    exit 1
fi
if ! grep -q '^# DEVELOPMENT KEY - REPLACE BEFORE PRODUCTION RELEASE$' resources/updater/public-key.pem; then
    echo "error: updater public key is not explicitly marked as DEVELOPMENT" >&2
    exit 1
fi

build_dir="$repo_root/build"
stage_root="$build_dir/stage"
plugin_dir="$stage_root/oxy-ai-readiness"
rm -rf "$stage_root"
mkdir -p "$plugin_dir"

cp -R app "$plugin_dir/app"
cp -R routes "$plugin_dir/routes"
cp -R dist "$plugin_dir/dist"
cp -R resources "$plugin_dir/resources"
cp oxy-ai-readiness.php "$plugin_dir/oxy-ai-readiness.php"
cp uninstall.php "$plugin_dir/uninstall.php"
cp composer.json "$plugin_dir/composer.json"

composer install --no-dev --optimize-autoloader --no-interaction --working-dir="$plugin_dir"

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
if find "$plugin_dir" -type f \( -name '*private*.pem' -o -name '.env' -o -name '.env.*' \) -print -quit | grep -q .; then
    echo "error: private key or .env file leaked into staged package" >&2
    exit 1
fi
if grep -RIEq --exclude='public-key.pem' --exclude='*.js' --exclude='*.css' '(BEGIN (RSA |EC |OPENSSH )?PRIVATE KEY|AKIA[0-9A-Z]{16}|sk-[A-Za-z0-9]{20,})' "$plugin_dir"; then
    echo "error: secret-like content detected in staged package" >&2
    exit 1
fi

zip_path="$build_dir/oxy-ai-readiness-$version.zip"
rm -f "$zip_path" "$zip_path.md5" "$zip_path.sha256"
php -r '
    $stageRoot = $argv[1];
    $pluginDir = $argv[2];
    $zipPath = $argv[3];
    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
        fwrite(STDERR, "error: could not create zip archive\n"); exit(1);
    }
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($pluginDir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($iterator as $path) {
        $localName = substr($path->getPathname(), strlen($stageRoot) + 1);
        $localName = str_replace(DIRECTORY_SEPARATOR, "/", $localName);
        if ($path->isDir()) { $zip->addEmptyDir($localName); }
        else { $zip->addFile($path->getPathname(), $localName); }
    }
    $zip->close();
' "$stage_root" "$plugin_dir" "$zip_path"

if unzip -Z1 "$zip_path" | grep -Eq '(^|/)(tests|fixtures)(/|$)|test-private\.pem|\.env($|\.)'; then
    echo "error: test fixture/private/.env path found inside production ZIP" >&2
    exit 1
fi
if unzip -p "$zip_path" | grep -aEiq '(BEGIN (RSA |EC |OPENSSH )?PRIVATE KEY|AKIA[0-9A-Z]{16}|sk-[A-Za-z0-9]{20,})'; then
    echo "error: secret-like content found inside production ZIP" >&2
    exit 1
fi

md5="$(php -r 'echo hash_file("md5", $argv[1]);' "$zip_path")"
sha256="$(php -r 'echo hash_file("sha256", $argv[1]);' "$zip_path")"
echo "$md5  $(basename "$zip_path")" > "$zip_path.md5"
echo "$sha256  $(basename "$zip_path")" > "$zip_path.sha256"
rm -rf "$stage_root"

echo "Built: $zip_path"
echo "MD5: $md5"
echo "SHA256: $sha256"
echo "Archive secret scan: PASS"
echo "Test updater fixtures excluded: PASS"
