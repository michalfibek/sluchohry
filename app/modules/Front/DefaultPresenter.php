<?php
namespace App\Module\Front\Presenters;

use Nette,
	App\Model;
use Tracy\Debugger;

/**
 * Homepage presenter.
 */
class DefaultPresenter extends \App\Module\Base\Presenters\BasePresenter
{
	public function renderDefault()
	{
		// $this->template->posts = $this->database->table('posts')
		// 	->order('created_at DESC')
		// 	->limit(5);
	}

}
