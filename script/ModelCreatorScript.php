<?php

/**
 * モデルのエンティティとスケルトンを生成するスクリプト
 */

require_once('./config.php');
require_once('./use.php');

if (!isset($argv[1])) {
	echo 'ERROR: 名前空間が指定されていない' . PHP_EOL;
	exit;
}

$namespace = $argv[1]; //名前空間を受け取る
$modelCreator = $container->get('System\Util\ModelCreator');

try {
	$modelCreator->initialize($namespace);
} catch (SystemException $e) {
	echo sprintf('ERROR: %s', $e->getMessage()) . PHP_EOL;
	exit;
}

$modelCreator->makeDirectory();

$message = sprintf('%sモデルの', $modelCreator->getModelName());
if (false === $modelCreator->existEntity()) {
	$modelCreator->createEntity();
	$message .= 'Entityと';
}
$modelCreator->createSkeleton();
$message .= 'Skeletonを生成しました';

echo $message . PHP_EOL;
