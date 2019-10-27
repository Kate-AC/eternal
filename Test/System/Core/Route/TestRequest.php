<?php

/**
 * Requestのテスト
 */

namespace Test\System\Core\Route;

use System\Core\Extend\ExtendProtocol;
use System\Core\Route\Request;
use System\Core\Route\Route;
use System\Type\Resource\File;
use System\Type\Resource\Image;
use Phantom\Phantom;
use Test\TestHelper;

class TestRequest extends TestHelper
{
    /**
     * __construct
     */
    public function __constructTest()
    {
        $route = new Route();
        $route->set(['/' => 'hoge@fuga']);
        $this->compareInstance('System\Core\Route\Request', new Request($route));
    }

    /**
     * get
     */
    public function getTest()
    {
        $request = Phantom::m('System\Core\Route\Request');
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
        $request = Phantom::m('System\Core\Route\Request');
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
        $request = Phantom::m('System\Core\Route\Request');
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
        $request  = Phantom::m('System\Core\Route\Request');
        $filePath = str_replace(sprintf('%s://', ExtendProtocol::PROTOCOL), '', __FILE__);
        $fileList = [
            'hoge' => [
                'name'     => 'test',
                'tmp_name' => $filePath
            ]
        ];
        $request->files = $fileList;

        $request
            ->setMethod('getMimeType')
            ->setArgs()
            ->setReturn('otherType')
            ->exec();
        $expectList = ['hoge' => new File($filePath, 'test')];

        $this->compareValueLax($expectList, $request->file(), '引数無し(File)');
        $this->compareValueLax($expectList['hoge'], $request->file('hoge'), '第一引数のみ(File)');
        $this->compareValueLax(100, $request->file(null, 100), 'デフォルト値を設定(File)');

        $tmpName = 'tmpName';
        $fileList = [
            'hoge' => [
                'name'     => 'test',
                'tmp_name' => 'tmpName'
            ]
        ];
        $request->files = $fileList;
        $request
            ->setMethod('getMimeType')
            ->setArgs('tmpName')
            ->setReturn('image/png')
            ->exec();

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
        $request  = Phantom::m('System\Core\Route\Request');
        $filePath = str_replace(sprintf('%s://', ExtendProtocol::PROTOCOL), '', __FILE__);
        $this->compareValue('text/x-php', $request->getMimeType($filePath));
    }

    /**
     * json
     */
    public function jsonTest()
    {
        $request = Phantom::m('System\Core\Route\Request');
        $this->compareValue(null, $request->json());
    }

    /**
     * getControllerNameSpace
     */
    public function getControllerNameSpaceTest()
    {
        $route = new Route();
        $route->set(['/' => 'App\Controller\AdminController@fuga']);
        $request = new Request($route);

        $this->compareValue('App\Controller\AdminController', $request->getControllerNameSpace());
    }

    /**
     * getControllerMethod
     */
    public function getControllerMethodTest()
    {
        $route = new Route();
        $route->set(['/' => 'App\Controller\AdminController@fuga']);
        $request = new Request($route);

        $this->compareValue('fuga', $request->getControllerMethod());
    }
}

