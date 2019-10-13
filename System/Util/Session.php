<?php

/**
 * セッション管理クラス
 */

namespace System\Util;

class Session
{
    const SESSION_LIFETIME = 259200; // 3日

    /**
     * セッションに値をセットする
     *
     * @param string $key
     * @param mixed    $value
     */
    public function set($key, $value = null)
    {
        session_start();
        $_SESSION[$key] = $value;
        session_write_close();
    }

    /**
     * セッションから値を取得する
     *
     * @param string $key
     */
    public function get($key)
    {
        session_start();
        $value = isset($_SESSION[$key]) ? $_SESSION[$key] : null;
        session_write_close();

        return $value;
    }

    /**
     * セッションのキーをクリアする
     *
     * @param string $key
     */
    public function clear($key)
    {
        session_start();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
        session_write_close();
    }

    /**
     * 全てのセッションをクリアする
     */
    public function clearAll()
    {
        session_start();
        $_SESSION = [];
        setcookie(session_name(), '', time() - self::SESSION_LIFETIME);
        session_destroy();
    }
}

