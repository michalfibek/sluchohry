<?php
namespace App\Module\Front\Game\Presenters;

use Nette,
	App\Model;

/**
 * Homepage presenter.
 */
class MelodicCubesPresenter extends \App\Module\Base\Presenters\BaseGamePresenter
{
	// define cube splits count by difficulty
	const DIFFICULTY_1_SPLITS = 2,
		DIFFICULTY_2_SPLITS = 4,
		DIFFICULTY_3_SPLITS = 8;

	private $songStorage;
	protected $gameAssets;
	protected $difficulty;
	protected $cubeCount;

	public function __construct(Model\SongStorage $songStorage)
	{
		$this->songStorage = $songStorage;
		$this->difficulty = 3; // TODO this is hardcoded, remove after difficulty implementation
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
		$assets['song'] = $this->songStorage->getSongById($id);
		$assets['markers'] = $this->songStorage->getCubeMarkersByCount($id, $this->cubeCount);

		return $assets;
	}

	protected function getAssetsRandom()
	{
		$assets['song'] = $this->songStorage->getSongRandom();
		$assets['markers'] = $this->songStorage->getCubeMarkersByCount($assets['song']->getPrimary(), $this->cubeCount);

		return $assets;
	}

	public function actionDefault($id = null)
	{
		$this->gameAssets = (isset($id)) ? $this->getAssetsById($id) : $this->getAssetsRandom();
	}

	public function renderDefault()
	{
		parent::renderDefault();
		$this->template->song = $this->gameAssets['song'];
		$this->template->markers = $this->gameAssets['markers'];

		$shuffledOrder = range(0, count($this->gameAssets['markers'])-1);
		shuffle($shuffledOrder);
		$this->template->shuffledOrder = $shuffledOrder;

		\Tracy\Debugger::barDump($this->gameAssets['song']);
		\Tracy\Debugger::barDump($this->gameAssets['markers']);
		\Tracy\Debugger::barDump($this->template->shuffledOrder);
	}

}
