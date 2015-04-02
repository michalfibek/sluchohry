<?php

namespace App\Module\Admin\Presenters;

use Nette,
	App\Model,
	Nette\Application\UI\Form,
	Mesour\DataGrid\Grid,
	Mesour\DataGrid\NetteDbDataSource,
	Mesour\DataGrid\Components\Link;
use Tracy\Debugger;


/**
 * Sign in/out presenters.
 */
class GroupPresenter extends \App\Module\Base\Presenters\BasePresenter
{
	/** @inject @var Model\User */
	public  $userRecord;

	/**
	 * @return Form
	 */
	protected function createComponentEditGroupForm()
	{
		$form = new Form;
		$form->addText('name')
			->setRequired();
		$form->addSubmit('save');
		$form->addHidden('groupId');
		$form->onSuccess[] = array($this, 'addEditGroupFormSucceed');

		return $form;
	}

	/**
	 * @param $name
	 * @return Grid
     */
	protected function createComponentGroupDataGrid($name) {
		$source = new NetteDbDataSource($this->userRecord->getGroupAll());
//		$groupCount = $this->userRecord->getGroupCount();
//		Debugger::barDump($groupCount);
		$grid = new Grid($this, $name);
		$table_id = 'id';
		$grid->setPrimaryKey($table_id); // primary key is now used always
		$grid->setDataSource($source);

		$grid->addNumber('id');
		$grid->addText('name', 'Group name');
		$grid->addText('userCount', 'User count')
			->setCallback(function($data) {
				Debugger::barDump($data);
				return $data['id'];
		});

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
			->setConfirm('Do you really want to delete group?')
			->setTitle('delete')
			->setAttribute('href', new Link('delete!', array(
				'id' => '{'.$table_id.'}'
			)));
		return $grid;
	}

	public function addEditGroupFormSucceed($form, $values)
	{
		if (strlen($values['groupId'])>0) {
			$this->userRecord->updateGroup($values['groupId'], $values['name']);
			$this->flashMessage('The group name has been successfully changed.', 'success');
			$this->redirect('default');
		} else {
			$this->userRecord->addGroup($values['name']);
			$this->flashMessage('The group has been successfully added.', 'success');
			$this->redirect('default');
		}
	}

	/**
	 * List all groups
	 *
	 */
	public function actionDefault()
	{

	}

	/**
	 * Delete user by id.
	 *
	 * @param $id
	 */
	public function handleDelete($id)
	{
		$this->userRecord->deleteGroup($id);
	}

	public function renderAdd()
	{

	}

	/**
	 * Edit group by id.
	 *
	 * @param null $id
	 */
	public function	renderEdit($id = NULL)
	{
		if ($groupRow = $this->userRecord->getGroupById($id))
		{
			$form = $this['editGroupForm'];
			$form->setDefaults($groupRow);
			$form['groupId']->setValue($id);
			$this->template->group = $groupRow;
		} else {
			$this->flashMessage('Sorry, this group was not found.', 'error');
			$this->redirect('default');
		}
	}
	/**
	 *
	 */
	public function	renderDefault()
	{
//		$this->template->users = $this->userRecord->getAll();
	}
}
