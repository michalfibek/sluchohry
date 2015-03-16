<?php
namespace App\Presenters;

use Nette,
	App\Model;

/**
 * Homepage presenter.
 */
class GamePresenter extends BasePresenter
{
	/** @var Nette\Database\Context */
	private $database;
	private $songCur;
	private $splitDuration;
	protected $splitCount = 8;
	protected $shuffledOrder;

	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}

	public function actionMelodicCubes()
	{
		// select existing song, while loop is for conditions where some songs were removed from db
		while (!$this->songCur) {
			$songCount = $this->database->table('song')->count("*");
			$randSongId = rand(1,$songCount);
			$this->songCur = $this->database->table('song')->get($randSongId);
		}
		$this->splitDuration = $this->songCur->duration/$this->splitCount;
		$this->shuffledOrder = range(1,$this->splitCount);
		shuffle($this->shuffledOrder);
	}

	public function renderDefault()
	{

	}
    public function renderPexeso()
    {

    }

	public function renderMelodicCubes()
	{
		$this->template->song = $this->songCur;
		$this->template->splitCount = $this->splitCount;
		$this->template->splitDuration = $this->splitDuration;
		$this->template->shuffledOrder = $this->shuffledOrder;
	}

}
