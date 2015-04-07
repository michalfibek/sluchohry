<?php

namespace App\Module\Front\Presenters;

use Nette,
    App\Model,
    App\Components;
use Tracy\Debugger;


/**
 * User profile
 */
class ProfilePresenter extends \App\Module\Base\Presenters\BasePresenter
{
    /** @inject @var Components\IUserEditFormFactory */
    public $userEditForm;

    /**
     * List all users
     *
     */
    public function actionDefault()
    {
        $this['userEditForm']
            ->edit($this->user->getId());
    }

    public function	renderDefault()
    {
        $this->template->userRow = $this['userEditForm']->getUser();
    }

    /**
     * @return Form
     */
    protected function createComponentUserEditForm()
    {
        $form = $this->userEditForm->create();

        $form->onDuplicateEmail[] = function($values) {
            $this->flashMessage('Sorry, the e-mail '.$values['email'].' is already registered. Is it you?', 'error');
            $this->redirect('Front:Default');
        };

        $form->onDuplicateUsername[] = function($values) {
            $this->flashMessage('Sorry, the username '.$values['username'].' is already registered. Is it you?', 'error');
            $this->redirect('Front:Default');
        };

        $form->onAccessDenied[] = function($values) {
            $this->flashMessage('Sorry, not enough permissions to edit this user '.$values['username'].'.', 'error');
            $this->redirect('Front:Default');
        };

        $form->onNotFound[] = function() {
            $this->flashMessage('Sorry, this user was not found.', 'error');
            $this->redirect('Front:Default');
        };

        $form->onSuccessAdd[] = function($values) {
            $this->flashMessage('The user '.$values['username'].' has been successfully added.', 'success');
            $this->redirect('Front:Default');
        };

        $form->onFail[] = function($values) {
            $this->flashMessage('Error while adding or editing user '.$values['username'].'.', 'error');
            $this->redirect('Front:Default');
        };

        $form->onSuccessEdit[] = function($values) {
            $this->flashMessage('The user '.$values['username'].' changes has been saved.', 'success');
            $this->redirect('Front:Default');
        };

        $form->onNoChange[] = function($values) {
            $this->flashMessage('There was no change to user '.$values['username'].'.', 'info');
            $this->redirect('Front:Default');
        };


        return $form;
    }

}
