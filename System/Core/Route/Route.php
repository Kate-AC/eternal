<?php

/**
 * ルートクラス
 */

namespace System\Core\Route;

use System\Exception\ControllerException;

class Route
{
    private $routeList = [];

    private $valueList = [];

    private $controller;

    private $method;

    /**
     * 名前空間とURLのセットを返す
     *
     * @param string[] $list
     */
    public function set(array $list)
    {
        $this->routeList = $list;
    }

    /**
     * URLからアクセス対象のクラスを解決する
     *
     * @param string $requestUrl
     */
    public function resolve($requestUrl)
    {
        if (false !== strstr($requestUrl, '?', true)) {
            $requestUrl = strstr($requestUrl, '?', true);
        }

        $requestList = explode('/', $requestUrl);

        foreach ($this->routeList as $url => $controller) {
            $urlList = explode('/', $url);
            $sprintf = preg_replace(':{[^}]+}:', '%s', $url);

            foreach ($urlList as $i => $u) {
                preg_match(':{[^}]+}:', $u, $match);
                if (isset($match[0]) && isset($requestList[$i])) {
                    $name = str_replace(['{', '}'], '', $match[0]);
                    $this->valueList[$name] = $requestList[$i];
                }
            }

            if (count($this->valueList) !== substr_count($sprintf, '%s')) {
                continue;
            }

            if ($requestUrl === vsprintf($sprintf, $this->valueList)) {
               $c = explode('@', $controller);
               $this->method     = $c[1];
               $this->controller = $c[0];
               return;
            }

            $this->clear();
        }

        throw new ControllerException('存在しないアドレスにアクセスした。');
    }

    /**
     * Routeの設定を消去する
     */
    private function clear()
    {
        $this->method     = null;
        $this->controller = null;
        $this->valueList  = [];
    }

    /**
     * メソッド名を返す
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * コントローラー名を返す
     *
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * URLの動的に指定された部分を返す
     *
     * @return string[]
     */
    public function getValueList()
    {
        return $this->valueList;
    }
}
