<?php
namespace App\Module\Front\Game\Presenters;

use Nette,
	App\Model;
use Tracy\Debugger;

/**
 * Homepage presenter.
 */
class PexesoPresenter extends \App\Module\Base\Presenters\BaseGamePresenter
{
	/** @inject @var Model\Song */
	public $song;

	private $songPairCount;


	public function startup()
	{
		parent::startup();
		$this->gameId = self::GAME_PEXESO;
	}

	protected function getAssetsById($id)
	{

	}

	protected function getAssetsRandom()
	{
//		$omitSongs = ($this->gameSession['pexesoHistory']) ? explode('-',$this->gameSession['melodicCubesHistory']) : null;

		if ($assets = $this->song->getRandom(null, false, self::GAME_PEXESO, $this->songPairCount)) {
			return $assets;
		} else {
			return null;
		}
	}

	public function actionDefault($difficulty = 2, $nextRound = null)
	{
		if (!$nextRound) {
			$this->historyClear();
		}

		$this->difficulty = (int)$difficulty;

		$this->songPairCount = $this->getVariationByDifficulty($this->difficulty);

		$this->gameAssets = $this->getAssetsRandom();
		if (!$this->gameAssets) {
			$this->flashMessage($this->translator->translate('front.pexeso.flash.noSongFound'), 'error');
			$this->redirect(':Front:Default:');
		}

		$this->historyAdd();
	}

	public function renderDefault()
	{
		$this->template->songs = $this->gameAssets;
		$this->template->difficulty = $this->difficulty;

	}
}
