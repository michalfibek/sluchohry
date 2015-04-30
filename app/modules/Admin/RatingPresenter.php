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

		$grid->setTranslator($this->translator);

//		$grid->setFilterRenderType(Grido\Components\Filters\Filter::RENDER_INNER);

		$grid->addColumnText('realname', 'admin.rating.realName')
			->setSortable()
			->setFilterText()
			->setSuggestion('realname');

		$grid->addColumnText('score_easy', 'admin.rating.scoreSum.easy')
			->setSortable();

		$grid->addColumnText('score_medium','admin.rating.scoreSum.medium')
			->setSortable();

		$grid->addColumnText('score_hard', 'admin.rating.scoreSum.hard')
			->setSortable();

		$groupList = $this->groupModel->getAll()->fetchPairs('id', 'name');

		$grid->addColumnText('user_group', 'admin.rating.groups')
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

		$grid->addColumnText('play_count', 'admin.rating.totalPlays')
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
