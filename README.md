# Eternal

MVCのPHPフレームワークです。

## ■ 開発環境  
```
PHP   7.1.29  
Node  v11.15.0 (npm v6.7.0)
mysql 5.7.24
```

## ■ 設置方法
```
make init
```
とするとcomposerとnode_moduleがインストールされます。（必須ではありません）

適当なディレクトリにcloneします。  
その後、NginxやApache等で設定されている公開用ディレクトリに、  
publicディレクトリのシンボリックリンクを張ります。  

```
cd /home/hogefuga  
git clone https://github.com/Kate-AC/Eternal.git  
ln -s /home/hogefuga/Eternal/public /var/www/html  
```

これでルートURLにアクセスして、Hello World!と表示されていれば完了です。  

## ■ config.phpの設定方法

Eternalに関する設定は全てここに記述します。  
ファイル内に説明が書いてあるのでここでの説明は省略します。 
  
ひとつ注意点として、` http://0.0.0.0/ディレクトリ名/ ` という構成にしたい場合があると思います。  
その場合は ` define('CURRENT_DIR', 'ディレクトリ名/'); `  として下さい。  

## ■ ファイル作成時のルール

Eternalではクラスファイルのオートロードと名前空間が用いられているため、  
名前空間の階層とクラスファイルを設置する際のディレクトリの階層は等しくなるようにして下さい。  

例として、下記のようなパスのHogeクラスがあった場合、Hogeクラスの名前空間は以下のようになります。  
```
src/App/Fuga/Hoge.php => App\Fuga;  
```
また、URLとコントローラークラスの階層も紐づいており、  
コントローラーファイルのパスが以下の場合はURLは以下のようになります。  
```
src/App/Controller/HogeFuga.php  
http://0.0.0.0/hogeFuga/メソッド名  
```
メソッド名を指定しない場合、indexActionメソッドが呼ばれます。  
Actionを接尾語に指定していない、あるいはpublicではない場合にメソッドは実行できません。  
  
## ■ 記述時のルール(必須ではありません)

Eternalではコンストラクタインジェクションを採用しています。  
ですのでクラス作成時に必要な他のクラスは、全てコンストラクタに含めて実装できます。  
(可能というだけで、実装方法を強制するものではありません)  
  
Hogeクラス内でUserクラスとImageクラスを使用する場合の例  
  
```php
namespace App\Fuga;

use App\User;
use App\Image;

class Hoge
{
	private $user;
	private $image;

	public function __construct(
		User $user,
		Image $image
	) {
		$this->user  = $user;
		$this->image = $image;
	}

	public function do()
	{
		$this->user->getName();
	}
}
```
なお、Memcached等を利用し、尚且つコンストラクタインジェクションを使用している場合に、  
新しくコンストラクタの引数を増やすまたは減らす場合、キャッシュクリアが必要な場合があります。  
その場合は ` service memcached restart ` コマンド等を実行して下さい。  

## ■ ルーティングの設定
route.phpに記述する。
左辺にURL、右辺に ` 名前空間@メソッド名 ` とする。
```
$route->set([
    '/' => 'App\Controller\IndexController@indexAction'
]);
```
左辺は ` /hoge/{user_id}/fuga/{number} ` という風に記述できる。  
例えば ` /hoge/100/fuga/99 ` でアクセスできる。
その場合コントローラで以下のようにして値を取得できる。
```
$this->get('user_id'); //100
$this->get('number'); //99
```

## ■ MySQLを使用する 

独自ORMを使用しています。
Modelは ` make model ` コマンドから自動生成できます。  
手動でも作成可能ですが、ルールが多いので手動作成は推奨できません。  
例として以下のように名前空間を指定してエンターを押します。  
```
App\Model\Hoge\User  
```
すると ` src/App/Model/Hoge/ ` ディレクトリ配下に  
```
User.php  
UserSkeleton.php  
```
2つのModelクラスが生成されます。  
UserSkeletonはテーブルの構造が記述されているため、手動で変更はしないで下さい。  
UserはUserSkeletonを継承しているため、オーバーライド可能です。  
  
モデルが生成出来たら、使用するクラス内でuseして、コンストラクタに引数として追加してください。  
  
### SELECT
SELECTで使用できるメソッドを全て使用した場合は下記のようになります。  
```php
$this->post->selectQuery()
	//EXPLAINを使用する場合
	->explain()
	//取得する箇所のみselectで指定し、記述しない場合は * と同じ
	//テーブルに別名を付ける場合は第2引数に記述する
	->select([
	'count' => $this->post //サブクエリを使用する場合はSelectQueryオブジェクトを渡してください
		->selectQuery()
		->select(['c' => 'COUNT(*)']), //AS句を使用する場合は左辺に記述
		'u.id'
	], 'p')
	//from句にサブクエリを使用する場合(指定しない場合は元のテーブル名になる)
	->from('post_tbl', $this->post
		->selectQuery()
		->select([
			'id',
			'user_id'
		])
	)
	->where('u.id', '=', 6)
	//IN句にサブクエリを指定する場合
	//[1, 2, 3]のように配列も指定できる
	->where('u.id', 'IN', $this->post
		->selectQuery()
		->select(['id'])
		->where()
	)
	//or句
	->otherwise($this->post
		//conditionで指定したwhere句は括弧でグループ化される
		->getCondition()
		->where('id', '>', -1)
		->otherwise('id', '<', 100)
	)
	//第1引数の左辺にJOIN時の別名を指定できる(Hoge::classのように指定してもよい)
	//第2引数で結合するカラムを複数指定できる
	->join(['u' => 'user_tbl'], [
		'p.id'      => 'u.id',
		'p.user_id' => 'u.type_id'
	])
	->orderBy('u.id DESC')
	->orderBy('u.name ASC')
	//インデックスを使用する(FORCE,USE,IGNOREが指定可能)
	->indexHint('post_tbl', 'IGNORE', ['PRIMARY'])
	->groupBy('u.id')
	->having('u.id', '=', 6)
	->limit(3)
	->forUpdate()
	->fetchAll();
```
1つのテーブルから全て取得したい場合は下記の記述だけです  
```
$this->user->selectQuery()->fetchAll();  
```
取得方法は以下から選択できます  
```
fetchAll             //全てオブジェクトの配列で取得  
fetch                //1件だけオブジェクトで取得  
fetchAllByKey        //指定したカラムの値を添え字にして全てオブジェクトの配列で取得  
fetchAllAsArray      //全て配列で取得  
fetchAsArray         //1件だけ配列で取得  
fetchAllAsArrayByKey //指定したカラムの値を添え字にして全て配列で取得  
count                //ヒットした件数を返す  
getQuery             //実際に流れるクエリを取得する  
```
### INSERT

//モデル名::make([]);でエンティティを生成できるので、値を入れてinsertメソッドに渡します。  
//カラムの型がdate,time,datetimeの場合はオブジェクトをそのまま渡せます  
```php
$user = User::make([
	'name'       => 'hoge',
	'type'       => 2,
	'created_at' => new \DateTime()
]);
$this->user->insertQuery()->insert($user);
```
  
### UPDATE

更新したい値だけ配列でsetします  
カラムの型がdate,time,datetimeの場合はオブジェクトをそのまま渡せます  
```php
$this->user->updateQuery()
	->set([
		'name'       => 'fuga',
		'updated_at' => new DateTime()
	])
	->where('id', '=', 3)
	->update();
```

### DELETE

```php
$this->user->deleteQuery()
	->where('id', '=', 2)
	->delete();
```
  
## ■ トランザクションについて

SELECT以外はトランザクション中でしか実行できません。  
トランザクションを開始する場合は、Transactionをuseして下さい。  
以下はトランザクションの例です。  
尚、コミットを忘れると反映されませんのでご注意ください。  
レプリケーションが設定されている場合は、自動でmasterの接続が選択されトランザクションを開始します。  

```php
$this->transaction->beginTransaction();
try {
	$this->user->deleteQuery()
		->where('id', '=', 2)
		->delete();
	$this->transaction->commit();
} catch (\Exception $e) {
	$this->transaction->rollBack();
}
```
  
## ■ コントローラ内で使用できるメソッドについて

### $_POST値を取得  
```
$this->request->post('hoge', 1); //第2引数はhogeが無かった場合の戻り値を指定できます  
```
### $_GET値を取得  
```
$this->request->get('hoge', 2);  
```
### $_SERVERを取得  
```
$this->request->server('REQUEST_URI');  
```
### $_FILESを取得  
```
$this->request->file('image', null);  
```

画像であれば ` png jpg gif bmp ` のいずれかの場合はImageクラスでラッピングされて取得されます  
それ以外はFileクラスでラッピングされて取得されます  
  
### ビューを指定  
```
$this->render('/admin/index');  
```
拡張子は省略できます  
  
### リダイレクト  
```
$this->redirect('/admin/hoge');  
```

### ビューを使用せずにJsonデータを返却する  
```
$this->useJsonResponse();  
```
### 各アクションの実行前に実行される  
```
public function before() {}
```
beforeメソッドはBaseControllerに定義されているので、そのメソッドのオーバーライドとなります  
  
### 各アクションの実行後に実行される  
```
public function after() {}
```
afterメソッドはBaseControllerに定義されているので、そのメソッドのオーバーライドとなります  
  
### ビューに値を渡す  
クラスを渡す場合は名前空間を渡し、変数を渡す場合は第1引数に変数名、第2引数に値をセットしてください  
```
$this->set('System\Util\Str');  
$this->set('hoge', 1);  
```
尚、全てのコントローラより先に処理が必要な場合は  
FirstProcessクラスのexecuteメソッドに処理を記述してください。  
  
## ■ ビューの記述方法について
viewファイルの拡張子は ` .arc ` 

### 値を表示する場合は下記の記述になり、セミコロンは不要
```
{{$hoge}}  
```
### 式の場合はコロン、セミコロンを付与
```
{{if (true === $hoge):}}  
{{endif;}}  
```
### クラスからstaticメソッドを呼ぶ場合
```
{{Kit::columnToGetter('fire_bird')}}  
```

### ビューのみで使えるhtmlspecialchars()関数の別名  
ユーザーからの入力値を表示する場合は必ずこの関数を通してください  
```
{{escape($hoge)}}  
``` 
### 現在のビューから他のビューを呼ぶ場合の記述  
```
{{appendView('hoge/index');}}  
```
### 親のview  
子のレイアウトで置き換えたい場所を下記のように記述
```
<div>
  {% hoge %}
</div>
```
### 子のview  
親のレイアウトを指定
```
{% parent('layout') %}
```
オーバーライドする部分を記述
```
{% hoge %}
  <span>Hello World!</span>
{% /hoge %}
```
### 最終的なアウトプット
```
<div>
  <span>Hello World!</span>
</div>
```

## ■ Testディレクトリについて

このフレームワーク自体のテストコードが置いてあります。  
makefileの存在する階層で make test と入力し、Enterを押すとテストが自動で実行されます。  
もしもあなたがフレームワーク自体を修正した後にテストを実行してエラーが発生した場合、  
他の個所への影響に気づけるかもしれません。  
  
## ■ デバッグ用関数について

var_dump();  
exit;  
のラッパー  
```
ve();  
```
var_dump();  
のラッパー  
```
v();  
```
memory_get_usage();  
memory_get_peak_usage();  
exit;  
のラッパー  
```
mem();  
```

## ■ ディレクトリ・ファイル構成
- [Eternal]
  - [System]
    - [Core]
      - __Autoloader.php__　includeされていないファイルを読み込む際に呼ばれる
      - __Cach.php__　キャッシュ
      - [Di]
        - __Container.php__　名前空間で指定したクラスを依存性解決済みで取得するクラス
        - __DependencyDto.php__　依存関係をまとめたクラス
        - __DependencySearcher.php__　依存しているクラスを再帰的に探すクラス
        - __DependencyInjector.php__　依存しているクラスを再帰的に生成するクラス
      - [Extend]
        - __ExtendProtocol.php__　includeやfile_get_contents等の関数を拡張するクラス
      - [Module]
        - __AbstractMolude.php__　抽象モジュールクラス
        - __OverrideViewModule.php__　viewの継承を可能にするクラス
        - __RenderModule.php__　Eternal専用のviewをレンダリングするクラス
      - [Route]
        - __Controller.php__　各コントローラのベースクラス
        - __Dispatcher.php__　アクセス先のクラスやコントローラを振り分けるクラス
        - __Request.php__　POSTやGETなどのユーザーからの入力が入るクラス
    - [Database]
      - __Model.php__　各モデルのベースクラス
      - __Collect.php__　どのモデルに当てはまらないクエリ結果を格納するクラス
      - __Connection.php__　DBとの接続を生成するクラス
      - __Transaction.php__　トランザクションを開始するクラス
      - [Query]
        - __BaseQuery.php__　各クエリのベースクラス
        - __DeleteQuery.php__　Delete用クラス
        - __DirectQuery.php__　クエリを直接実行するクラス
        - __QueryConditionTrait.php__　Where用クラス
        - __QueryFactoryTrait.php__　Select結果をクエリオブジェクトにまとめる
        - __UpdateQuery.php__　Update用クラス
        - __Condition.php__　Where句をグループ化する際に使う
        - __InsertQuery.php__　Insert用クラス
        - __QeryFetchTrait.php__　クエリの取得方法を定義
        - __SelectQuery.php__　Select用クラス
    - [Exception]
      - __AbstractException.php__　例外抽象クラス
      - __DatabaseException.php__　データベース使用時の例外クラス
      - __IncorrectTypeException.php__　Primitive型利用時に発生する例外クラス
      - __ControllerException.php__　コントローラで発生した例外クラス
      - __DiException.php__　依存解決時に発生した例外クラス
      - __SystemException.php__　フレームワークで発生した汎用例外クラス
    - [GlobalFunction]
      - __DebugFunction.php__　デバッグ時に使用可能なグローバル関数
    - [Log]
      - __AbstractLogger.php__　抽象例外クラス
      - __ActionLogger.php__　行動ログクラス
      - __SystemErrorLogger.php__　システムエラーログクラス
    - [Type]
      - [Other]
        - __Point.php__　MySqlでGeometry型を使うクラス
      - [Resource]
        - __File.php__　画像以外のファイル型
        - __Image.php__　画像ファイル型
    - [Util]
      - __FilePathSearcher.php__　ファイルのパスを再帰的に取得するクラス
      - __Kit.php__　フレームワークの汎用処理を行うクラス 
      - __ModelCreator.php__　モデルのEntityとSkeletonを生成するクラス
      - __Session.php__　汎用Sessionクラス
      - __Str.php__　文字列操作用クラス
  - [assets]
    - [js]
      - __main.js__　webpack用のサンプルjs
    - [scss]
      - __main.scss__　webpack用のサンプルscss
  - [log]
  - [script]
    - __ModelMakeShell.php__　モデル生成用スクリプト
  - [public]
    - __index.php__　ルーティングの起点となるファイル
    - [css]
      - __main.css__　サンプルcss
    - [js]
      - __main.js__　サンプルjs
    - [view]
      - index.arc　HelloWorldが記述されたviewファイル
      - layout.arc　親のviewファイル
      - not_found.arc　NotFound用のviewファイル
  - [src]
    - [App]
      - __FirstProcess.php__　全てのコントローラより先に実行されるクラス
      - [Controller]
        - __IndexController.php__　デフォルトの初期コントローラ  
      - [Model]
  - [Test]
    - [Phantom]
      - __Phantom.php__　モック用クラス
      - __Reborn.php__　オリジナルクラスを書き換えるクラス
      - [tmp]
    - [System]
      - フレームワークのテストが書かれたクラス群
    - __TestHelper.php__　実行結果を判定するクラス  
    - __TestRunner.php__　テストを一括で実行するクラス  
  - __Makefile__  
  - __config.php__　フレームワークの設定を記述するファイル  
  - __use.php__　フレームワークが動作するために最低限必要なrequire文が書かれたファイル  
  - __package.json__  
  - __composer.json__  
  - __webpack.config.js__  
