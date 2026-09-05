<?php

namespace Tests\Unit;

require_once __DIR__ . '/../../app/Plugin/Security/CanonicalPayloadBuilder.php';

use App\Plugin\Security\CanonicalPayloadBuilder;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CanonicalPayloadBuilderTest extends TestCase
{
    public function test_same_input_produces_byte_for_byte_identical_canonical_json()
    {
        $data1 = ['version' => '1.0.0', 'name' => 'Demo Plugin', 'alias' => 'demo'];
        $data2 = ['version' => '1.0.0', 'name' => 'Demo Plugin', 'alias' => 'demo'];

        $canonical1 = CanonicalPayloadBuilder::canonicalizeJson($data1);
        $canonical2 = CanonicalPayloadBuilder::canonicalizeJson($data2);

        $this->assertSame($canonical1, $canonical2);
    }

    public function test_different_key_ordering_produces_identical_canonical_output()
    {
        $unordered1 = ['z_key' => 'last', 'a_key' => 'first', 'm_key' => ['nested_z' => 2, 'nested_a' => 1]];
        $unordered2 = ['a_key' => 'first', 'm_key' => ['nested_a' => 1, 'nested_z' => 2], 'z_key' => 'last'];

        $canonical1 = CanonicalPayloadBuilder::canonicalizeJson($unordered1);
        $canonical2 = CanonicalPayloadBuilder::canonicalizeJson($unordered2);

        $this->assertSame('{"a_key":"first","m_key":{"nested_a":1,"nested_z":2},"z_key":"last"}', $canonical1);
        $this->assertSame($canonical1, $canonical2);
    }

    public function test_different_whitespace_and_newlines_produce_identical_canonical_output()
    {
        $jsonStringWithWhitespace = "{\n  \"name\": \"Test\",\r\n  \"version\": \"1.0.0\"\n}";
        $data = json_decode($jsonStringWithWhitespace, true);

        $canonical = CanonicalPayloadBuilder::canonicalModuleManifest($data);
        $this->assertSame('{"name":"Test","version":"1.0.0"}', $canonical);
    }

    public function test_path_normalization()
    {
        $input = 'src\\Controllers\\..\\Controllers\\TestController.php';
        $normalized = CanonicalPayloadBuilder::normalizeAndValidateRelativePath($input);

        $this->assertSame('src/Controllers/TestController.php', $normalized);
    }

    public function test_absolute_path_rejection()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Absolute path violates package security rule");

        CanonicalPayloadBuilder::normalizeAndValidateRelativePath('/etc/passwd');
    }

    public function test_path_traversal_rejection()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Path traversal ('..') violates package security rule");

        CanonicalPayloadBuilder::normalizeAndValidateRelativePath('app/../../secret.txt');
    }

    public function test_duplicate_normalized_path_rejection()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Duplicate normalized path detected");

        $checksums = [
            'src/index.php' => 'hash1',
            'src\\index.php' => 'hash2',
        ];

        CanonicalPayloadBuilder::canonicalChecksums($checksums);
    }

    public function test_modified_file_produces_different_checksum()
    {
        $tempDir = sys_get_temp_dir() . '/test_plugin_' . uniqid();
        mkdir($tempDir);
        file_put_contents($tempDir . '/module.json', json_encode(['name' => 'Test']));
        file_put_contents($tempDir . '/index.php', '<?php echo "v1";');

        $checksums1 = CanonicalPayloadBuilder::buildFileChecksums($tempDir);

        file_put_contents($tempDir . '/index.php', '<?php echo "v2";');
        $checksums2 = CanonicalPayloadBuilder::buildFileChecksums($tempDir);

        $this->assertNotEquals($checksums1['index.php'], $checksums2['index.php']);

        // Cleanup
        unlink($tempDir . '/module.json');
        unlink($tempDir . '/index.php');
        rmdir($tempDir);
    }

    public function test_modified_checksums_produces_different_signed_payload()
    {
        $manifest = CanonicalPayloadBuilder::canonicalModuleManifest(['name' => 'Demo', 'version' => '1.0']);
        $checksums1 = CanonicalPayloadBuilder::canonicalChecksums(['index.php' => 'hash_a']);
        $checksums2 = CanonicalPayloadBuilder::canonicalChecksums(['index.php' => 'hash_b']);

        $payload1 = CanonicalPayloadBuilder::buildSignedPayload($manifest, $checksums1);
        $payload2 = CanonicalPayloadBuilder::buildSignedPayload($manifest, $checksums2);

        $this->assertNotEquals($payload1, $payload2);
    }
}
