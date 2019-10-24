<?php

/**
 * デフォルトのコントローラー
 */

namespace App\Controller;

use System\Core\Route\Controller;

class IndexController extends Controller
{
    public function __construct() {
    }

    /**
     * デフォルトのアクション
     */
    public function indexAction()
    {
        $this->render('index');
    }
}

