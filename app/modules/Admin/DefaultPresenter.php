<?php

namespace App\Module\Admin\Presenters;

use App\Model\Event;
use Nette,
	App\Model;
use Tracy\Debugger;


class DefaultPresenter extends \App\Module\Base\Presenters\BasePresenter
{
	/** @inject @var Event */
	public $event;

	public function actionDefault()
	{

	}

	public function renderDefault()
	{
		$this->template->solvedByWeekCurrent = $this->event->getEventClassByWeek(Event::CLASS_GAME_SOLVED, true);
		$this->template->startedByWeekCurrent = $this->event->getEventClassByWeek(Event::CLASS_GAME_STARTED, true);

		$this->template->solvedByWeekPrevious = $this->event->getEventClassByWeek(Event::CLASS_GAME_SOLVED, false);
		$this->template->startedByWeekPrevious = $this->event->getEventClassByWeek(Event::CLASS_GAME_STARTED, false);

		$this->template->favGameStats = $this->event->getGameStats(Event::CLASS_GAME_STARTED);


	}

}
