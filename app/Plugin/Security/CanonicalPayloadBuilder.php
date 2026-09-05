<?php

namespace App\Plugin\Security;

use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Class CanonicalPayloadBuilder
 *
 * Deterministic JSON canonicalization, checksum generation, and payload building
 * complying with RFC 8785 conventions.
 */
class CanonicalPayloadBuilder
{
    /**
     * Files excluded from checksum calculations.
     */
    private const EXCLUDED_FILES = [
        'signature.pem',
        'signature.json',
        'checksums.json',
        '.DS_Store',
        'Thumbs.db',
    ];

    /**
     * Directory prefixes excluded from checksum calculations.
     */
    private const EXCLUDED_PREFIXES = [
        '__MACOSX/',
        '.git/',
        '.svn/',
    ];

    /**
     * Canonicalize arbitrary JSON data using RFC 8785 sorting conventions.
     */
    public static function canonicalizeJson(mixed $data): string
    {
        $sorted = self::sortKeysRecursively($data);
        $json = json_encode($sorted, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            throw new InvalidArgumentException("Failed to canonicalize JSON: " . json_last_error_msg());
        }

        return str_replace(["\r\n", "\r"], "\n", $json);
    }

    /**
     * Generate canonical string for module.json manifest.
     */
    public static function canonicalModuleManifest(array $manifest): string
    {
        return self::canonicalizeJson($manifest);
    }

    /**
     * Validate and canonicalize file checksums dictionary.
     */
    public static function canonicalChecksums(array $checksums): string
    {
        $normalized = [];

        foreach ($checksums as $relativePath => $hash) {
            $posixPath = self::normalizeAndValidateRelativePath($relativePath);

            if (array_key_exists($posixPath, $normalized)) {
                throw new InvalidArgumentException("Duplicate normalized path detected: '{$posixPath}'");
            }

            $normalized[$posixPath] = strtolower(trim((string)$hash));
        }

        ksort($normalized, SORT_STRING);

        return self::canonicalizeJson($normalized);
    }

    /**
     * Construct the deterministic signed payload string.
     */
    public static function buildSignedPayload(string $canonicalManifest, string $canonicalChecksums): string
    {
        return $canonicalManifest . "\n---\n" . $canonicalChecksums;
    }

    /**
     * Build SHA-256 checksum dictionary for all files in a plugin directory.
     */
    public static function buildFileChecksums(string $directoryPath): array
    {
        $realDirectory = realpath($directoryPath);
        if (!$realDirectory || !is_dir($realDirectory)) {
            throw new InvalidArgumentException("Invalid plugin directory path: '{$directoryPath}'");
        }

        $checksums = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($realDirectory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $absPath = $file->getPathname();
            $relPath = str_replace('\\', '/', substr($absPath, strlen($realDirectory) + 1));

            if (self::isExcludedFile($relPath)) {
                continue;
            }

            $posixPath = self::normalizeAndValidateRelativePath($relPath);

            if (array_key_exists($posixPath, $checksums)) {
                throw new InvalidArgumentException("Duplicate path encountered: '{$posixPath}'");
            }

            $hash = hash_file('sha256', $absPath);
            if ($hash === false) {
                throw new InvalidArgumentException("Failed to compute SHA-256 hash for '{$relPath}'");
            }

            $checksums[$posixPath] = $hash;
        }

        ksort($checksums, SORT_STRING);

        return $checksums;
    }

    /**
     * Normalize relative path to POSIX style and validate against traversal / absolute path violations.
     */
    public static function normalizeAndValidateRelativePath(string $path): string
    {
        $posixPath = str_replace('\\', '/', trim($path));

        // Reject absolute paths
        if (str_starts_with($posixPath, '/') || preg_match('/^[a-zA-Z]:\//', $posixPath)) {
            throw new InvalidArgumentException("Absolute path violates package security rule: '{$path}'");
        }

        $segments = explode('/', $posixPath);
        $normalizedSegments = [];

        foreach ($segments as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..') {
                throw new InvalidArgumentException("Path traversal ('..') violates package security rule: '{$path}'");
            }

            $normalizedSegments[] = $segment;
        }

        if (empty($normalizedSegments)) {
            throw new InvalidArgumentException("Empty relative path is invalid: '{$path}'");
        }

        return implode('/', $normalizedSegments);
    }

    /**
     * Check if a relative path matches excluded signature/metadata files or system artifacts.
     */
    public static function isExcludedFile(string $relPath): bool
    {
        $baseName = basename($relPath);
        if (in_array($baseName, self::EXCLUDED_FILES, true)) {
            return true;
        }

        foreach (self::EXCLUDED_PREFIXES as $prefix) {
            if (str_starts_with($relPath, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Helper method to recursively sort array keys.
     */
    private static function sortKeysRecursively(mixed $data): mixed
    {
        if (!is_array($data)) {
            return $data;
        }

        // Check if list/indexed array or associative map
        $isAssoc = false;
        $keys = array_keys($data);
        foreach ($keys as $i => $key) {
            if ($key !== $i) {
                $isAssoc = true;
                break;
            }
        }

        if ($isAssoc) {
            ksort($data, SORT_STRING);
        }

        foreach ($data as $k => $v) {
            $data[$k] = self::sortKeysRecursively($v);
        }

        return $data;
    }
}
