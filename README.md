# Eternal

EternalはPHP5.6以上、MySQL5.7以上での使用を想定したフレームワークです。

## ■ ディレクトリ・ファイル構成
- [Eternal] フレームワーク本体のディレクトリ
  - [Core] フレームワークの核となるファイルがあるディレクトリ  
    __Autoloader.php__ includeされていないファイルを読み込む際に呼ばれるクラス  
    __Cach.php__ キャッシュクラス  
    - [Route] ユーザーがアクセスしてページを表示するまでに必要なクラス  
      __BaseController.php__ 各コントローラのベースクラス  
      __Dispatcher.php__ アクセス先のクラスやコントローラを振り分けるクラス  
      __Request.php__ POSTやGETなどのユーザーからの入力が入るクラス  
    - [Extend] PHPのデフォルトの動作を拡張するファイルがあるディレクトリ  
      __ExtendProtocol.php__ includeやfile_get_contents等の関数を拡張するクラス
      - [Module] 拡張モジュールがあるディレクトリ  
        __AbstractMolude.php__ 抽象モジュールクラス  
        __AutoUseModule.php__ 自動でPrimitive型をコード内にuseさせるモジュールクラス(副作用が多いため未使用)  
        __RenderModule.php__ SmartyのようにHtml内で{{$hoge}}等の書き方を可能にするモジュールクラス
    - [Di] コンストラクタインジェクションに必要なファイルがあるディレクトリ  
      __Container.php__ 名前空間で指定したクラスを依存性解決済みで取得するクラス  
      __DependencyDto.php__ 依存関係をまとめたValueObjectクラス  
      __DependencySearcher.php__ 依存しているクラスを再帰的に探すクラス  
      __DependencyInjector.php__ 依存しているクラスを再帰的に生成するクラス  
  - [Database] データベースに関するファイルがあるディレクトリ
    - [MySql] MySQLを利用する場合に必要なファイルがあるディレクトリ  
      __BaseModel.php__ 各モデルのベースクラス  
      __Collect.php__ どのモデルに当てはまらないクエリ結果を格納するクラス  
      __Connection.php__ DBとの接続を生成するクラス  
      __TransactionFactory.php__ トランザクションを開始するクラス
        - [Query] クエリに関するファイルがあるディレクトリ  
          __BaseQuery.php__ 各クエリのベースクラス  
          __DeleteQuery.php__ Delete用クラス  
          __QueryConditionTrait.php__ Where用クラス  
          __QueryFactoryTrait.php__ Select結果をクエリオブジェクトにまとめる  
          __UpdateQuery.php__ Update用クラス  
          __Condition.php__ Where句をグループ化する際に使う  
          __InsertQuery.php__ Insert用クラス  
          __QeryFetchTrait.php__ クエリの取得方法を定義  
          __SelectQuery.php__ Select用クラス  
  - [Exception] 例外に関するファイルがあるディレクトリ  
    __AbstractException.php__ 例外抽象クラス  
    __DatabaseException.php__ データベース使用時の例外クラス  
    __IncorrectTypeException.php__ Primitive型利用時に発生する例外クラス  
    __ControllerException.php__ コントローラで発生した例外クラス  
    __DiException.php__ 依存解決時に発生した例外クラス  
    __SystemException.php__ フレームワークで発生した汎用例外クラス  
  - [Log] ログに関するファイルがあるディレクトリ  
    __AbstractLogger.php__ 抽象例外クラス  
    __ActionLogger.php__ 行動ログクラス  
    __SystemErrorLogger.php__ システムエラーログクラス  
  - [Type] クラスの型に関するファイルがあるディレクトリ
    - [Other] その他の型に関するファイルがあるディレクトリ  
      __Point.php__ MySqlでGeometry型を使うクラス  
    - [Primitive] プリミティブ型に関するファイルがあるディレクトリ  
      __AbstractPrimitive.php__ Primitive抽象クラス  
      __CalculateTrait.php__ 四則演算を提供するトレイト  
      __Boolean.php__ Boolean型  
      __Float.php__ Float型  
      __Int.php__ Int型  
      __String.php__ String型  
    - [Resource] リソース関連の型に関するファイルがあるディレクトリ  
      __File.php__ 画像以外のファイル型  
      __Image.php__ 画像ファイル型  
  - [NotClass] クラスではないメソッドのみが記述されたファイルがあるディレクトリ  
    __CallPrimitiveFunction.php__ Primitive型を呼び出せるグローバル関数  
    __DebugFunction.php__         デバッグ時に使用可能なグローバル関数  
    - [Script] CUIから呼ばれるスクリプトがあるディレクトリ  
      __ModelCreatorSctipt.php__ モデルを自動生成するスクリプト  
      __help__ makefileのヘルプ  
      __model_creator.c__ モデルを自動生成するスクリプトの呼び出し用ファイル  
  - [Util] 汎用的なクラスがあるディレクトリ  
    __FilePathSearcher.php__ ファイルのパスを再帰的に取得するクラス  
    __ModelCreator.php__ モデルのEntityとSkeletonを生成するクラス  
    __SessionManager.php__ 汎用Sessionクラス  
    __StringOperator.php__ 文字列操作用クラス  

  __makefile__ makeコマンドが記述されたファイル  
  __config.php__ フレームワークの設定を記述するファイル  
  __use.php__ フレームワークが動作するために最低限必要なrequire文が書かれている  
  - [log] ログが書き出されるディレクトリ  
  - [public] 公開用ディレクトリ  
    __index.php__ フレームワークの起点となるファイル  
    - [App] アプリケーションディレクトリ(名前空間と連動している)  
      __FirstProcess.php__ 全てのコントローラより先に実行されるクラス  
      - [Controller] コントローラを入れるディレクトリ  
        __IndexController.php__ デフォルトの初期コントローラ  
      - [Model] モデルを入れるディレクトリ  
    - [template] ビューファイルを入れるディレクトリ  
- [Test] フレームワークのテストに関するファイルがあるディレクトリ  
  __Mock.php__ クラスのモックを作成するクラス  
  __TestHelper.php__ 実行結果を判定するクラス  
  __TestRunner.php__ テストを一括で実行するクラス  

## ■ 設置方法
  
適当なディレクトリにcloneします。  
その後、apache等で設定されている公開用ディレクトリに、  
publicディレクトリのシンボリックリンクを張ります。  
  
> cd /home/hogefuga  
> git clone https://github.com/Kate-AC/Eternal.git  
> ln -s /home/hogefuga/Eternal/public /var/www/html  
  
これでルートURLにアクセスして、Hello World!と表示されていれば完了です。  
  
## ■ config.phpの設定方法

Eternalに関する設定は全てここに記述します。  
ファイル内に説明が書いてあるのでここでの説明は省略します。 
  
ひとつ注意点として、http://0.0.0.0/ディレクトリ名/ という構成にしたい場合があると思います。  
その場合は define('CURRENT_DIR',    'ディレクトリ名/'); として下さい。  
  
## ■ ファイル作成時のルール

Eternalではクラスファイルのオートロードと名前空間が用いられているため、  
名前空間の階層とクラスファイルを設置する際のディレクトリの階層は等しくなるようにして下さい。  

例として、下記のようなパスのHogeクラスがあった場合、Hogeクラスの名前空間は以下のようになります。  
/var/www/html/src/App/Fuga/Hoge.php => App\Fuga;  
  
また、URLとコントローラークラスの階層も紐づいており、  
コントローラーファイルのパスが以下の場合はURLは以下のようになります。  
  
/var/www/html/src/App/Controller/HogeFuga.php  
http://0.0.0.0/hogeFuga/メソッド名  
メソッド名を指定しない場合、indexActionメソッドが呼ばれます。  
Actionを接尾語に指定していない、あるいはpublicではない場合にメソッドは実行できません。  
  
## ■ 記述時のルール(必須ではありません)

Eternalではコンストラクタインジェクションを採用しています。  
ですのでクラス作成時に必要な他のクラスは、全てコンストラクタに含めて実装できます。  
(可能というだけで、実装方法を強制するものではありません)  
  
//Hogeクラス内でUserクラスとImageクラスを使用する場合の例  
  
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
その場合はservice memcached restartコマンド等を実行して下さい。  
  
## ■ MySQLを使用する

オブジェクトリレーショナルマッピング(ORM)方式を採用しているため、  
Modelクラスのプロパティは実際のテーブルのカラムと等しくなるように設計されています。  
  
Modelはmakeコマンドから自動生成できます。  
手動でも作成可能ですが、ルールが多いので手動作成は推奨できません。  
makefileが存在する階層で make Model と入力し、例として以下のように名前空間を指定してエンターを押します。  
  
App\Model\Hoge\User  
  
するとsrc/App/Model/Hoge/ディレクトリ以下に  
User.php  
UserSkeleton.php  
2つのModelクラスが生成されます。  
UserSkeletonはテーブルの構造が記述されているため、手動で変更はしないで下さい。  
UserはUserSkeletonを継承しているため、オーバーライド可能です。  
  
モデルが生成出来たら、使用するクラス内でuseして、コンストラクタに引数として追加してください。  
  
- SELECT

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
	//第1引数の左辺にJOIN時の別名を指定できる
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
	->limit(3)
	->forUpdate()
	->fetchAll();
```
//1つのテーブルから全て取得したい場合は下記の記述だけです  
$this->user->selectQuery()->fetchAll();  
  
//取得方法は以下から選択できます  
fetchAll             //全てオブジェクトの配列で取得  
fetch                //1件だけオブジェクトで取得  
fetchAllByKey        //指定したカラムの値を添え字にして全てオブジェクトの配列で取得  
fetchAllAsArray      //全て配列で取得  
fetchAsArray         //1件だけ配列で取得  
fetchAllAsArrayByKey //指定したカラムの値を添え字にして全て配列で取得  
count                //ヒットした件数を返す  
getQuery             //実際に流れるクエリを取得する  
  
- INSERT

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
  
- UPDATE

//更新したい値だけ配列でsetします  
//カラムの型がdate,time,datetimeの場合はオブジェクトをそのまま渡せます  
```php
$this->user->updateQuery()
	->set([
		'name'       => 'fuga',
		'updated_at' => new DateTime()
	])
	->where('id', '=', 3)
	->update();
```

- DELETE

```php
$this->user->deleteQuery()
	->where('id', '=', 2)
	->delete();
```
  
## ■ トランザクションについて

SELECT以外はトランザクション中でしか実行できません。  
トランザクションを開始する場合は、TransactionFactoryをuseして下さい。  
以下はトランザクションの例です。  
尚、コミットを忘れると反映されませんのでご注意ください。  
レプリケーションが設定されている場合は、自動でmasterの接続が選択されトランザクションを開始します。  

```php
$this->transactionFactory->beginTransaction();
try {
	$this->user->deleteQuery()
		->where('id', '=', 2)
		->delete();
	$this->transactionFactory->commit();
} catch (\Exception $e) {
	$this->transactionFactory->rollBack();
}
```
  
## ■ コントローラ内で使用できるメソッドについて

//第2引数はhogeが無かった場合の戻り値を指定できます  
  
//$_POST値を取得  
$this->request->post('hoge', 1);  
//$_GET値を取得  
$this->request->get('hoge', 2);  
//$_SERVERを取得  
$this->request->server('REQUEST_URI');  
//$_FILESを取得  
$this->request->file('image', null);  
画像であればpng jpg gif bmpのいずれかの場合はImageクラスでラッピングされて取得されます  
それ以外はFileクラスでラッピングされて取得されます  
  
//ビューを指定  
$this->render('/admin/index');  
拡張子は省略できます  
  
//リダイレクト  
$this->redirect('/admin/hoge');  
  
//ビューを使用せずにJsonデータを返却する  
$this->useJsonResponse();  
  
//各アクションの実行前に実行される  
public function before()  
beforeメソッドはBaseControllerに定義されているので、そのメソッドのオーバーライドとなります  
  
//各アクションの実行後に実行される  
public function after()  
afterメソッドはBaseControllerに定義されているので、そのメソッドのオーバーライドとなります  
  
//ビューに値を渡す  
//クラスを渡す場合は名前空間を渡し、変数を渡す場合は第1引数に変数名、第2引数に値をセットしてください  
$this->set('System\Util\StringOperator');  
$this->set('hoge', 1);  
  
尚、全てのコントローラより先に処理が必要な場合は  
FirstProcessクラスのexecuteメソッドに処理を記述してください。  
  
## ■ ビューの記述方法について

//値を表示する場合は下記の記述になり、セミコロンは不要です  
{{$hoge}}  
  
//式の場合はコロンかセミコロンを付けてください  
{{if (true === $hoge):}}  
{{endif;}}  
  
//クラスからstaticメソッドを呼ぶ場合です  
{{StringOperator::columnToGetter('fire_bird')}}  
  
//ビューのみで使えるhtmlspecialchars()関数の別名です  
//ユーザーからの入力値を表示する場合は必ずこの関数を通してください  
{{escape($hoge)}}  
    
//現在のビューから他のビューを呼ぶ場合の記述です  
{{appendView('hoge/index');}}  


## ■ Primitive型について

Primitive型としてInt,String,Float,Booleanのクラスが用意されていますが、  
様々な問題により、フレームワーク上でPrimitive型をそのまま渡して処理できるようにはなっていません。  
また、System\Core\Extend\Module\AutoUseModuleを使用すれば  
クラス内で各Primitive型をuseせずともタイプヒントにPrimitive型を使用できますが、  
潜在的なバグが発生する可能性があり、デフォルトでは使用されていません。  
また、動作も保証できません。  
  
## ■ Testディレクトリについて

このフレームワーク自体のテストコードが置いてあります。  
makefileの存在する階層で make test と入力し、Enterを押すとテストが自動で実行されます。  
もしもあなたがフレームワーク自体を修正した後にテストを実行してエラーが発生した場合、  
他の個所への影響に気づけるかもしれません。  
  
## ■ デバッグ用関数について

//var_dump(); exit; のラッパー  
ve();  
  
//var_dump(); のラッパー  
v();  
  
//memory_get_usage(); memory_get_peak_usage(); exit; のラッパー  
mem();  
