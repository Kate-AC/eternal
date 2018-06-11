<?php

/**
 * デフォルトのコントローラー
 */

namespace App\Controller;

use System\Core\Route\BaseController;

class IndexController extends BaseController
{

	public function __construct(
	) {
	}

	/**
	 * デフォルトのアクション
	 */
	public function indexAction()
	{
		$this->render('index');
	}
}

