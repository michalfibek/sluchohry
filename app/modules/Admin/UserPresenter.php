<?php

namespace App\Module\Admin\Presenters;

use Nette,
	App\Model,
	App\Components,
	Nette\Application\UI\Form,
	Mesour\DataGrid\Grid,
	Mesour\DataGrid\NetteDbDataSource,
	Mesour\DataGrid\Components\Link;
use Tracy\Debugger;


/**
 * Sign in/out presenters.
 */
class UserPresenter extends \App\Module\Base\Presenters\BasePresenter
{
	/** @inject @var Model\User */
	public $userModel;

	/** @inject @var Components\IUserEditFormFactory */
	public $userEditForm;

	private $userRow;

	/**
	 * List all users
	 *
	 */
	public function actionDefault()
	{

	}

	public function actionEdit($id)
	{
		if ($userRow = $this->userModel->getById($id))
		{
			if (!$this->acl->isChildRole($userRow->ref('role')['name'], $this->user->roles[0]))
			{
				$this->flashMessage('Sorry, not enough permissions to edit this user.', 'error');
				$this->redirect('default');
			}
			$form = $this['userEditForm'];
			Debugger::barDump($form);
			$form->setDefaults($userRow);
			$form->setUserId($id);
			$this->userRow = $userRow;
		} else {
			$this->flashMessage('Sorry, this user was not found.', 'error');
			$this->redirect('default');
		}
	}

	/**
	 * Delete user by id.
	 *
	 * @param $id
	 */
	public function handleDelete($id)
	{
		$this->userModel->deleteById($id);
	}

	public function	renderDefault()
	{

	}

	public function renderAdd()
	{
		// require when adding new user
		$this['userEditForm']['password']->setRequired()
			->addRule(Form::MIN_LENGTH, 'The password has to be at least %d characters long', 6);
		$this['userEditForm']['passwordVerify']->setRequired('Please enter your password second time for verification.')
			->addRule(Form::EQUAL, 'Passwords do not match', $this['userEditForm']['password']);
	}

	public function	renderEdit()
	{
		$this->template->userRow = $this->userRow;
	}

	/**
	 * @return Form
	 */
	protected function createComponentUserEditForm()
	{
		$form = $this->userEditForm->create();
		$form->onUserSave[] = array($this, 'addUserEditFormSucceed');

		return $form;
	}

	/**
	 * Edit or add user - called by form
	 *
	 * @param $form
	 * @param $values
	 */
	public function addUserEditFormSucceed($form, $values)
	{
		if (strlen($values['userId'])>0)
		{
			$insertData = array(
				'username' => $values['username'],
				'password' => $values['password'],
				'email' => $values['email'],
				'realname' => $values['realname'],
				'role_id' => $values['role_id']
			);
			$this->userModel->updateById($values['userId'],$insertData);
			$this->flashMessage('The user preferences has been changed.', 'success');
			$this->redirect('default');
		} else {
			$insertData = array(
				'username' => $values['username'],
				'password' => $values['password'],
				'email' => $values['email'],
				'realname' => $values['realname'],
				'role_id' => $values['role_id']
			);
			$this->userModel->insert($insertData);
			$this->flashMessage('The user has been successfully added.', 'success');
			$this->redirect('default');
		}
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
