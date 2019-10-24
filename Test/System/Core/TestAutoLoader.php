<?php

/**
 * AutoLoaderのテスト
 */

namespace Test\System\Core;

use Phantom\Phantom;
use Test\TestHelper;
use System\Core\AutoLoader;
use System\Util\FilePathSearcher;
use System\Util\Kit;
use System\Util\Str;
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
        $autoLoader      = Phantom::m('System\Core\AutoLoader');
        $namespace       = get_class($this);
        $allFilePathList = [
            TEST_DIR . 'Eternal/AaBb.php'    => TEST_DIR . 'Eternal/AaBb.php',
            TEST_DIR . 'Eternal/CcDd.php'    => TEST_DIR . 'Eternal/CcDd.php',
            Kit::nameSpaceToPath($namespace) => Kit::nameSpaceToPath($namespace)
        ];

        $filePathSearcher = Phantom::m('System\Util\FilePathSearcher')
            ->setMethod('getAllFilePathList')
            ->setArgs()
            ->setReturn([])
            ->exec();
        $filePathSearcher
            ->setMethod('search')
            ->setArgs()
            ->setReturn($allFilePathList)
            ->exec();

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
