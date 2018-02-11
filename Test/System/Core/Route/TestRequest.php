<?php

/**
 * Requestのテスト
 */

namespace Test\System\Core\Route;

use System\Core\Extend\ExtendProtocol;
use System\Core\Route\Request;
use System\Type\Resource\File;
use System\Type\Resource\Image;
use Test\Mock;
use Test\TestHelper;

class TestRequest extends TestHelper
{
	/**
	 * __construct
	 */
	public function __constructTest()
	{
		$this->compareInstance('System\Core\Route\Request', new Request());
	}

	/**
	 * get
	 */
	public function getTest()
	{
		$request = Mock::m('System\Core\Route\Request');
		$request->get = ['hoge' => 99];

		$this->compareValue(['hoge' => 99], $request->get(), '引数無し');
		$this->compareValue(99, $request->get('hoge'), '第一引数のみ');
		$this->compareValue(100, $request->get(null, 100), 'デフォルト値を設定');
	}

	/**
	 * post
	 */
	public function postTest()
	{
		$request = Mock::m('System\Core\Route\Request');
		$request->post = ['hoge' => 99];

		$this->compareValue(['hoge' => 99], $request->post(), '引数無し');
		$this->compareValue(99, $request->post('hoge'), '第一引数のみ');
		$this->compareValue(100, $request->post(null, 100), 'デフォルト値を設定');
	}

	/**
	 * server
	 */
	public function serverTest()
	{
		$request = Mock::m('System\Core\Route\Request');
		$request->server = ['hoge' => 99];

		$this->compareValue(['hoge' => 99], $request->server(), '引数無し');
		$this->compareValue(99, $request->server('hoge'), '第一引数のみ');
		$this->compareValue(100, $request->server(null, 100), 'デフォルト値を設定');
	}

	/**
	 * file
	 */
	public function fileTest()
	{
		$request  = Mock::m('System\Core\Route\Request');

		$filePath = str_replace(sprintf('%s://', ExtendProtocol::PROTOCOL), '', __FILE__);
		$fileList = [
			'hoge' => [
				'name'     => 'test',
				'tmp_name' => $filePath 
			]
		];
		$request->files = $fileList;

		$request->_setMethod('getMimeType')
			->_setArgs()
			->_setReturn('otherType')
			->e();
		$expectList = ['hoge' => new File($filePath, 'test')];

		$this->compareValueLax($expectList, $request->file(), '引数無し(File)');
		$this->compareValueLax($expectList['hoge'], $request->file('hoge'), '第一引数のみ(File)');
		$this->compareValueLax(100, $request->file(null, 100), 'デフォルト値を設定(File)');

		$fileList = [
			'hoge' => [
				'name'     => 'test',
				'tmp_name' => null 
			]
		];
		$request->files = $fileList;
		$request->_setMethod('getMimeType')
			->_setArgs(null)
			->_setReturn('image/png')
			->e();

		$expectList = ['hoge' => new Image(null, 'test')];

		$this->compareValueLax($expectList, $request->file(), '引数無し(Image)');
		$this->compareValueLax($expectList['hoge'], $request->file('hoge'), '第一引数のみ(Image)');
		$this->compareValueLax(100, $request->file(null, 100), 'デフォルト値を設定(Image)');
	}

	/**
	 * getMimeType
	 */
	public function getMimeTypeTest()
	{
		$request  = Mock::m('System\Core\Route\Request');
		$filePath = str_replace(sprintf('%s://', ExtendProtocol::PROTOCOL), '', __FILE__);
		$this->compareValue('text/x-php', $request->getMimeType($filePath));
	}

	/**
	 * json
	 */
	public function jsonTest()
	{
		$request = Mock::m('System\Core\Route\Request');
		$this->compareValue(null, $request->json());
	}

	/**
	 * getControllerNameSpace
	 */
	public function getControllerNameSpaceTest()
	{
		$uriList = ['admin', 'index'];

		$request = Mock::m('System\Core\Route\Request');
		$request->_setMethod('getUri')
			->_setArgs()
			->_setReturn($uriList)
			->e();

		$this->compareValue('App\Controller\AdminController', $request->getControllerNameSpace());
	}

	/**
	 * getControllerMethod
	 */
	public function getControllerMethodTest()
	{
		$uriList = ['admin', 'index'];

		$request = Mock::m('System\Core\Route\Request');
		$request->_setMethod('getUri')
			->_setArgs()
			->_setReturn($uriList)
			->e();

		$this->compareValue('indexAction', $request->getControllerMethod());
	}

	/**
	 * getUri
	 */
	public function getUri()
	{
		$uriList = ['admin', 'index'];

		$request = Mock::m('System\Core\Route\Request');
		$request->server = ['REQUEST_URI' => '/admin/index?id=1'];

		$this->compareValue($uriList, $request->getUri());
	}
}

