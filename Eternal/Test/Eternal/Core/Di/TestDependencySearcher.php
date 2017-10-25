<?php

/**
 * DependencySearcherのテスト
 */

namespace Test\System\Core\Di;

use Test\Mock;
use Test\TestHelper;
use System\Exception\SystemException;
use System\Core\Di\DependencyDto;
use System\Core\Di\DependencySearcher;
use System\Util\FilePathSearcher;

class TestDependencySearcher extends TestHelper
{
	/**
	 * __construct
	 */
	public function __constructTest()
	{
		$this->compareInstance('System\Core\Di\DependencySearcher', new DependencySearcher(new FilePathSearcher()));
	}

	/**
	 * search
	 * getRecursive
	 * getDependencyDtoList
	 */
	public function searchAndGetRecursiveAndGetDependencyDtoListTest()
	{
		$dependencySearcher = new DependencySearcher(new FilePathSearcher());
		$dependencyDtoList  = ['Test\Hoge' => new DependencyDto('Test\Hoge', [])];
		$resultList         = $dependencySearcher->search('Test\Hoge');

		$this->compareValueLax($resultList, $dependencyDtoList, 'search: 依存が無い場合');
		$this->compareValueLax($dependencySearcher->getDependencyDtoList(), $dependencyDtoList, '依存が無い場合');

		$dependencySearcher = new DependencySearcher(new FilePathSearcher());
		$dependencyDtoList  = [
			'Test\Fuga' => new DependencyDto('Test\Fuga', ['Test\Hoge']),
			'Test\Hoge' => new DependencyDto('Test\Hoge', [])
		];
		$resultList = $dependencySearcher->search('Test\Fuga');

		$this->compareValueLax($resultList, $dependencyDtoList, 'search: 依存がある場合');
		$this->compareValueLax($dependencySearcher->getDependencyDtoList(), $dependencyDtoList, '依存がある場合');
	}

	/**
	 * searchByTable
	 */
	public function searchByTableTest()
	{
		$filePathList = [MODEL_DIR . 'Hoge.php'];
		$namespace    = 'App\Model\Hoge';
		$table        = 'hoge_tbl';

		$mockC = Mock::m('System\Util\FilePathSearcher')
			->_setMethod('search')
			->_setArgs()
			->_setReturn($filePathList)
			->e();
		$mockB = Mock::m('System\Util\FilePathSearcher')
			->_setMethod('setUnUseDir')
			->_setArgs(getUnUseDirs())
			->_setReturn($mockC)
			->e();
		$mockA = Mock::m('System\Util\FilePathSearcher')
			->_setMethod('setUseDir')
			->_setArgs([MODEL_DIR])
			->_setReturn($mockB)
			->e();

		$dependencySearcher = Mock::m('System\Core\Di\DependencySearcher')
			->_setMethod('search')
			->_setArgs($namespace)
			->_setReturn(null)
			->e();

		$dependencySearcher->filePathSearcher = $mockA;
		$result = $dependencySearcher->searchByTable($table);

		$this->compareValue($namespace, $result, 'テーブルに対応するモデルが存在しない場合');

		try {
			$table   = 'not_exist_tbl';
			$message = sprintf('テーブル名(%s)に対応するモデルが存在しません', $table);
			$dependencySearcher->searchByTable($table);
			$this->throwError('例外が発生すべき箇所で発生していない');
		} catch (SystemException $e) {
			$this->compareException($message, $e, 'テーブルに対応するモデルが存在しない場合');
		}
	}
}

namespace Test;

class Hoge
{
}

namespace Test;

class Fuga
{
	private $hoge;

	public function __construct(Hoge $hoge)
	{
		$this->hoge = $hoge;
	}
}

namespace App\Model;

class Hoge
{
}
