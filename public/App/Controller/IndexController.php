<?php

/**
 * デフォルトのコントローラー
 */

namespace App\Controller;

use System\Core\Route\BaseController;
use App\Model\Holiday;
use App\Model\Code;
use App\Model\CodeGroup;
use System\Database\TransactionFactory;

class IndexController extends BaseController
{
	private $holiday;
	private $code;
	private $codeGroup;
	private $transactionFactory;

	public function __construct(
		Holiday $holiday,
		Code $code,
		CodeGroup $codeGroup,
		TransactionFactory $transactionFactory
	) {
		$this->holiday = $holiday;
		$this->code = $code;
		$this->codeGroup = $codeGroup;
		$this->transactionFactory = $transactionFactory;
	}

	/**
	 * デフォルトのアクション
	 */
	public function indexAction()
	{
		$a = $this->code->selectQuery()
			//->select(['hoge' => 'MAX(t_code.update_datetime)'])
			->join(['t_code_group'], ['t_code.code_group_id' => 't_code_group.code_group_id'])
			->where('t_code.code_group_id', 'IN', ['2527b221e6093e85a1b508e6fea4fb91', 'c4da9ecfa0c107fa7e1f67d420d05fe3'])
			->otherwise($this->code->getCondition()
				->where('t_code.code_group_id', '=', '784b13cd78061b63b1bb41ad7007e59f')
				->otherwise('t_code.code_group_id', '=', '9a713a909115a530e0c828e3610a75c0')
			)
			->fetchAll();
ve($a[3]->CodeGroup);
			/*
		$this->transactionFactory->beginTransaction();
		try {

			$this->transactionFactory->commit();
		} catch (\Exception $e) {
				ve($e->getMessage());
			$this->transactionFactory->rollBack();
		}
			ve(99);
			 */
		$this->render('index');
	}
}

