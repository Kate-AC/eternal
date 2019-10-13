<?php

/**
 * モック用クラス
 */

namespace Test;

class Mock
{
    /**
     * @var mixed[]
     */
    private $methodList = [];

    /**
     * @var mixed[]
     */
    private $originalMethodList = [];

    /**
     * @var mixed[]
     */
    private $propertyList = [];

    /**
     * @var mixed[]
     */
    private $current = [];

    /**
     * @var \ReflectionClass
     */
    private $class;

    /**
     * @var boolean
     */
    private $isStub;

    /**
     * コンストラクタ
     *
     * @param string $namespace
     */
    private function __construct($namespace = null)
    {
        if (!is_null($namespace)) {
            $this->isStub       = false;
            $reflection         = new \ReflectionClass($namespace);
            $this->class        = $reflection->newInstanceWithoutConstructor();
            $originalMethodList = $reflection->getMethods();
            $propertyList       = $reflection->getProperties();

            foreach ($propertyList as $property) {
                $property->setAccessible(true);
                if ($property->isStatic()) {
                    $this->propertyList[$property->name] = $property->getValue();
                } else {
                    $this->propertyList[$property->name] = $property->getValue($this->class);
                }
            }

            foreach ($originalMethodList as $originalMethod) {
                $originalMethod->setAccessible(true);
                if (true === $originalMethod->isStatic()) {
                    continue;
                }
                $closure  = $originalMethod->getClosure($this->class);
                $function = $closure->bindTo($this); //メソッドのスコープを自身のスコープにする

                $this->originalMethodList[$originalMethod->name] = $function;
            }
        } else {
            $this->isStub = true;
        }
    }

    /**
     * インスタンスを生成する
     *
     * @param string $namespace
     */
    public static function m($namespace = null)
    {
        return new self($namespace);
    }

    /**
     * 解析中のクラスのメソッドにアクセスする
     *
     * @param string  $method
     * @param mixed[] $arguments
     * @return mixed
     */
    public function __call($method, array $arguments)
    {
        if (isset($this->methodList[$method])) {
            $list = $this->methodList[$method];
            foreach ($list as $l) {
                if ($arguments == $l['argList']) {
                    return $l['return'];
                }
            }
        }

        if (isset($this->originalMethodList[$method])) {
            $function = $this->originalMethodList[$method];
            $argList  = [];
            foreach ($arguments as $key => $argument) {
                $argList[] = &$arguments[$key];
            }
            return call_user_func_array($function, $argList);
        }

        $trace = debug_backtrace();
        var_dump($trace[1]['function'], $trace[1]['class'], '存在しないメソッドにアクセスした');
        exit;
    }

    /**
     * プロパティから値を取得する
     *
     * @param string $property
     * @return mixed
     */
    public function __get($property)
    {
        if (array_key_exists($property, $this->propertyList) || true === $this->isStub) {
            return $this->propertyList[$property];
        }

        $trace = debug_backtrace();
        var_dump($trace[1]['function'], $trace[1]['class'], '存在しないプロパティから取得しようとした');
        exit;
    }

    /**
     * プロパティにセットする
     *
     * @param string $property
     * @param mixed  $value
     */
    public function __set($property, $value)
    {
        if (array_key_exists($property, $this->propertyList) || true === $this->isStub) {
            $this->propertyList[$property] = $value;
            return;
        }

        $trace = debug_backtrace();
        var_dump($trace[1]['function'], $trace[1]['class'], '存在しないプロパティにセットしようとした');
        exit;
    }

    /**
     * 上書きするメソッドをセットする
     *
     * @param string $method
     * @return Parse
     */
    public function _setMethod($method)
    {
        if (array_key_exists($method, $this->originalMethodList) || true === $this->isStub) {
            $this->current['method'] = $method;
            return $this;
        }

        $trace = debug_backtrace();
        var_dump($trace[1]['function'], $trace[1]['class'], '存在しないメソッドにセットしようとした');
        exit;
    }

    /**
     * 引数をセットする
     *
     * @return Parse
     */
    public function _setArgs()
    {
        $argList = func_get_args();
        $this->current['argList'] = $argList;
        return $this;
    }

    /**
     * 戻り値をセットする
     *
     * @param string $method
     * @return Parse
     */
    public function _setReturn($return = null)
    {
        $this->current['return'] = $return;
        return $this;
    }

    /**
     * 設定値で上書きする
     */
    public function e()
    {
        if (!array_key_exists($this->current['method'], $this->methodList) && true === $this->isStub) {
            $this->methodList[$this->current['method']] = [];
        }
        $this->methodList[$this->current['method']][] = [
            'argList'  => $this->current['argList'],
            'return'   => $this->current['return']
        ];

        $this->current = [];

        return $this;
    }
}
