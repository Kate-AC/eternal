<?php

/**
 * モック用クラス
 */

namespace Phantom;

class Phantom
{
    /**
     * @var mixed[]
     */
    private $methodList = [];

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
     * @var \ReflectionClass
     */
    private $origin;

    /**
     * @var boolean
     */
    private $isStub = true;

    /**
     * @var boolean
     */
    private $isCreated = false;

    /**
     * コンストラクタ
     *
     * @param string $namespace
     */
    private function __construct($namespace = null)
    {
        if (!is_null($namespace)) {
            $this->isStub = false;
            $this->class  = new \ReflectionClass($namespace);
            $this->origin = (new \ReflectionClass($namespace))
                ->newInstanceWithoutConstructor();
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
    public function __call($method, array $arguments = [])
    {
        // 上書きしたメソッド
        if (array_key_exists($method, $this->methodList)) {
            foreach ($this->methodList[$method] as $l) {
                if ($arguments == $l['argList']) {
                    return $l['return'];
                }
            }
        }

        // 上書きしたメソッド
        if (property_exists($this->origin, 'methodList') && array_key_exists($method, $this->origin->methodList)) {
            foreach ($this->origin->methodList[$method] as $l) {
                if ($arguments == $l['argList']) {
                    return $l['return'];
                }
            }
        }

        if ($this->class->hasMethod($method)) {
            $method = $this->class->getMethod($method);
            $method->setAccessible(true);
            // Hide call by reference warning.
            return @$method->invokeArgs($this->origin, $arguments);
        }

        echo get_called_class() . '存在しないメソッドにアクセスした' . PHP_EOL;
    }

    /**
     * プロパティから値を取得する
     *
     * @param string $property
     * @return mixed
     */
    public function __get($property)
    {
        if (!$this->isStub && $this->class->hasProperty($property)) {
            $property = $this->class->getProperty($property);
            $property->setAccessible(true);
            return $property->getValue($this->origin);
        }

        if (array_key_exists($property, $this->propertyList)) {
            return $this->propertyList[$property];
        }

        echo get_called_class() . '存在しないプロパティから取得しようとした' . PHP_EOL;
    }

    /**
     * プロパティにセットする
     *
     * @param string $property
     * @param mixed  $value
     */
    public function __set($property, $value)
    {
        if (!$this->isStub && $this->class->hasProperty($property)) {
            $this->propertyList[$property] = $value;
            $property = $this->class->getProperty($property);
            $property->setAccessible(true);
            $property->setValue($this->origin, $value);
            return;
        }

        if ($this->class->hasProperty($property)) {
            $this->propertyList[$property] = $value;
        }

        echo get_called_class() . '存在しないプロパティにセットしようとした' . PHP_EOL;
    }

    /**
     * 上書きするメソッドをセットする
     *
     * @param string $method
     * @return Phantom
     */
    public function setMethod($method)
    {
        if (!$this->isStub &&
            ($this->class->hasMethod($method) || $this->class->hasMethod($method . '_'))) {
            $this->current['method'] = $method;
            return $this;
        }

        if ($this->isStub) {
            $this->current['method'] = $method;
            return $this;
        }

        echo get_called_class() . '存在しないメソッドを上書きしようとした' . PHP_EOL;
    }

    /**
     * 引数をセットする
     *
     * @return Phantom
     */
    public function setArgs()
    {
        $argList = func_get_args();
        $this->current['argList'] = $argList;
        return $this;
    }

    /**
     * 戻り値をセットする
     *
     * @param string $method
     * @return Phantom
     */
    public function setReturn($return = null)
    {
        $this->current['return'] = $return;
        return $this;
    }

    /**
     * 設定値で上書きする
     */
    public function exec()
    {
        $method = $this->current['method'];
        if (!array_key_exists($method, $this->methodList)) {
            $this->methodList[$method] = [];
        }

        $this->methodList[$method][] = [
            'argList'  => array_key_exists('argList', $this->current) ? $this->current['argList'] : null,
            'return'   => array_key_exists('return', $this->current) ? $this->current['return'] : null,
        ];

        if ($this->isStub) {
            return $this;
        }

        $reborn = Reborn::reborn(
            $this->class,
            $method,
            $this->isCreated
        );

        $this->origin = $reborn->getClass();

        if ($reborn->isInnerMethodCall()) {
           $this->methodList['_' . $method] = $this->methodList[$method];
        }

        $this->origin->methodList = $this->methodList;

        foreach ($this->propertyList as $property => $value) {
            $originReflection = new \ReflectionClass($this->origin);
            $property = $originReflection->getProperty($property);
            $property->setAccessible(true);
            $property->setValue($this->origin, $value);
        }

        $this->class     = new \ReflectionClass($this->origin);
        $this->current   = [];
        $this->isCreated = true;

        return $this;
    }

    /**
     * オリジナルクラスを返す
     *
     * @return mixed
     */
    public function getOrigin()
    {
        return $this->origin;
    }
}

