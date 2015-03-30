<?php

namespace App\Module\Admin\Presenters;

use Nette,
	App\Model,
	Nette\Application\UI\Form;


/**
 * Sign in/out presenters.
 */
class UserPresenter extends \App\Module\Base\Presenters\BasePresenter
{
	private $userRecord;

	/**
	 * @param Model\User $userRecord
	 */
	public function __construct(Model\User $userRecord)
	{
		$this->userRecord = $userRecord;
	}

	/**
	 * @return Form
	 */
	protected function createComponentEditUserForm()
	{
		$form = new Form;
		$form->addText('username')
			->setRequired();
		$form->addPassword('password')
			->setRequired()
			->addRule(Form::MIN_LENGTH, 'The password has to be at least %d characters long', 6);
		$form->addPassword('passwordVerify')
			->setRequired('Please enter your password second time for verification.')
			->addRule(Form::EQUAL, 'Passwords do not match', $form['password']);
		$form->addText('email')
			->addRule(Form::EMAIL, 'E-mail format is incorrect.');
		$form->addText('realname');
		$form->addSelect('role_id')
			->setItems($this->userRecord->getRoleArray())
			->setDefaultValue(4);
		$form->addSubmit('save');
		$form->onSuccess[] = array($this, 'editUserFormSucceed');

		return $form;
	}

	/**
	 * @param $form
	 * @param $values
	 */
	public function editUserFormSucceed($form, $values)
	{
		$this->actionAdd($values['username'], $values['password'], $values['email'], $values['realname'], $values['role_id']);
	}

	/**
	 *
	 */
	public function actionDefault()
	{

	}

	/**
	 * @param $username
	 * @param $password
	 * @param $email
	 * @param $realname
	 * @param $roleId
	 * @throws Model\DuplicateNameException
	 */
	public function actionAdd($username, $password, $email, $realname, $roleId)
	{
		$this->userRecord->add($username, $password, $email, $realname, $roleId);
		$this->flashMessage('The user has been successfully added.', 'success');
		$this->redirect('default');
	}

	/**
	 * @param $id
	 */
	public function actionDelete($id)
	{

	}

	/**
	 * @param null $id
	 */
	public function	renderEdit($id = NULL)
	{
		if ($id)
		{
			$userRow = $this->userRecord->getById($id);

			if (!$userRow) {
				$this->flashMessage('Sorry, this user was not found.', 'error');
				$this->redirect('default');
			}

			$this['editUserForm']->setDefaults($userRow);
			$this->template->userRow = $userRow;
		}
	}

	/**
	 *
	 */
	public function	renderDefault()
	{
		$this->template->users = $this->userRecord->getAll();
	}

	/**
	 * @param $userId
	 * @param $roleName
	 */
	public function actionSetRole($userId, $roleName)
	{
		$this->addRole('guest');
		$this->getUser()->logout();
		$this->flashMessage('The role has been successfully set.', 'success');
		$this->redirect('default');
	}

}
