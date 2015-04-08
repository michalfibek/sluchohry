<?php
namespace App\Module\Front\Game\Presenters;

use Nette,
	App\Model;
use Tracy\Debugger;

/**
 * Homepage presenter.
 */
class MelodicCubesPresenter extends \App\Module\Base\Presenters\BaseGamePresenter
{
	// define cube splits count by difficulty
	const
		DIFFICULTY_1_SPLITS = 2,
		DIFFICULTY_2_SPLITS = 4,
		DIFFICULTY_3_SPLITS = 8;

	/** @inject @var Model\Song */
	public $song;
	protected $cubeCount;


	public function startup()
	{
		parent::startup();
	}

	protected function setAssetsByDifficulty()
	{
		switch ($this->difficulty)
		{
			case 1:
				$this->cubeCount = self::DIFFICULTY_1_SPLITS;
				break;
			case 2:
				$this->cubeCount = self::DIFFICULTY_2_SPLITS;
				break;
			case 3:
				$this->cubeCount = self::DIFFICULTY_3_SPLITS;
				break;
		}
	}

	protected function getAssetsById($id)
	{
		if ($song = $this->song->getById($id, true))
		{
			$assets['song'] = $song;
			$assets['markers'] = $this->song->getCubeMarkersByCount($id, $this->cubeCount);
			return $assets;
		} else {
			return null;
		}

	}

	protected function getAssetsRandom()
	{
		$omitSongs = ($this->gameSession['melodicCubesHistory']) ? explode('-',$this->gameSession['melodicCubesHistory']) : null;
		if ($song = $this->song->getRandom($omitSongs, true, self::GAME_MELODIC_CUBES))
		{
			$assets['song'] = $song;
			$assets['markers'] = $this->song->getCubeMarkersByCount($song->id, $this->cubeCount);
			return $assets;
		} else {
			return null;
		}
	}

	public function actionDefault($id = null, $difficulty = 2, $nextRound = null)
	{
		if ($difficulty)
			$this->difficulty = (int)$difficulty;

		$this->setAssetsByDifficulty();

		if (!$nextRound) {
			unset($this->gameSession['melodicCubesHistory']);
		}
		$this->gameAssets = (isset($id)) ? $this->getAssetsById($id) : $this->getAssetsRandom();
		if (!$this->gameAssets) {
			if (isset($id)) {
				$msg = Nette\Utils\Html::el('span', $this->translator->translate('front.melodicCubes.flash.noMarkers'));
				$status = 'error';
			} else {
				$msg = Nette\Utils\Html::el('span', $this->translator->translate('front.melodicCubes.flash.playedAll'));
				$msg->add( Nette\Utils\Html::el('a', $this->translator->translate('front.melodicCubes.flash.playAgain'))->href($this->link('default')) );
				$status = 'success';
			}
			$this->flashMessage($msg, $status);
			$this->redirect(':Front:Default:');
		}
		if ($nextRound)
			$this->gameSession['melodicCubesHistory'] = $this->gameSession['melodicCubesHistory'].'-'.$this->gameAssets['song']['id'];
		else
			$this->gameSession['melodicCubesHistory'] = $this->gameAssets['song']['id'];
//		Debugger::barDump($this->gameSession);
	}

	public function renderDefault()
	{
		$this->template->song = $this->gameAssets['song'];
		$this->template->markers = $this->gameAssets['markers'];
		$this->template->difficulty = $this->difficulty;

		$originalOrder = $shuffledOrder = range(0, count($this->gameAssets['markers'])-1);
		while ($originalOrder == $shuffledOrder)
		{
			shuffle($shuffledOrder);
		}
		$this->template->shuffledOrder = $shuffledOrder;

//		\Tracy\Debugger::barDump($this->gameAssets['song']);
//		\Tracy\Debugger::barDump($this->gameAssets['markers']);
//		\Tracy\Debugger::barDump($this->template->shuffledOrder);
	}
}
