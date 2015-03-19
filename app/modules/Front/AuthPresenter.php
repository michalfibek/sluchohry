<?php
namespace App\Module\Front\Presenters;

use Nette,
	App\Model;
use Tracy\Debugger;

/**
 * Homepage presenter.
 */
class AuthPresenter extends \App\Module\Base\Presenters\BasePresenter
{
    /** @persistent */
    public $backlink;

    /**
     * Sign-in form factory.
     * @return Nette\Application\UI\Form
     */
    protected function createComponentLoginForm()
    {
        $form = new Nette\Application\UI\Form;
        $form->addText('username', 'Username:')
            ->setRequired('Please enter your username.');

        $form->addPassword('password', 'Password:')
            ->setRequired('Please enter your password.');

        $form->addCheckbox('remember', 'Keep me logged in');

        $form->addSubmit('send', 'Login');

        $form->onSuccess[] = array($this, 'processLoginForm');
        return $form;
    }


    public function processLoginForm($form, $values)
    {
        if ($values->remember) {
            $this->getUser()->setExpiration('14 days', FALSE);
        } else {
            $this->getUser()->setExpiration('20 minutes', TRUE);
        }

        try {
            $this->getUser()->login($values->username, $values->password);
            $this->restoreRequest($this->backlink);
            $this->redirect(':Front:Default:default');

        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }

    public function actionLogout()
    {
        $this->getUser()->logout();
        $this->flashMessage('You have been logged out.');
        $this->redirect('Default:');
    }

	public function renderDefault()
	{
		// $this->template->posts = $this->database->table('posts')
		// 	->order('created_at DESC')
		// 	->limit(5);
	}

}
