<?php
namespace App\Components;

use App,
    Nette,
    Kdyby,
    Nette\Application\UI,
    Nette\Application\UI\Form;
use Tracy\Debugger;


class UserProfile extends UI\Control
{
    /** @var App\Model\User */
    private $userModel;

    /** @var App\Model\Group */
    private $groupModel;

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
    public $onEditFail;

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

    /** @var array */
    public $onReturnAction; // \Kdyby\Events workaround to fix event priority problem https://github.com/Kdyby/Events/pull/66

    /** @var array */
    public $onFailAction; // \Kdyby\Events workaround to fix event priority problem

    /** @var App\Model\Avatar */
    private $avatar;

    /** @var Kdyby\Translation\Translator */
    private $translator;

    public function __construct(App\Model\User $userModel, App\Model\Group $groupModel, Nette\Security\IAuthorizator $acl, Nette\Security\User $user, App\Model\Avatar $avatar, Kdyby\Translation\Translator $translator)
    {
        parent::__construct();
        $this->userModel = $userModel;
        $this->groupModel = $groupModel;
        $this->acl = $acl;
        $this->user = $user;
        $this->userId = null;
        $this->avatar = $avatar;
        $this->translator = $translator;

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

            $form->addCheckboxList('group_id')
                ->setItems($this->groupModel->getGroupAsArrayWithId('name'));
//                ->setDefaultValue(4);
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

        if ($this->userRow) {
            $form->setDefaults($this->userRow);
            $form['group_id']->setDefaultValue($this->userModel->getUserGroups($this->userId)->fetchPairs(NULL, 'group_id'));
        }

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

//        if (isset($values['username']) && $this->userRow->username) // what? don't know what the hell this meant
//            $values['username'] = $this->userRow->username;

        if (!$values['avatar_id'])
            $values['avatar_id'] = 1; // set default avatar

        if (isset($values['email']))
            if (!$this->userModel->isUniqueColumn('email', $values['email'], $this->userId))
            {
                $this->onDuplicateEmail($values);
                $this->onFailAction();
            }

        if (isset($values['username']))
            if (!$this->userModel->isUniqueColumn('username', $values['username'], $this->userId))
            {
                $this->onDuplicateUsername($values);
                $this->onFailAction();
            }

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
                $insertData['group_id'] = $values['group_id'];
            }

            $result = $this->userModel->updateById($values['userId'],$insertData);

            if ($result == true)
            {
                $this->onSuccessEdit($values);
                $this->onReturnAction();
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
                $insertData['group_id'] = $values['group_id'];
            }

            $result = $this->userModel->insert($insertData);

            if ($result == true)
            {
                $this->onSuccessAdd($values);
                $this->onReturnAction();
            }
        }

        if ($result == 0)
        {
            $this->onNoChange($values);
            $this->onReturnAction();
        }
        else
        {
            $this->onEditFail($values);
            $this->onFailAction();
        }

        return $this;

    }

    public function setDefaultSignals()
    {
        $this->onDuplicateEmail[] = function($values) {
            $msg = $this->translator->translate('front.user.flash.duplicateEmail', NULL, array('email' => $values['email']));
            $this->getPresenter()->flashMessage($msg, 'error');
        };

        $this->onDuplicateUsername[] = function($values) {
            $msg = $this->translator->translate('front.user.flash.duplicateUsername', NULL, array('username' => $values['username']));
            $this->getPresenter()->flashMessage($msg, 'error');
        };

        $this->onAccessDenied[] = function($values) {
            $msg = $this->translator->translate('front.user.flash.accessDenied', NULL, array('username' => $values['username']));
            $this->getPresenter()->flashMessage($msg, 'error');
        };

        $this->onNotFound[] = function() {
            $msg = $this->translator->translate('front.user.flash.notFound');
            $this->getPresenter()->flashMessage($msg, 'error');
        };

        $this->onSuccessAdd[] = function($values) {
            $msg = $this->translator->translate('front.user.flash.successAdd', NULL, array('username' => $values['username']));
            $this->getPresenter()->flashMessage($msg, 'success');
        };

        $this->onSuccessEdit[] = function($values) {

            if ($this->userId == $this->user->getId()) {
                $this->updateCurrentIdentity($values); // update records in current user identity
                $msg = $this->translator->translate('front.user.flash.successEditPersonal');
            } else {
                $msg = $this->translator->translate('front.user.flash.successEdit', NULL, array('username' => $values['username']));
            }

            $this->getPresenter()->flashMessage($msg, 'success');
        };

        $this->onEditFail[] = function($values) {
            $msg = $this->translator->translate('front.user.flash.editFail', NULL, array('username' => $values['username']));
            $this->getPresenter()->flashMessage($msg, 'error');
        };

        $this->onNoChange[] = function($values) {

            if ($this->userId == $this->user->getId()) {
                $this->updateCurrentIdentity($values); // update records in current user identity
                $msg = $this->translator->translate('front.user.flash.noChangePersonal');
            } else {
                $msg = $this->translator->translate('front.user.flash.noChange', NULL, array('username' => $values['username']));
            }

            $this->getPresenter()->flashMessage($msg, 'info');
        };

        return $this;
    }

    public function edit($userId) {

        $this->userId = $userId;

        if ($userRow = $this->userModel->getById($this->userId)) {

            if (!$this->acl->isChildRole($this->userModel->getUserRoles($this->userId), $this->user->roles, true)) {
                $this->onAccessDenied($userRow);
                $this->onReturnAction();
            }

            $this->userRow = $userRow;

        } else {
            $this->onUserNotFound();
            $this->onReturnAction();
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