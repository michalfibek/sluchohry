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
        $form->setTranslator($this->translator);
        $form->addText('username', 'front.auth.loginForm.username')
            ->setRequired('front.auth.loginForm.username');

        $form->addPassword('password', 'front.auth.loginForm.password')
            ->setRequired('front.auth.loginForm.requiredPassword');

        $form->addCheckbox('remember', 'front.auth.loginForm.remember');

        $form->addSubmit('send', 'front.auth.loginForm.loginButton');

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
        $this->flashMessage('front.auth.flash.logout');
        $this->redirect('Default:');
    }

	public function renderDefault()
	{

	}

}
