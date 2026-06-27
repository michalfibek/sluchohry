<?php

namespace App\Module\Admin\Presenters;

use Nette,
	App\Model,
	App\Components,
	Contributte\Datagrid\Datagrid,
	Contributte\Datagrid\Column\Action\Confirmation\StringConfirmation,
	Tracy\Debugger;


/**
 * User editing presenter.
 */
class UsersPresenter extends \App\Module\Base\Presenters\BasePresenter
{
	/** @inject @var Model\User */
	public $userModel;

	/** @inject @var Components\IUserProfileFactory */
	public $userProfile;

	/** @inject @var Model\EventListeners\UserListener */
	public $logger;

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
		if ($this->userModel->deleteById($id)) {
			$msg = $this->translator->translate('admin.users.flash.deleted');
			$this->flashMessage($msg, 'success');
		}
	}

	public function	renderDefault()
	{

	}

	public function renderAdd()
	{
		$this->setView('edit');
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
		$form->onSuccessAdd[] = function ($values) {
			$this->logger->onUserProfileSuccessAdd($this->user, $values);
		};
		$form->onSuccessEdit[] = function ($values) {
			$this->logger->onUserProfileSuccessEdit($this->user, $values);
		};
		$form->onReturnAction[] = function() {
			$this->redirect(':Admin:Users:');
		};
		$form->onFailAction[] = function() {
			$this->redirect('this');
		};


		return $form;
	}


	protected function createComponentGrid($name)
	{
		$grid = new Datagrid();
		$this->addComponent($grid, $name);
		$grid->setDataSource($this->userModel->getAll(true));

		$grid->setTranslator($this->translator);

        $grid->addColumnNumber('id','id')
            ->setSortable();
		$grid->addFilterText('id', 'id');

		$grid->addColumnText('username', 'admin.users.username')
			->setSortable();
		$grid->addFilterText('username', 'admin.users.username');

		$grid->addColumnText('realname', 'admin.users.realname')
			->setSortable();
		$grid->addFilterText('realname', 'admin.users.realname');

		$grid->addColumnText('email', 'admin.users.email')
			->setSortable();
		$grid->addFilterText('email', 'admin.users.email');

		$grid->addColumnText('groups', 'admin.users.groups')
			->setSortable()
			->setRenderer(function($item) {
				$groups = $this->userModel->getUserGroups($item->id);
				$render = '';
				foreach ($groups as $g) {
					$render .= '<a href=\''.$this->link('Groups:Edit', $g->group_id).'\' class=\'grid-cell-subitem\'>'.$g->ref('group')->name.'</a>';
				}
				return $render;
			})
			->setTemplateEscaping(false);
		$grid->addFilterText('groups', 'admin.users.groups');

		$grid->addColumnDateTime('create_time', 'admin.users.createTime')
			->setFormat('d.m.Y H:i:s')
			->setSortable();
		$grid->addFilterDateRange('create_time', 'admin.users.createTime');

		$grid->addColumnDateTime('last_login_time', 'admin.users.lastLoginTime')
			->setFormat('d.m.Y H:i:s')
			->setSortable();
		$grid->addFilterDateRange('last_login_time', 'admin.users.lastLoginTime');

		$grid->addAction('edit', 'admin.common.edit')
			->setIcon('pencil')
			->setRenderCondition(function ($item) {
				$roles = $this->userModel->getUserRoles($item->id);
				return $this->acl->isChildRole($roles, $this->user->roles, $this->editNoGroupUsers);
			});

		$grid->addAction('delete', 'admin.common.delete', 'delete!')
			->setIcon('remove')
			->setConfirmation(new StringConfirmation('Do you really want to delete user? All user logs will be deleted too!'))
			->setRenderCondition(function ($item) {
				$roles = $this->userModel->getUserRoles($item->id);
				return $this->acl->isChildRole($roles, $this->user->roles, $this->editNoGroupUsers);
			});

		$grid->setDefaultSort(array(
			'username' => 'ASC'
		));

		return $grid;
	}


}
