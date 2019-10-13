<?php

/**
 * ビューをオーバーライドするモジュール
 */

namespace System\Core\Extend\Module;

class OverrideViewModule extends AbstractModule
{
    /**
     * @var OverrideViewModule
     */
    protected static $instance;

    /**
     * ビューをオーバーライドする
     *
     * @param string $path
     * @param string $data
     * @return string
     */
    public function run($path, $data)
    {
        if (false === strpos($path, VIEW_DIR)) {
            return $data;
        }

        $data = preg_replace(':{% *([^ ]+) *%}:', '{%$1%}', $data);
        preg_match(':{%parent\(\'(.+)\'\)%}:', $data, $match);
        if (!isset($match[1])) {
            return $data;
        }
        $parent = $match[1] . '.php';

        preg_match_all(':{%([^/() ]+)%}:', $data, $match);
        $overrideList = $match[0];
        $tagList      = $match[1];

        $list = [];
        foreach ($tagList as $key => $tag) {
            preg_match(':{%' . $tag . '%}([\s\S]*){%/' . $tag . '%}:', $data, $match);
            if (!isset($match[1])) {
                continue;
            }
            $list[$overrideList[$key]] = $match[1];
        }

        $parent = file_get_contents(VIEW_DIR . $parent);
        $parent = preg_replace(':{% *([^ ]+) *%}:', '{%$1%}', $parent);

        foreach ($list as $key => $l) {
            $parent = preg_replace(':' . $key . ':', $l, $parent);
        }

        return $parent;
    }
}
