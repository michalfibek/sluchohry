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

    /** @var Nette\Security\IAuthorizator */
    private $acl;

    /** @var Nette\Security\User */
    private $user;

    /** @var array of default form settings */
    private $userRow;

    /** @var int user id */
    private $userId;

    /** @var bool */
    private $requirePassword;

    /** @var bool */
    private $roleChanger;

    /** @var array */
    public $onSuccessAdd;

    /** @var array */
    public $onSuccessEdit;

    /** @var array */
    public $onFail;

    /** @var array */
    public $onNoChange;

    /** @var array */
    public $onDuplicateEmail;

    /** @var array */
    public $onDuplicateUsername;

    /** @var array */
    public $onAccessDenied;

    /** @var array */
    public $onNotFound;

    // TODO FIX form saving -> currently not working callback

    public function __construct(App\Model\User $userModel, Nette\Security\IAuthorizator $acl, Nette\Security\User $user)
    {
        parent::__construct();
        $this->userModel = $userModel;
        $this->acl = $acl;
        $this->user = $user;
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

        if ($this->userRow)
            $form->setDefaults($this->userRow);

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
            unset($values['password'],$values['passwordVerify']);

            if ($this->roleChanger)
                $insertData['role_id'] = $values['role_id'];

            $result = $this->userModel->updateById($values['userId'],$insertData);

            if ($result == true)
                $this->onSuccessEdit($values);

        } else { // user id not set - insert user

            $insertData = array(
                'username' => $values['username'],
                'password' => $values['password'],
                'email' => $values['email'],
                'realname' => $values['realname'],
                'role_id' => $values['role_id']
            );
            unset($values['password'],$values['passwordVerify']);

            $result = $this->userModel->insert($insertData);

            if ($result == true)
                $this->onSuccessAdd($values);
        }

        if ($result == 0)
            $this->onNoChange($values);
        else
            $this->onFail($values);

        return $this;

    }

    public function getUser()
    {
        return $this->userRow;
    }

    public function edit($userId) {

        $this->userId = $userId;

        if ($userRow = $this->userModel->getById($this->userId)) {

            if (!$this->acl->isChildRole($userRow->ref('role')['name'], $this->user->roles[0])) {
                $this->onAccessDenied($userRow);
            }

            $this->userRow = $userRow;

        } else {
            $this->onUserNotFound();
        }

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