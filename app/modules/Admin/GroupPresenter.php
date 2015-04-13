<?php

namespace App\Module\Admin\Presenters;

use Nette,
	App\Model,
	Nette\Application\UI\Form,
	Grido,
	Grido\Grid,
	Tracy\Debugger;


/**
 * Sign in/out presenters.
 */
class GroupPresenter extends \App\Module\Base\Presenters\BasePresenter
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
		$grid = new Grid($this, $name);
		$grid->setModel($this->group->getAll());

		$grid->setEditableColumns();
		$grid->setTemplateFile(__DIR__.'/templates/components/simpleGrid.latte');

//		$grid->setFilterRenderType(Grido\Components\Filters\Filter::RENDER_OUTER);

		$grid->addColumnNumber('id', 'id')
			->setSortable();
//			->setFilterText();

		$grid->addColumnText('name', 'Group name')
			->setSortable()
			->setFilterText();

		$grid->addColumnText('role_id', 'Role')
			->setSortable()
			->setCustomRender(function($item) {
				return $this->group->getRoleById($item->role_id)->name;
			})
			->setFilterText();

//		$grid->addColumnText('userCount', 'User count')
//			->setSortable()
//			->setFilterText();

//		$grid->addColumnDate('create_time', 'Created')
//			->setDateFormat('d.m.Y H:i:s')
//			->setSortable()
//			->setFilterDateRange();

		$grid->addActionHref('edit', 'Edit')
			->setIcon('fa fa-pencil')
			->setDisable(function ($item) {
				return (!$this->user->isAllowed($this->name, 'edit'));
			});

		$grid->addActionHref('delete', 'Delete', 'delete!')
			->setIcon('fa fa-remove')
			->setConfirm('Do you really want to delete this group?')
			->setDisable(function ($item) {
				return (!$this->user->isAllowed($this->name, 'delete'));
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
		if (!$this->user->isAllowed($this->name, 'delete')) {
			$this->flashMessage($this->translator->translate('front.auth.flash.actionForbidden'), 'error');
		}
		$this->group->deleteById($id);
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
		Debugger::barDump($this->getSignal());
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
