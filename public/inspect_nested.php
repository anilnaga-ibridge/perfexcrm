<?php

header('Content-Type: text/plain');

$zipPath = __DIR__ . '/../storage/app/private/temp_uploads/hrpayrollibridge-109.zip';
if (file_exists($zipPath)) {
    echo "hrpayrollibridge-109.zip exists\n";
    $zip = new ZipArchive();
    if ($zip->open($zipPath) === true) {
        $nestedZipEntry = null;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (str_ends_with(strtolower($name), '.zip')) {
                $nestedZipEntry = $name;
                echo "Found nested zip: {$name}\n";
                break;
            }
        }
        if ($nestedZipEntry) {
            $tempFile = __DIR__ . '/../storage/app/private/temp_uploads/temp_nested_extract.zip';
            copy('zip://' . $zipPath . '#' . $nestedZipEntry, $tempFile);
            
            $nestedZip = new ZipArchive();
            if ($nestedZip->open($tempFile) === true) {
                echo "Nested ZIP File Count: " . $nestedZip->numFiles . "\n";
                for ($j = 0; $j < $nestedZip->numFiles; $j++) {
                    $nName = $nestedZip->getNameIndex($j);
                    echo "  [$j] => {$nName}\n";
                    if (basename($nName) === 'module.json' || basename($nName) === 'manifest.json' || basename($nName) === 'manifest.php') {
                        echo "    --- Content of {$nName} ---\n";
                        echo $nestedZip->getFromIndex($j) . "\n";
                        echo "    ---------------------------\n";
                    }
                }
                $nestedZip->close();
            } else {
                echo "Failed to open nested ZIP!\n";
            }
            @unlink($tempFile);
        } else {
            echo "No nested zip found in hrpayrollibridge-109.zip!\n";
        }
        $zip->close();
    } else {
        echo "Failed to open main ZIP!\n";
    }
} else {
    echo "Main ZIP does not exist!\n";
}
