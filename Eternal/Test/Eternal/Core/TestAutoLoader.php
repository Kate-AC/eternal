<?php

/**
 * AutoLoaderのテスト
 */

namespace Test\System\Core;

use Test\Mock;
use Test\TestHelper;
use System\Core\AutoLoader;
use System\Util\FilePathSearcher;
use System\Util\StringOperator;
use System\Exception\SystemException;

class TestAutoLoader extends TestHelper
{
	/**
	 * __construct
	 */
	public function __constructTest()
	{
		$autoLoader = new AutoLoader(new FilePathSearcher());
		$this->compareInstance('System\Core\AutoLoader', $autoLoader);
	}

	/**
	 * autoLoad
	 */
	public function autoLoadTest()
	{
		$autoLoader      = Mock::m('System\Core\AutoLoader');
		$namespace       = get_class($this);
		$allFilePathList = [
			FRAMEWORK_DIR . 'Test/Eternal/AaBb.php'     => FRAMEWORK_DIR . 'Test/Eternal/AaBb.php',
			FRAMEWORK_DIR . 'Test/Eternal/CcDd.php'     => FRAMEWORK_DIR . 'Test/Eternal/CcDd.php',
			StringOperator::nameSpaceToPath($namespace) => StringOperator::nameSpaceToPath($namespace)
		];

		$filePathSearcher = Mock::m('System\Util\FilePathSearcher')
			->_setMethod('getAllFilePathList')
			->_setArgs()
			->_setReturn([])
			->e();
		$filePathSearcher->_setMethod('search')
			->_setArgs()
			->_setReturn($allFilePathList)
			->e();

		$autoLoader->filePathSearcher = $filePathSearcher;
		$this->compareValue(null, $autoLoader->autoLoad($namespace), 'クラスが見つかった場合');

		$namespace = 'NotExist';
		$message   = '存在しないクラス(NotExist)が呼ばれた';
		try {
			$autoLoader->autoLoad($namespace);
			$this->throwError('例外が発生すべき箇所で発生していない');
		} catch (SystemException $e) {
			$this->compareException($message, $e, '存在しない名前空間を指定した場合');
		}
	}
}

