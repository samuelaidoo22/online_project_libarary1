<?php
require_once __DIR__ . '/../includes/functions.php';

$passed = 0;
$failed = 0;
$assert = static function (bool $condition, string $name) use (&$passed, &$failed): void {
    if ($condition) {
        $passed++;
        echo "PASS: {$name}\n";
    } else {
        $failed++;
        echo "FAIL: {$name}\n";
    }
};

$assert(normalize_keywords('AI, ai, Security, ') === 'AI, ai, Security', 'keywords are trimmed and empty values removed');
$assert(normalize_authors(['  Ada   Lovelace ', '', 'Grace Hopper']) === ['Ada Lovelace', 'Grace Hopper'], 'authors are normalized');

$validPath = tempnam(sys_get_temp_dir(), 'gctu_pdf_');
file_put_contents($validPath, "%PDF-1.4\nvalid test fixture");
$validFile = ['error' => UPLOAD_ERR_OK, 'size' => filesize($validPath), 'name' => 'project.pdf', 'tmp_name' => $validPath];
[$valid, $validError] = validate_pdf_upload($validFile);
$assert($valid, 'PDF signature is accepted');

$invalidPath = tempnam(sys_get_temp_dir(), 'gctu_file_');
file_put_contents($invalidPath, 'not a pdf');
$invalidFile = ['error' => UPLOAD_ERR_OK, 'size' => filesize($invalidPath), 'name' => 'project.txt', 'tmp_name' => $invalidPath];
[$isInvalid, $invalidError] = validate_pdf_upload($invalidFile);
$assert(!$isInvalid, 'non-PDF upload is rejected');

@unlink($validPath);
@unlink($invalidPath);
echo "RESULT: {$passed} passed, {$failed} failed\n";
exit($failed === 0 ? 0 : 1);
