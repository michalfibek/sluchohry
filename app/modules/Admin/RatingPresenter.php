<?php

namespace App\Module\Admin\Presenters;

use Nette,
	App\Model,
	App\Components,
	Grido,
	Grido\Grid,
	Tracy\Debugger;


/**
 * User editing presenter.
 */
class RatingPresenter extends \App\Module\Base\Presenters\BasePresenter
{
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

	protected function createComponentScoreGrid($name)
	{
		$grid = new Grid($this, $name);
		$grid->setModel($this->scoreModel->getScoreView());

//		$grid->setFilterRenderType(Grido\Components\Filters\Filter::RENDER_INNER);

		$grid->addColumnNumber('user_id','User id')
			->setSortable()
			->setFilterText();

		$grid->addColumnText('realname', 'Full name')
			->setSortable()
			->setFilterText();

		$grid->addColumnText('score_easy', 'Score sum easy')
			->setSortable();

		$grid->addColumnText('score_medium', 'Score sum medium')
			->setSortable();

		$grid->addColumnText('score_hard', 'Score sum hard')
			->setSortable();

		$grid->addColumnText('user_group', 'Groups')
			->setCustomRender(function($item) {
				$groups = $this->userModel->getUserGroups($item['user_id']);

				foreach ($groups as $g) {
					$renderGroups[] = $this->groupModel->getById($g->group_id)->name;
				}
				Debugger::barDump($groups);

				return implode(', ', $renderGroups);
			});

		$grid->addColumnText('play_count', 'Total plays count')
			->setCustomRender(function($item) {
				$playCount = $this->scoreModel->getPlayCountPerUser($item['user_id']);

				Debugger::barDump($playCount);

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
