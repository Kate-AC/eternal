<?php

/**
 * モック設定値変更用クラス
 */

namespace Phantom;

class Reborn
{
    /**
     * @var \ReflectionClass
     */
    private $reflection;

    /**
     * @var string
     */
    private $rebornClass;

    /**
     * @var boolean
     */
    private $isInnerMethodCall = false;

    /**
     * コンストラクタ
     *
     * @param \ReflectionClass $reflection
     * @param string           $method
     * @param boolean          $isCreated
     */
    private function __construct($reflection, $method, $isCreated)
    {
        $this->reflection = $reflection;

        $content = file_get_contents($this->reflection->getFileName());
        $content = str_replace("\r", '', $content);

        $parent = null;
        if (!$this->reflection->isAbstract()) {
            $parent = $this->reflection->getParentClass();
        }

        if (false !== $parent && !is_null($parent)) {
            if (false === strpos($content, $parent->name)) {
                $content = preg_replace(
                    sprintf(':(class[ ]+%s[^{]*{):', $this->getClassName()),
                    sprintf('use %s;%s$1', $parent->name, PHP_EOL),
                    $content
                );
            }
        }

        $traitNameList = $this->reflection->getTraitNames();
        foreach ($traitNameList as $traitName) {
            if (false !== strpos($content, $traitName)) {
                continue;
            }
            $content = preg_replace(
                sprintf(':(class[ ]+%s[^{]*{):', $this->getClassName()),
                sprintf('use %s;%s$1', $traitName, PHP_EOL),
                $content
             );
        }

        if (false === $isCreated) {
            // Add __call method.
            $content = preg_replace(
                sprintf(':^([\S\s]+class[ ]+%s[^{]*){([\S\s]+):', $this->getClassName()),
                sprintf('$1%s$2', $this->getCallMethod()),
                $content
            );
        }

        // Avoid original method.
        $content = preg_replace(
            sprintf(':(function[ ]+)%s( ?\():', $method),
            sprintf('$1%s_ $2', $method),
            $content
        );

        if (false !== strpos($content, sprintf('->%s(', $method))) {
            // Replace original method.
            $content = preg_replace(
                sprintf(':->%s\(:', $method),
                sprintf('->_%s(', $method),
                $content
            );
            $this->isInnerMethodCall = true;
        }

        if (false !== strpos($content, sprintf('->_%s(', $method))) {
            $this->isInnerMethodCall = true;
        }

        $generateName = $this->generateName();

        // Add dummy namespace.
        $content = preg_replace(
            ':(namespace[ ]+)[^=;]+;:',
            sprintf('$1%s;', $this->getNamespace($generateName)),
            $content
        );

        $filePath = sprintf('%s/tmp/%s.php', __DIR__, $this->getClassName());

        file_put_contents($filePath, $content);
        include($filePath);

        $reflection = new \ReflectionClass($this->getFullName($generateName));
        $this->rebornClass = $reflection->newInstanceWithoutConstructor();
    }

    /**
     * モックしたクラスを設定値で再生成する
     *
     * @param \ReflectionClass $reflection
     * @param string           $method
     * @param boolean          $isCreated
     * @return Reborn
     */
    public static function reborn($reflection, $method, $isCreated)
    {
        return new self($reflection, $method, $isCreated);
    }

    /**
     * __callメソッドを付与する文字列を取得
     *
     * @return string
     */
    private function getCallMethod()
    {
        return <<<EOD
        {
            public \$methodList = [];

            public function __call(\$method, array \$arguments = []) {
                if (isset(\$this->methodList[\$method])) {
                    foreach (\$this->methodList[\$method] as \$l) {
                        if (\$arguments == \$l['argList']) {
                            return \$l['return'];
                        }
                    }
                }
            }

EOD;
    }

    /**
     * ダミー用の文字列を取得する
     *
     * @return string
     */
    private function generateName()
    {
        return sprintf('%s_%s',
            str_replace('\\', '_', $this->reflection->name),
            str_replace(['.', ' '], '', microtime())
        );
    }

    /**
     * クラス名を取得する
     *
     * @return string
     */
    private function getClassName()
    {
        $name = explode('\\', $this->reflection->name);
        return end($name);
    }

    /**
     * クラスの名前空間を取得する
     *
     * @param  string $generateName
     * @return string
     */
    private function getNamespace($generateName)
    {
        return $this->reflection->getNamespaceName() . '\\' . $generateName;
    }

    /**
     * クラスのフルネームを取得する
     *
     * @param  string $generateName
     * @return string
     */
    private function getFullName($generateName)
    {
        return sprintf('%s\\%s\\%s',
            $this->reflection->getNamespaceName(),
            $generateName,
            $this->getClassName()
        );
    }

    /**
     * 再生成したクラスを取得する
     *
     * @return mixed
     */
    public function getClass()
    {
        return $this->rebornClass;
    }

    /**
     * メソッド内のメソッド呼び出し箇所の置換か
     *
     * @return boolean
     */
    public function isInnerMethodCall()
    {
        return $this->isInnerMethodCall;
    }
}

