<?php

/**
 * テスト一括実行ファイル
 */

require('../../config.php');
require('../../use.php');

use System\Util\FilePathSearcher;
use System\Util\StringOperator;

$filePathSearcher = new FilePathSearcher();
$testFilePathList = $filePathSearcher->setUseDir([FRAMEWORK_DIR . 'Test/Eternal/'])
	->setUnUseDir(getUnUseDirs())
	->search();

foreach ($testFilePathList as $filePath) {
	$container->get(StringOperator::pathToNameSpace($filePath))->run();
}

