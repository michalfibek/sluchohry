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
class UserPresenter extends \App\Module\Base\Presenters\BasePresenter
{
	/** @inject @var Model\User */
	public $userModel;

	/** @inject @var Components\IUserProfileFactory */
	public $userProfile;

	private $editNoGroupUsers;

	protected function startup()
	{
		parent::startup();

		if ($this->user->isInRole('editor'))
			$this->editNoGroupUsers = true;
		else
			$this->editNoGroupUsers = false;
	}


	/**
	 * List all users
	 *
	 */
	public function actionDefault()
	{

	}

	public function actionAdd()
	{
		$this['userProfile']
			->setRequirePassword();
	}

	public function actionEdit($id)
	{
		$this['userProfile']
			->edit($id);
	}

	/**
	 * Delete user by id.
	 *
	 * @param $id
	 */
	public function handleDelete($id)
	{
		if ($this->userModel->deleteById($id))
			$this->flashMessage('User successfully deleted.', 'success');
	}

	public function	renderDefault()
	{

	}

	public function renderAdd()
	{

	}

	public function	renderEdit()
	{

	}

	/**
	 * @return Form
	 */
	protected function createComponentUserProfile()
	{
		$form = $this->userProfile->create();
		$form->setDefaultSignals();
		$form->onReturnAction = function() {
			Debugger::barDump('return');
			$this->redirect(':Admin:User:');
		};
		$form->onFailAction = function() {
			$this->redirect('this');
		};


		return $form;
	}


	protected function createComponentGrid($name)
	{
		$grid = new Grid($this, $name);
		$grid->setModel($this->userModel->getAll(true));

		$grid->setFilterRenderType(Grido\Components\Filters\Filter::RENDER_INNER);

        $grid->addColumnNumber('id','id')
            ->setSortable()
			->setFilterText();

		$grid->addColumnText('username', 'Username')
			->setSortable()
			->setFilterText();

		$grid->addColumnText('realname', 'Full name')
			->setSortable()
			->setFilterText();

		$grid->addColumnText('email', 'E-mail')
			->setSortable()
			->setFilterText();

		$grid->addColumnText('groups', 'Groups')
			->setSortable()
			->setCustomRender(function($item) {
				$groups = $this->userModel->getUserGroups($item->id);
				$render = '';
				foreach ($groups as $g) {
					$render .= '<a href=\''.$this->link('Group:Edit', $g->group_id).'\' class=\'grid-cell-subitem\'>'.$g->ref('group')->name.'</a>';
				}
				return $render;
			})
			->setFilterText();

		$grid->addColumnDate('create_time', 'Created')
			->setDateFormat('d.m.Y H:i:s')
			->setSortable()
			->setFilterDateRange();

		$grid->addColumnDate('last_login_time', 'Last login')
			->setDateFormat('d.m.Y H:i:s')
			->setSortable()
			->setFilterDateRange();

		$grid->addActionHref('edit', 'Edit')
			->setIcon('fa fa-pencil')
			->setDisable(function ($item) {
				$roles = $this->userModel->getUserRoles($item->id);
				return !$this->acl->isChildRole($roles, $this->user->roles, $this->editNoGroupUsers);
			});

		$grid->addActionHref('delete', 'Delete', 'delete!')
			->setIcon('fa fa-remove')
			->setConfirm('Do you really want to delete user? All user logs will be deleted too!')
			->setDisable(function ($item) {
				$roles = $this->userModel->getUserRoles($item->id);
				return !$this->acl->isChildRole($roles, $this->user->roles, $this->editNoGroupUsers);
			});


		$grid->setDefaultSort(array(
			'username' => 'ASC'
		));

		return $grid;
	}


}
