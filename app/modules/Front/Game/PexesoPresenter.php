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

	}

	protected function getAssetsById($id)
	{
		$song = $this->songStorage->getSongById($id);
		$markers = $this->songStorage->getMarkers($id);

		return array($song, $markers);
	}

	protected function getAssetsRandom()
	{
		$song = $this->songStorage->getSongRandom();
		$markers = $this->songStorage->getMarkers($song->getPrimary());

		return array($song, $markers);
	}

	public function renderDefault()
	{
		parent::renderDefault();

	}
}
