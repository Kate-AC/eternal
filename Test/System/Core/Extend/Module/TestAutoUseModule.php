<?php

/**
 * AutoUseModuleのテスト
 */

namespace Test\System\Core\Extend\Module;

use System\Core\Extend\Module\AutoUseModule;
use Test\Mock;
use Test\TestHelper;

class TestAutoUseModule extends TestHelper
{
    /**
     * run
     */
    public function runTest()
    {
        $autoUseModule = AutoUseModule::get();
        $path = PUBLIC_DIR . 'HogeFuga.php';
        $data = <<<EOD
<?php

namespace Test\Module;

class A
{
}
EOD;

        $expected = <<<EOD
<?php

namespace Test\Module;

use System\Type\Primitive\Boolean;
use System\Type\Primitive\Float;
use System\Type\Primitive\Int;
use System\Type\Primitive\String;

class A
{
}
EOD;
        $this->compareValue(
            str_replace(["\r", "\n"], '', $expected),
            str_replace(["\r", "\n"], '', $autoUseModule->run($path, $data))
        );
    }
}
