<?php

namespace App\Module\Admin\Presenters;

use Nette,
	App\Model,
	App\Model\Event,
	App\Components,
	Grido,
	Grido\Grid,
	Tracy\Debugger;


/**
 * User editing presenter.
 */
class StatsPresenter extends \App\Module\Base\Presenters\BasePresenter
{
	/** @inject @var Model\Event */
	public $event;

	/** @inject @var Model\User */
	public $userModel;

	/** @inject @var Model\Game */
	public $gameModel;

	/** @inject @var Model\Score */
	public $scoreModel;

	/** @inject @var Model\Group */
	public $groupModel;

	/** @inject @var Components\IUserProfileFactory */
	public $userProfile;

		public function actionDefault()
	{
		if (!$this->user->isAllowed($this->name, 'default'))	{
			$this->flashMessage('Access denied.', 'error');
			$this->redirect(':Admin:Default');
		}

	}

	public function	renderDefault()
	{

	}

	public function	renderGames()
	{
		$this->template->favGameStats = $this->event->getGameStats(Event::CLASS_GAME_STARTED);

		$this->template->gameRatioMelodicCubes = $this->event->getGameRatio('melodicCubes');
		$this->template->gameRatioPexeso = $this->event->getGameRatio('pexeso');
		$this->template->gameRatioNoteSteps = $this->event->getGameRatio('noteSteps');
		$this->template->gameRatioFaders = $this->event->getGameRatio('faders');
	}

	public function	renderUser($id)
	{
		$this->template->userRow = $this->userModel->getById($id);
		$this->template->favGameStats = $this->event->getGameStats(Event::CLASS_GAME_STARTED, null, $id);

		$this->template->gameRatioMelodicCubes = $this->event->getGameRatio('melodicCubes', $id);
		$this->template->gameRatioPexeso = $this->event->getGameRatio('pexeso', $id);
		$this->template->gameRatioNoteSteps = $this->event->getGameRatio('noteSteps', $id);
		$this->template->gameRatioFaders = $this->event->getGameRatio('faders', $id);

		$this->template->scoreSum = $this->scoreModel->getScoreSum($id);

		$userRoles = $this->userModel->getUserRoles($id);
		$this->template->allowEdit = $this->acl->isChildRole($userRoles, $this->user->roles, false);
	}

	protected function createComponentScoreGrid($name)
	{
		$grid = new Grid($this, $name);
		$grid->setModel($this->scoreModel->getScoreView());

		$grid->setTranslator($this->translator);

//		$grid->setFilterRenderType(Grido\Components\Filters\Filter::RENDER_INNER);

		$grid->addColumnText('realname', 'admin.stats.realName')
			->setCustomRender(function($item) {
				$url = $this->link('User', $item->user_id);
				return '<a href="'. $url . '">' . $item->realname . '</a>';
			})
			->setSortable()
			->setFilterText()
			->setSuggestion('realname');

		$grid->addColumnText('score_easy', 'admin.stats.scoreSum.easy')
			->setSortable();

		$grid->addColumnText('score_medium','admin.stats.scoreSum.medium')
			->setSortable();

		$grid->addColumnText('score_hard', 'admin.stats.scoreSum.hard')
			->setSortable();

		$groupList = $this->groupModel->getAll()->fetchPairs('id', 'name');

		$grid->addColumnText('user_group', 'admin.stats.groups')
			->setCustomRender(function($item) {
				$groups = $this->userModel->getUserGroups($item['user_id']);

				foreach ($groups as $g) {
					$renderGroups[] = $this->groupModel->getById($g->group_id)->name;
				}

				return implode(', ', $renderGroups);
			})
			->setFilterSelect($groupList)
			->setWhere(function($value, \Nette\Database\Table\Selection $connection) {
				$usersFiltered = $this->groupModel->getById($value)->related('user')->fetchPairs(NULL, 'user_id');
				$value
					? $connection->where('user_id IN' , $usersFiltered)
					: NULL;
			});;

		$grid->addColumnText('play_count', 'admin.stats.totalPlays')
			->setCustomRender(function($item) {
				$playCount = $this->scoreModel->getPlayCountPerUser($item['user_id']);

				foreach ($playCount as $cnt) {
					$gameName = $this->gameModel->getById($cnt['game_id'])->name;
					$renderCount[] = $gameName . ':&nbsp;' . $cnt['play_count'];
				}

				return implode(', ', $renderCount);
			});


		$grid->setDefaultSort(array(
			'realname' => 'ASC'
		));

		return $grid;
	}


}
