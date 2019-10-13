<?php

/**
 * デフォルトのコントローラー
 */

namespace App\Controller;

use System\Core\Route\Controller;
use System\Database\Transaction;
use App\Model\Post;
use App\Model\User;
use App\Model\Category;
use App\Model\Type;

class IndexController extends Controller
{
    private $post;
    private $type;
    private $transaction;

    public function __construct(
        Post $post,
        Type $type,
        Transaction $transaction
    ) {
        $this->post = $post;
        $this->type = $type;
        $this->transaction = $transaction;
    }

    /**
     * デフォルトのアクション
     */
    public function indexAction()
    {
        $a = $this->post->selectQuery()
                /*
          ->select([
              'hoge' => 'post.id',
              'sum' => 'select COUNT(*) from post'
          ])
                 */
          ->join(User::class, ['post.user_id' => 'user.id'])
          ->join(Category::class, ['post.category_id' => 'category.id'])
          ->join(Type::class, ['user.type_id' => 'type.id'])
          ->groupBy('post.id')
          ->fetchAllByKey();
        /*
        $type = Type::make([
            'coordinate' => 'sfsfsf',
            'created_at' => new \DateTime()
        ]);

        try {
            $this->transaction->beginTransaction();
            $this->type->deleteQuery()
                ->where('id', '=', '8')
                ->delete();
            $this->transaction->commit();
        } catch (\Exception $e) {
            $this->transaction->rollBack();
            ve($e->getMessage());
        }
         */
        $this->render('index');
    }
}

