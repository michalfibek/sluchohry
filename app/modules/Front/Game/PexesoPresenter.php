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
		DIFFICULTY_1_PAIRS = 4,
		DIFFICULTY_2_PAIRS = 6,
		DIFFICULTY_3_PAIRS = 11;

	/** @inject @var Model\Song */
	public $song;
	private $songPairCount;


	public function startup()
	{
		parent::startup();
	}

	protected function setAssetsByDifficulty()
	{
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

		if ($assets = $this->song->getRandom(null, false, self::GAME_PEXESO, $this->songPairCount)) {
			return $assets;
		} else {
			return null;
		}
	}

	public function actionDefault($difficulty = 2, $nextRound = null)
	{
		if ($difficulty)
			$this->difficulty = (int)$difficulty;

		$this->setAssetsByDifficulty();

		$this->gameAssets = $this->getAssetsRandom();
		if (!$this->gameAssets) {
			$this->flashMessage($this->translator->translate('front.pexeso.flash.noSongFound'), 'errror');
			$this->redirect(':Front:Default:');
		}
	}

	public function renderDefault()
	{
		$this->template->difficultyText = $this->getDifficultyText();
		$this->template->songs = $this->gameAssets;
		$this->template->difficulty = $this->difficulty;

	}
}
