<?php
namespace App\Module\Front\Game\Presenters;

use Nette,
	App\Model;

/**
 * Homepage presenter.
 */
class PexesoPresenter extends \App\Module\Base\Presenters\BaseGamePresenter
{

	public function __construct()
	{
		parent::__construct();

	}

	protected function getAssetsById($id)
	{
	}

	protected function getAssetsRandom()
	{
	}

	public function renderDefault()
	{
		parent::renderDefault();

	}
}
