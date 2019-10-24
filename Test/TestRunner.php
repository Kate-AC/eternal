<?php

/**
 * テスト一括実行ファイル
 */

include('../config.php');
include('../use.php');
include('./Phantom/Phantom.php');
include('./Phantom/Reborn.php');

use System\Util\FilePathSearcher;
use System\Util\Kit;

$filePathSearcher = new FilePathSearcher();

$testFilePathList = $filePathSearcher->setUseDir([TEST_DIR . 'System/'])
    ->setUnUseDir(getUnUseDirs())
    ->search();

foreach ($testFilePathList as $filePath) {
    $container->get(Kit::pathToNameSpace($filePath))->run();
}

exec('rm -fR ./Phantom/tmp/*');

