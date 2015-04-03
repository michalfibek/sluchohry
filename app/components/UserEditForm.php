<?php
namespace App\Components;

use App,
    Nette,
    Nette\Application\UI,
    Nette\Application\UI\Form;
use Tracy\Debugger;


class UserEditForm extends UI\Control
{
    private $userModel;
    private $defaults;
    private $userId;

    public $onUserSave;

    // TODO FIX form saving -> currently not working callback

    public function __construct(App\Model\User $userModel)
    {
        parent::__construct();
        $this->userModel = $userModel;

    }

    public function createComponentForm()
    {
        $form = new Form;
        $form->addText('username')
            ->setRequired();
        $form->addPassword('password');
        $form->addPassword('passwordVerify');
        $form->addText('email')
            ->addRule(Form::EMAIL, 'E-mail format is incorrect.');
        $form->addText('realname');
        $form->addSelect('role_id')
            ->setItems($this->userModel->getRolePairs())
            ->setDefaultValue(4);
        $form->addSubmit('save');
        $form->addHidden('userId');

        if ($this->defaults)
            $form->setDefaults($this->defaults);

        $form['userId']->setValue($this->userId);

        $form->onSuccess[] = $this->processForm;

        return $form;
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/UserEditForm.latte');
        $this->template->render();
    }

    public function processForm($form)
    {
        $this->onUserSave($this, $user);
    }

    public function setDefaults($values)
    {
        $this->defaults = $values;
    }

    public function setUserId($id)
    {
        $this->userId = $id;
    }
}

interface IUserEditFormFactory
{
    /** @return UserEditForm */
    function create();
}