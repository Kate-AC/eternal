<?php

/**
 * Eternalの設定ファイル
 */

/* ディレクトリの設定 =================================================================== */


define('CURRENT_DIR',    '');
define('SOURCE_DIR',     __DIR__    . '/');
define('FRAMEWORK_DIR',  SOURCE_DIR . 'System/');
define('TEST_DIR',       SOURCE_DIR . 'Test/');
define('PUBLIC_DIR',     SOURCE_DIR . 'public/');
define('APP_DIR',        SOURCE_DIR . 'public/App/');
define('CONTROLLER_DIR', SOURCE_DIR . 'public/App/Controller/');
define('MODEL_DIR',      SOURCE_DIR . 'public/App/Model/');
define('TEMPLATE_DIR',   SOURCE_DIR . 'public/template/');


/* テンプレートの拡張子の設定 =========================================================== */

const TEMPLATE_EXTENSION = 'php';


/* コントローラより先に実行されるクラスの使用設定 ======================================= */

const USE_FIRST_PROCESS   = true;
const FIRST_PROCESS_CLASS = 'App\FirstProcess';


/* ログファイルの設定 =================================================================== */

define('ACTION_LOG_FILE',       SOURCE_DIR . 'log/action');
define('SYSTEM_ERROR_LOG_FILE', SOURCE_DIR . 'log/system_error');

const USE_SYSTEM_ERROR_LOG_FILE = false;


/* 使用するデータベースの設定 =========================================================== */

const DB_MYSQL    = 1;
const DB_POSTGRES = 2;

define('USE_DB', DB_MYSQL);


/* データベースの設定 =================================================================== */

function getConnectionList()
{
	//slaveを追加する場合は「slave3」「slave4」のように追加して下さい
	return [
		'master' => [
			'use'      => true,
			'host'     => 'localhost',
			'database' => 'postgres',
			'user'     => 'postgres',
			'password' => 'manager'
		],
		'slave1' => [
			'use'      => false,
			'host'     => 'localhost',
			'database' => 'vega',
			'user'     => 'slave',
			'password' => 'slave'
		],
		'slave2' => [
			'use'      => false,
			'host'     => 'localhost',
			'database' => 'vega',
			'user'     => 'slave',
			'password' => 'slave'
		]
	];
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
		FRAMEWORK_DIR,
		TEST_DIR,
		PUBLIC_DIR
	];
}


/* オートロード対象から外すディレクトリを指定 =========================================== */

function getUnUseDirs()
{
	return [
		'.',
		'..',
		'.git',
		'setup'
	];
}


/* テーブルの接頭辞・接尾辞を指定 ======================================================= */

//テーブルで使用する接頭辞を記述して下さい。
//テーブル名をモデル名に変換する際に必要です。

function getTablePrefix()
{
	return [
		'tbl',
		'mst'
	];
}
