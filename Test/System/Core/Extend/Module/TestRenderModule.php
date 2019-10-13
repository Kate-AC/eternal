<?php

/**
 * RenderModuleのテスト
 */

namespace Test\System\Core\Extend\Module;

use System\Core\Extend\Module\RenderModule;
use Test\Mock;
use Test\TestHelper;

class TestRenderModule extends TestHelper
{
    /**
     * setClassForView
     * run
     */
    public function setClassForViewAndRunTest()
    {
        $renderModule = RenderModule::get();
        $renderModule->setClassForView('StaticClass', 'Test\StaticClass');
        $path = TEMPLATE_DIR . 'HogeFuga.php';

        $data = <<<EOD
{{\$hoge}}
{{if (1 === \$fuga):}}
{{else:}}
{{endif;}}
{{StaticClass::hoge()}}
EOD;

        $expected = <<<EOD
<?php echo \$hoge; ?>
<?php if (1 === \$fuga): ?>
<?php else: ?>
<?php endif; ?>
<?php echo Test\StaticClass::hoge(); ?>
EOD;

        $this->compareValue(
            str_replace(["\r", "\n"], '', $expected),
            str_replace(["\r", "\n"], '', $renderModule->run($path, $data))
        );
    }
}
