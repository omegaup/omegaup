<?php
// Merge per-worker PHPUnit coverage dumps into one Clover file.
$autoload = $argv[1] ?? '';
$dir = $argv[2] ?? '';
$dest = $argv[3] ?? '';
if ($autoload === '' || $dir === '' || $dest === '') {
    fwrite(STDERR, "usage: merge-coverage.php autoload.php runfiles-dir coverage.xml\n");
    exit(1);
}
require $autoload;

$merged = null;
foreach (glob($dir . '/coverage-*.php') ?: [] as $file) {
    $coverage = include $file;
    if (!$coverage instanceof SebastianBergmann\CodeCoverage\CodeCoverage) {
        continue;
    }
    if ($merged === null) {
        $merged = $coverage;
    } else {
        $merged->merge($coverage);
    }
}
if ($merged === null) {
    fwrite(STDERR, "no coverage dumps in {$dir}\n");
    exit(1);
}
(new SebastianBergmann\CodeCoverage\Report\Clover())->process($merged, $dest);
