<?php

namespace App\Module\Admin\Presenters;

use Nette,
	App\Model,
	Nette\Application\UI\Form,
	Contributte\Datagrid\Datagrid,
	Contributte\Datagrid\Column\Action\Confirmation\StringConfirmation,
	Tracy\Debugger;


/**
 * Sign in/out presenters.
 */
class GroupsPresenter extends \App\Module\Base\Presenters\BasePresenter
{
	/** @inject @var Model\User */
	public $userModel;

	/** @inject @var Model\Group */
	public $group;

	/**
	 * @return Form
	 */
	protected function createComponentEditGroupForm()
	{
		$form = new Form;
		$form->addText('name')
			->setRequired();
		$form->addSelect('role_id', 'Role')
			->setItems($this->group->getRolePairs());
		$form->addSubmit('save');
		$form->addHidden('groupId');
		$form->onSuccess[] = array($this, 'addEditGroupFormSucceed');

		return $form;
	}


	protected function createComponentGrid($name)
	{
		$grid = new Datagrid();
		$this->addComponent($grid, $name);
		$grid->setDataSource($this->group->getAll());

		$grid->setTranslator($this->translator);

		$grid->addColumnNumber('id', 'admin.common.id')
			->setSortable();

		$grid->addColumnText('name', 'admin.groups.name')
			->setSortable();
		$grid->addFilterText('name', 'admin.groups.name');

		$grid->addColumnText('role_id', 'admin.groups.role')
			->setSortable()
			->setRenderer(function($item) {
				return $this->group->getRoleById($item->role_id)->name;
			});
		$grid->addFilterText('role_id', 'admin.groups.role');

		$grid->addAction('edit', 'admin.common.edit')
			->setIcon('pencil')
			->setRenderCondition(function ($item) {
				return $this->user->isAllowed($this->getName(), 'edit');
			});

		$grid->addAction('delete', 'admin.common.delete', 'delete!')
			->setIcon('remove')
			->setConfirmation(new StringConfirmation('Do you really want to delete this group?'))
			->setRenderCondition(function ($item) {
				return $this->user->isAllowed($this->getName(), 'delete');
			});

		$grid->setDefaultSort(array(
			'name' => 'ASC'
		));

		return $grid;
	}

	public function addEditGroupFormSucceed($form, $values)
	{
		$data = array(
			'name' => $values['name'],
			'role_id' => $values['role_id']
		);

		if (strlen($values['groupId'])>0) {
			$this->group->updateById($values['groupId'], $data);
			$this->flashMessage('The group name has been successfully changed.', 'success');
			$this->redirect('default');
		} else {
			$this->group->insert($data);
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
		if (!$this->user->isAllowed($this->getName(), 'delete')) {
			$this->flashMessage($this->translator->translate('front.auth.flash.actionForbidden'), 'error');
		}
		$this->group->deleteById($id);
	}

	public function renderAdd()
	{
		$this->setView('edit');
	}

	/**
	 * Edit group by id.
	 *
	 * @param null $id
	 */
	public function	renderEdit($id = NULL)
	{
//		Debugger::barDump($this->getSignal());
		if ($groupRow = $this->group->getById($id))
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
//		$this->template->users = $this->userModel->getAll();
	}
}
