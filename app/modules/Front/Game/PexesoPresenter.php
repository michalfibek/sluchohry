<?php
namespace App\Module\Front\Game\Presenters;

use Nette,
	App\Model;

/**
 * Homepage presenter.
 */
class PexesoPresenter extends \App\Module\Base\Presenters\BaseGamePresenter
{
	// define cube splits count by difficulty
	const
		DIFFICULTY_1_PAIRS = 3,
		DIFFICULTY_2_PAIRS = 6,
		DIFFICULTY_3_PAIRS = 8;

	/** @inject @var Model\SongStorage */
	public $songStorage;
	private $songPairCount;


	public function startup()
	{
		parent::startup();

		$this->difficulty = 1; // TODO this is hardcoded, remove after difficulty implementation
		switch ($this->difficulty)
		{
			case 1:
				$this->songPairCount = self::DIFFICULTY_1_PAIRS;
				break;
			case 2:
				$this->songPairCount = self::DIFFICULTY_2_PAIRS;
				break;
			case 3:
				$this->songPairCount = self::DIFFICULTY_3_PAIRS;
				break;
		}
	}

	protected function getAssetsById($id)
	{
	}

	protected function getAssetsRandom()
	{
//		$omitSongs = ($this->gameSession['pexesoHistory']) ? explode('-',$this->gameSession['melodicCubesHistory']) : null;

		if ($assets = $this->songStorage->getSongRandom(null, false, self::GAME_PEXESO, $this->songPairCount)) {
			return $assets;
		} else {
			return null;
		}
	}

	public function actionDefault($nextRound = null)
	{
		$this->gameAssets = $this->getAssetsRandom();
		if (!$this->gameAssets) {
			$this->flashMessage($this->translator->translate('front.pexeso.flash.noSongFound'), 'errror');
			$this->redirect(':Front:Default:');
		}
	}

	public function renderDefault()
	{
		$this->template->songs = $this->gameAssets;
		$this->template->difficulty = $this->difficulty;

	}
}
