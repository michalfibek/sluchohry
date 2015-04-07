<?php
namespace App\Components;

use App,
    Nette,
    Nette\Application\UI,
    Nette\Application\UI\Form;
use Tracy\Debugger;


class UserEditForm extends UI\Control
{
    /** @var App\Model\User */
    private $userModel;

    /** @var array of default form settings */
    private $defaults;

    /** @var int user id */
    private $userId;

    /** @var bool */
    private $requirePassword;

    /** @var bool */
    private $roleChanger;

    /** @var array */
    public $onSuccess;

    /** @var array */
    public $onFail;

    /** @var array */
    public $onNoChange;

    /** @var array */
    public $onDuplicateEmail;

    /** @var array */
    public $onDuplicateUsername;




    // TODO FIX form saving -> currently not working callback

    public function __construct(App\Model\User $userModel)
    {
        parent::__construct();
        $this->userModel = $userModel;
        $this->userId = null;

    }

    public function createComponentForm()
    {
        $form = new Form;
        $form->addText('username')
            ->setRequired();

        $form->addPassword('password');
        $form->addPassword('passwordVerify');

        $form->addText('email')
            ->addRule(Form::EMAIL, 'E-mail format is incorrect.')
            ->setRequired();

        $form->addText('realname');

        if ($this->roleChanger)
            $form->addSelect('role_id')
                ->setItems($this->userModel->getRolePairs())
                ->setDefaultValue(4);

        $form->addSubmit('save');

        if ($this->userId)
            $form->addHidden('userId')
                ->setValue($this->userId);

        if ($this->defaults)
            $form->setDefaults($this->defaults);

        if ($this->requirePassword)
        {
            $form['password']->setRequired()
                ->addRule(Form::MIN_LENGTH, 'The password has to be at least %d characters long', 6);
            $form['passwordVerify']->setRequired('Please enter your password second time for verification.')
                ->addRule(Form::EQUAL, 'Passwords do not match', $form['password']);
        }

        $form->onSuccess[] = $this->processForm;

        return $form;
    }

    public function render()
    {
        $this->template->roleChanger = $this->roleChanger;
        $this->template->userId = $this->userId;
        $this->template->setFile(__DIR__ . '/UserEditForm.latte');
        $this->template->render();
    }

    public function processForm(Form $form)
    {
        $values = $form->getValues();

        if (!$this->userModel->isUniqueColumn('email', $values['email'], $this->userId))
            $this->onDuplicateEmail($values);
        if (!$this->userModel->isUniqueColumn('username', $values['username'], $this->userId))
            $this->onDuplicateUsername($values);

        if ($this->userId) // is user id set? update user
        {
            $insertData = array(
                'username' => $values['username'],
                'password' => $values['password'],
                'email' => $values['email'],
                'realname' => $values['realname']
            );

            if ($this->roleChanger)
                $insertData['role_id'] = $values['role_id'];

            $result = $this->userModel->updateById($values['userId'],$insertData);

        } else { // user id not set - insert user

            $insertData = array(
                'username' => $values['username'],
                'password' => $values['password'],
                'email' => $values['email'],
                'realname' => $values['realname'],
                'role_id' => $values['role_id']
            );

            $result = $this->userModel->insert($insertData);
        }

        if ($result == true)
            $this->onSuccess($values);
        else if ($result == 0)
            $this->onNoChange($values);
        else
            $this->onFail();

        return $this;


    }

    public function setDefaults($values)
    {
        $this->defaults = $values;

        return $this;
    }

    public function setUserId($id)
    {
        $this->userId = $id;

        return $this;
    }

    public function setRequirePassword()
    {
        $this->requirePassword = true;

        return $this;
    }

    public function setRoleChanger()
    {
        $this->roleChanger = true;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isRoleChanger()
    {
        return $this->roleChanger;
    }

}

interface IUserEditFormFactory
{
    /** @return UserEditForm */
    function create();
}