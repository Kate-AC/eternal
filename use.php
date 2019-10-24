<?php

/**
 * 初動に必要なファイルを宣言する 
 */

if (file_exists(CURRENT_DIR . 'vendor/autoload.php')) {
    require(CURRENT_DIR . 'vendor/autoload.php');
}
require(SYSTEM_DIR  . 'Core/AutoLoader.php');
require(SYSTEM_DIR  . 'Core/Extend/ExtendProtocol.php');
require(SYSTEM_DIR  . 'Core/Extend/Module/AbstractModule.php');
require(SYSTEM_DIR  . 'Core/Extend/Module/RenderModule.php');
require(SYSTEM_DIR  . 'Log/AbstractLogger.php');
require(SYSTEM_DIR  . 'Log/SystemErrorLogger.php');
require(SYSTEM_DIR  . 'Exception/AbstractException.php');
require(SYSTEM_DIR  . 'Exception/SystemException.php');
require(SYSTEM_DIR  . 'GlobalFunction/DebugFunction.php');
require(SYSTEM_DIR  . 'Util/FilePathSearcher.php');
require(SYSTEM_DIR  . 'Util/Kit.php');
require(SYSTEM_DIR  . 'Util/Str.php');

use System\Core\AutoLoader;
use System\Core\Cache;
use System\Core\Di\Container;
use System\Core\Di\DependencyInjector;
use System\Core\Di\DependencySearcher;
use System\Core\Extend\ExtendProtocol;
use System\Core\Extend\Module\RenderModule;
use System\Core\Route\Dispatcher;
use System\Core\Route\Request;
use System\Log\AbstractLogger;
use System\Log\SystemErrorLogger;
use System\Util\FilePathSearcher;

$filePathSearcher = new FilePathSearcher();
$filePathSearcher->setUseDir(getAutoLoadDirs())->setUnUseDir(getUnUseDirs());

$systemErrorLogger  = new SystemErrorLogger;
$autoLoader         = new AutoLoader($filePathSearcher);
$cache              = new Cache();
$dependencySearcher = new DependencySearcher($filePathSearcher);
$dependencyInjector = new DependencyInjector($cache);
$container          = new Container($autoLoader, $cache, $dependencySearcher, $dependencyInjector);
$extendProtocol     = new ExtendProtocol();
$request            = new Request();
$dispatcher         = new Dispatcher($container, $extendProtocol, $request, $systemErrorLogger);

