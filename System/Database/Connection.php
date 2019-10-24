<?php

/**
 * 接続情報を保持するクラス
 */

namespace System\Database;

use System\Exception\DatabaseException;

class Connection
{
    /**
     * @var \PDO[]
     */
    private static $pdo = [];

    /**
     * @var string
     */
    private $useConnection;

    /**
     * コネクションを生成する
     */
    public function start()
    {
        $connectionList = $this->getConnectionList();
        $slaveList      = [];

        foreach ($connectionList as $key => $connection) {
            if (false === $connection['use']) {
                if (array_key_exists($key, self::$pdo)) {
                    unset(self::$pdo[$key]);
                }
                continue;
            }

            if (!isset(self::$pdo[$key])) {
                $db = new \PDO(
                    sprintf('%s:host=%s; port=%s; dbname=%s; charset=utf8;',
                        USE_DB,
                        $connection['host'],
                        $connection['port'],
                        $connection['database']
                    ),
                    $connection['user'],
                    $connection['password'],
                    [
                        \PDO::ATTR_PERSISTENT => true
                    ]
                );

                self::$pdo[$key] = $db;
                self::$pdo[$key]->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            }

            if ('master' !== $key) {
                $slaveList[$key] = self::$pdo[$key];
            }
        }

        if (empty(self::$pdo)) {
            throw new DatabaseException(implode("\r\n", [
                '接続が存在しません',
                'DBが存在しないか、データベースの設定でuseがtrueになっていない可能性があります'
            ]));
        }

        //slaveが存在しない場合はmasterを参照する
        if (empty($slaveList)) {
            $this->useConnection = 'master';
        } else {
            $this->useConnection = array_rand($slaveList);
        }
    }

    /**
     * 設定ファイルからコネクションの一覧を取得する
     */
    private function getConnectionList()
    {
        return getConnectionList();
    }

    /**
     * 指定したキーのPDOを返す
     *
     * @param string $key
     * @return \PDO
     */
    public function get($key)
    {
        if (!isset(self::$pdo[$key])) {
            $this->start();
        }

        if (!isset(self::$pdo[$key])) {
            throw new DatabaseException(sprintf('存在しない接続を指定しました(%s)', $key));
        }

        $this->useConnection = $key;
        return self::$pdo[$key];
    }

    /**
     * 自動でPDOを返す
     *
     * @return \PDO
     */
    public function getAuto()
    {
        if (is_null($this->useConnection)) {
            $this->start();
        }

        if (!isset(self::$pdo[$this->useConnection])) {
            $this->start();
        }

        return self::$pdo[$this->useConnection];
    }

    /**
     * 使用する接続を返す
     *
     * @return string
     */
    public function getUseConnection()
    {
        return $this->useConnection;
    }
}

