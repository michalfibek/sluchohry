<?php
namespace App\Module\Front\Presenters;

use Nette,
	App\Model;
use Tracy\Debugger;

/**
 * Homepage presenter.
 */
class DefaultPresenter extends \App\Module\Base\Presenters\BasePresenter
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
		$form->setTranslator(
			$this->translator->domain('front.auth.loginForm')
		);
		$form->addText('username', 'username')
			->setRequired('requiredUsername');

		$form->addPassword('password', 'password')
			->setRequired('requiredPassword');

		$form->addCheckbox('remember', 'remember');

		$form->addSubmit('send', 'loginButton');

		$form->onSuccess[] = array($this, 'processLoginForm');
		return $form;
	}

	public function processLoginForm($form, $values)
	{
		if ($values->remember) {
			$this->getUser()->setExpiration('14 days', FALSE);
		} else {
			$this->getUser()->setExpiration('60 minutes', TRUE);
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
