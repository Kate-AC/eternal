<?php

/**
 * Eternalの設定ファイル
 */

/* ディレクトリの設定 =================================================================== */


define('CURRENT_DIR',    __DIR__    . '/');
define('SYSTEM_DIR',     CURRENT_DIR . 'System/');
define('TEST_DIR',       CURRENT_DIR . 'Test/');
define('SRC_DIR',        CURRENT_DIR . 'src/');
define('CONTROLLER_DIR', CURRENT_DIR . 'src/App/Controller/');
define('MODEL_DIR',      CURRENT_DIR . 'src/App/Model/');
define('PUBLIC_DIR',     CURRENT_DIR . 'public/');
define('VIEW_DIR',       CURRENT_DIR . 'public/view/');
define('CSS_DIR',        CURRENT_DIR . 'public/css/');
define('JS_DIR',         CURRENT_DIR . 'public/js/');


/* ビューの拡張子の設定 ================================================================= */

const VIEW_EXTENSION = 'arc';


/* コントローラより先に実行されるクラスの使用設定 ======================================= */

const USE_FIRST_PROCESS   = true;
const FIRST_PROCESS_CLASS = 'App\FirstProcess';


/* ログファイルの設定 =================================================================== */

define('ACTION_LOG_FILE',       CURRENT_DIR . 'log/action');
define('SYSTEM_ERROR_LOG_FILE', CURRENT_DIR . 'log/system_error');

const USE_SYSTEM_ERROR_LOG_FILE = false;


/* 使用するデータベースの設定 =========================================================== */

const DB_MYSQL    = 'mysql';
const DB_POSTGRES = 'pgsql';

define('USE_DB', DB_MYSQL);


/* データベースの設定 =================================================================== */

function getConnectionList()
{
    //slaveを追加する場合は「slave3」「slave4」のように追加して下さい
    if ('production' === getenv('ENV_MODE')) {
        return [
            'master' => [
                'use'      => true,
                'host'     => 'localhost',
                'port'     => 3306,
                'database' => 'vega',
                'user'     => 'root',
                'password' => 'root'
            ],
            'slave1' => [
                'use'      => false,
                'host'     => 'localhost',
                'port'     => 3306,
                'database' => 'vega',
                'user'     => 'slave',
                'password' => 'slave'
            ],
            'slave2' => [
                'use'      => false,
                'host'     => 'localhost',
                'port'     => 3306,
                'database' => 'vega',
                'user'     => 'slave',
                'password' => 'slave'
            ]
        ];
    } else {
        return [
            'master' => [
                'use'      => true,
                'host'     => 'localhost',
                'port'     => 3306,
                'database' => 'vega',
                'user'     => 'root',
                'password' => 'root'
            ]
        ];
    }
}


/* Cacheの設定 ========================================================================== */

// 0: キャッシュを使用しない
// 1: Memcache
// 2: Memcached
const SELECT_CACHE_TYPE = 0;

// 上記で1か2を選択した場合
const MEMCACHE_HOST = '127.0.0.1';
const MEMCACHE_PORT = 11211;


/* デバッグモードの設定 ================================================================= */

//trueの場合は例外が発生した際に処理が停止します
//falseの場合はNotFoundページが表示されます
const USE_DEBUG_MODE = true;


/* オートロード対象のディレクトリを指定 ================================================= */

function getAutoLoadDirs()
{
    return [
        SYSTEM_DIR,
        SRC_DIR,
        TEST_DIR
    ];
}


/* オートロード対象から外すディレクトリを指定 =========================================== */

function getUnUseDirs()
{
    return [];
}

