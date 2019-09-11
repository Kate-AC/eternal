<?php

/**
 * 初動に必要なファイルを宣言する 
 */

require(SOURCE_DIR    . 'vendor/autoload.php');
require(FRAMEWORK_DIR . 'Core/AutoLoader.php');
require(FRAMEWORK_DIR . 'Core/Extend/ExtendProtocol.php');
require(FRAMEWORK_DIR . 'Core/Extend/Module/AbstractModule.php');
require(FRAMEWORK_DIR . 'Core/Extend/Module/RenderModule.php');
require(FRAMEWORK_DIR . 'Log/AbstractLogger.php');
require(FRAMEWORK_DIR . 'Log/SystemErrorLogger.php');
require(FRAMEWORK_DIR . 'Exception/AbstractException.php');
require(FRAMEWORK_DIR . 'Exception/SystemException.php');
require(FRAMEWORK_DIR . 'NotClass/DebugFunction.php');
require(FRAMEWORK_DIR . 'Util/FilePathSearcher.php');
require(FRAMEWORK_DIR . 'Util/StringOperator.php');

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

