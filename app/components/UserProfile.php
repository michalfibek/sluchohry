<?php
namespace App\Components;

use App,
    Nette,
    Nette\Application\UI,
    Nette\Application\UI\Form;
use Tracy\Debugger;


class UserProfile extends UI\Control
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
    private $adminMode;

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

    /** @var App\Model\Avatar */
    private $avatar;

    // TODO FIX event listener hooks

    public function __construct(App\Model\User $userModel, Nette\Security\IAuthorizator $acl, Nette\Security\User $user, App\Model\Avatar $avatar)
    {
        parent::__construct();
        $this->userModel = $userModel;
        $this->acl = $acl;
        $this->user = $user;
        $this->userId = null;
        $this->avatar = $avatar;

        if ($this->user->isAllowed('Admin:User', 'edit')) {
            $this->adminMode = true;
        }
    }

    public function createComponentForm()
    {
        $form = new Form;

        $form->addText('username');

        $form->addPassword('password');
        $form->addPassword('passwordVerify');

        if ($this->requirePassword)
        {
            $form['password']->setRequired()
                ->addRule(Form::MIN_LENGTH, 'The password has to be at least %d characters long', 6);
            $form['passwordVerify']->setRequired('Please enter your password second time for verification.')
                ->addRule(Form::EQUAL, 'Passwords do not match', $form['password']);
        }

        $form->addText('email')
            ->addRule(Form::EMAIL, 'E-mail format is incorrect.')
            ->setRequired();

        $form->addText('realname');

        if ($this->adminMode) { // admin access to particular changes

            $form->addSelect('role_id')
                ->setItems($this->userModel->getRolePairs())
                ->setDefaultValue(4);
            $form['username']
                ->setRequired();

        } else { // user access only

            $form['username']
                ->setDisabled();

        }

        if ($this->userId)
            $form->addHidden('userId')
                ->setValue($this->userId);

        $form->addRadioList('avatar_id', 'Avatar', $this->avatar->getAsArray('filename'));

        if ($this->userRow)
            $form->setDefaults($this->userRow);

        $form->addSubmit('save');

        $form->onSuccess[] = $this->processForm;

        return $form;
    }

    public function render()
    {
        $this->template->adminMode = $this->adminMode;
        $this->template->userId = $this->userId;
        $this->template->userRow = $this->userRow;
        $this->template->avatarDir = $this->avatar->getDir();
        $this->template->setFile(__DIR__ . '/UserProfile.latte');
        $this->template->render();
    }

    public function processForm(Form $form)
    {
        $values = $form->getValues();
        if (isset($values['email']))
            $values['username'] = $this->userRow->username;

        if (isset($values['email']))
            if (!$this->userModel->isUniqueColumn('email', $values['email'], $this->userId))
                $this->onDuplicateEmail($values);

        if (isset($values['username']))
            if (!$this->userModel->isUniqueColumn('username', $values['username'], $this->userId))
                $this->onDuplicateUsername($values);

        if ($this->userId) // is user id set? update user
        {
            $insertData = array(
                'password' => $values['password'],
                'email' => $values['email'],
                'realname' => $values['realname'],
                'avatar_id' => $values['avatar_id'],
            );
            unset($values['password'],$values['passwordVerify']);

            if ($this->adminMode)
            {
                $insertData['username'] = $values['username'];
                $insertData['role_id'] = $values['role_id'];
            }

            $result = $this->userModel->updateById($values['userId'],$insertData);

            if ($result == true)
            {
                if ($this->userId == $this->user->getId()) // update records in current user identity
                    $this->updateCurrentIdentity($values);

                $this->onSuccessEdit($values);
            }

        } else { // user id not set - insert user

            $insertData = array(
                'password' => $values['password'],
                'email' => $values['email'],
                'realname' => $values['realname'],
                'avatar_id' => $values['avatar_id'],
            );
            unset($values['password'],$values['passwordVerify']);

            if ($this->adminMode)
            {
                $insertData['username'] = $values['username'];
                $insertData['role_id'] = $values['role_id'];
            }

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

    public function setDefaultSignals()
    {
        $this->onDuplicateEmail[] = function($values) {
            $this->getPresenter()->flashMessage('Sorry, the e-mail '.$values['email'].' is already registered. Is it you?', 'error');
            $this->getPresenter()->redirect(':Front:Default:');
        };

        $this->onDuplicateUsername[] = function($values) {
            $this->getPresenter()->flashMessage('Sorry, the username '.$values['username'].' is already registered. Is it you?', 'error');
            $this->getPresenter()->redirect(':Front:Default:');
        };

        $this->onAccessDenied[] = function($values) {
            $this->getPresenter()->flashMessage('Sorry, not enough permissions to edit this user '.$values['username'].'.', 'error');
            $this->getPresenter()->redirect(':Front:Default:');
        };

        $this->onNotFound[] = function() {
            $this->getPresenter()->flashMessage('Sorry, this user was not found.', 'error');
            $this->getPresenter()->redirect(':Front:Default:');
        };

        $this->onSuccessAdd[] = function($values) {
            $this->getPresenter()->flashMessage('The user '.$values['username'].' has been successfully added.', 'success');
            $this->getPresenter()->redirect(':Front:Default:');
        };

        $this->onFail[] = function($values) {
            $this->getPresenter()->flashMessage('Error while adding or editing user '.$values['username'].'.', 'error');
            $this->getPresenter()->redirect(':Front:Default:');
        };

        $this->onSuccessEdit[] = function($values) {
            $this->getPresenter()->flashMessage('The user '.$values['username'].' changes has been saved.', 'success');
            $this->getPresenter()->redirect(':Front:Default:');
        };

        $this->onNoChange[] = function($values) {
            $this->getPresenter()->flashMessage('There was no change to user '.$values['username'].'.', 'info');
            $this->getPresenter()->redirect(':Front:Default:');
        };

        return $this;
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

    private function updateCurrentIdentity($values)
    {
        foreach($values as $key => $value)
            $this->user->identity->$key = $value;
    }

}

interface IUserProfileFactory
{
    /** @return UserProfile */
    function create();
}