<?php

namespace App\Module\Admin\Presenters;

use Nette,
	App\Model,
	App\Components,
	Mesour\DataGrid\Grid,
	Mesour\DataGrid\NetteDbDataSource,
	Mesour\DataGrid\Components\Link;
use Tracy\Debugger;


/**
 * User editing presenter.
 */
class UserPresenter extends \App\Module\Base\Presenters\BasePresenter
{
	/** @inject @var Model\User */
	public $userModel;

	/** @inject @var Components\IUserProfileFactory */
	public $userProfile;


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

		return $form;
	}

	/**
	 * @param $name
	 * @return Grid
	 */
	protected function createComponentUserDataGrid($name) {
		$source = new NetteDbDataSource($this->userModel->getAll());
		$grid = new Grid($this, $name);
		$table_id = 'id';
		$grid->setPrimaryKey($table_id); // primary key is now used always
		$grid->setDataSource($source);

		Link::$checkPermissionCallback = function($link) {
			switch ($link) {
				case 'edit':
					if (!$this->user->isAllowed($this->name, 'edit'))
						return false;
					break;
				case 'delete!':
					if (!$this->user->isAllowed($this->name, 'delete'))
						return false;
					break;
			}
			return $link;
		};

		$grid->addNumber('id');
		$grid->addText('role_id', 'Role');
		$grid->addText('username', 'Username');
		$grid->addText('realname', 'Full name');
		$grid->addText('email', 'E-Mail');
		$grid->addDate('create_time', 'Create time')
			->setFormat('j.n.Y H:i:s');
		$grid->addDate('last_login_time', 'Last login')
			->setFormat('j.n.Y H:i:s');

		$actions = $grid->addActions('Actions');
		$actions->addButton()
			->setType('btn-primary')
			->setIcon('fa fa-pencil')
			->setTitle('edit')
			->setAttribute('href', new Link('edit', array(
				'id' => '{'.$table_id.'}'
			)));
		$actions->addButton()
			->setType('btn-danger')
			->setIcon('fa fa-remove')
			->setConfirm('Do you really want to delete user? All user logs will be deleted too!')
			->setTitle('delete')
			->setAttribute('href', new Link('delete!', array(
				'id' => '{'.$table_id.'}'
			)));

		return $grid;
	}

}
