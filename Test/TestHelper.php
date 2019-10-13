<?php

/**
 * テストを補助するクラス
 */

namespace Test;

use System\Core\Di\Container;

class TestHelper
{
    /**
     * @var ind
     */
    private static $testCount = 0;

    /**
     * @var Container
     */
    protected $container;

    /**
     * コンストラクタ
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * テストを実行する
     */
    public function run()
    {
        $methodList = (new \ReflectionClass(get_called_class()))->getMethods();
        foreach ($methodList as $method) {
            if (1 !== preg_match('/^.+Test.*$/', $method->name)) {
                continue;
            }

            if (__CLASS__ !== $method->class) {
                $methodName = $method->name;
                $this->$methodName();
            }
        }
    }

    /**
     * 値を厳密比較する
     *
     * @param mixed  $expect
     * @param mixed  $result
     * @param string $when
     */
    protected function compareValue($expect, $result, $when = null)
    {
        if ($expect === $result) {
            $this->echoSuccessMessage($when);
        } else {
            $this->echoErrorMessage($when);
        }
    }

    /**
     * 値をゆるやかに比較する
     *
     * @param mixed  $expect
     * @param mixed  $result
     * @param string $when
     */
    protected function compareValueLax($expect, $result, $when = null)
    {
        if ($expect == $result) {
            $this->echoSuccessMessage($when);
        } else {
            $this->echoErrorMessage($when);
        }
    }

    /**
     * 一致しないことを確認する
     *
     * @param mixed  $expect
     * @param mixed  $result
     * @param string $when
     */
    protected function compareNotMatch($expect, $result, $when = null)
    {
        if ($expect !== $result) {
            $this->echoSuccessMessage($when);
        } else {
            $this->echoErrorMessage($when);
        }
    }

    /**
     * インスタンスを比較する
     *
     * @param mixed  $expect
     * @param mixed  $result
     * @param string $when
     */
    protected function compareInstance($expect, $result, $when = null)
    {
        if ($result instanceof $expect) {
            $this->echoSuccessMessage($when);
        } else {
            $this->echoErrorMessage($when);
        }
    }

    /**
     * 例外メッセージを比較する
     *
     * @param mixed  $expect
     * @param mixed  $result
     * @param string $when
     */
    protected function compareException($expect, \Exception $result, $when = null)
    {
        if (1 === preg_match(sprintf('/%s/', preg_quote($expect, '/[^a-zA-Z0-9]/')), $result->getMessage())) {
            $this->echoSuccessMessage($when);
        } else {
            $this->echoErrorMessage($when);
        }
    }

    /**
     * 成功時のメッセージを出力する
     *
     * @param string $when
     */
    private function echoSuccessMessage($when = null)
    {
        $when = !is_null($when) ? sprintf('WHEN %s', $when) : null;

        $list       = debug_backtrace();
        $methodName = $list[2]['function'];

        self::$testCount++;
        echo sprintf("\033[0;32m%s OK [%s] %s() %s\033[0m",
            self::$testCount,
            get_called_class(),
            $methodName,
            $when
        ) . PHP_EOL;
    }

    /**
     * 失敗時のメッセージを出力する
     *
     * @param string $when
     */
    private function echoErrorMessage($when = null)
    {
        $when = !is_null($when) ? sprintf('WHEN %s', $when) : null;

        $list       = debug_backtrace();
        $methodName = $list[2]['function'];

        self::$testCount++;
        echo sprintf("\033[0;31m%s ERROR [%s] %s() %s\033[0m",
            self::$testCount,
            get_called_class(),
            $methodName,
            $when
        ) . PHP_EOL;
    }

    /**
     * 意図的にテストをエラーにさせる
     *
     * @param string $when
     */
    protected function throwError($message)
    {
        $this->echoErrorMessage($message);
    }
}
