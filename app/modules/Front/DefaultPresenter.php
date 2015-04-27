<?php
namespace App\Module\Front\Presenters;

use Nette,
	App\Model,
	App\Utils\Utils;
use Tracy\Debugger;

/**
 * Homepage presenter.
 */
class DefaultPresenter extends \App\Module\Base\Presenters\BasePresenter
{
	const
		HOMEPAGE_QUOTE_COUNT = 11;

	/** @persistent */
	public $backlink;

	/** @inject @var Model\Score */
	public $score;

	/** @inject @var Model\Game */
	public $game;

	public function actionLogout()
	{
		$this->getUser()->logout();
		$this->flashMessage('front.auth.flash.logout');
		$this->redirect('Default:');
	}

	public function renderDefault()
	{
		if ($this->user->loggedIn)
		{
			$flashId = $this->getParameterId('flash');

			// if there is not a single flash message, annoy user with owl's quotes
			if (!$this->getPresenter()->getFlashSession()->$flashId)
			{
				$randMsg = (string)rand(1,self::HOMEPAGE_QUOTE_COUNT);
				$this->flashMessage('front.game.homepageQuote.msg'.$randMsg);
			}

			$scoreMelodicCubes = $this->score->getAllByUser($this->user->getId(), 1, true);
			$this->template->scoreMelodicCubes = $scoreMelodicCubes;
			$this->template->melodicCubesMaxdiffKey = Utils::arrayMaxWithIndex($scoreMelodicCubes)['i'];

			$scorePexeso = $this->score->getAllByUser($this->user->getId(), 2, true);
			$this->template->scorePexeso = $scorePexeso;
			$this->template->pexesoMaxdiffKey = Utils::arrayMaxWithIndex($scorePexeso)['i'];

			$scoreNoteSteps = $this->score->getAllByUser($this->user->getId(), 3, true);
			$this->template->scoreNoteSteps = $scoreNoteSteps;
			$this->template->noteStepsMaxdiffKey = Utils::arrayMaxWithIndex($scoreNoteSteps)['i'];

			$scoreFaders = $this->score->getAllByUser($this->user->getId(), 4, true);
			$this->template->scoreFaders = $scoreFaders;
			$this->template->fadersMaxdiffKey = Utils::arrayMaxWithIndex($scoreFaders)['i'];

			$this->template->scoreSum = $this->score->getScoreSum($this->user->getId());
		}

	}

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
			$this->getUser()->setExpiration('90 minutes', TRUE);
		}

		try {
			$this->getUser()->login($values->username, $values->password);
			$this->restoreRequest($this->backlink);
			$name = $this->user->identity->realname;
			$this->flashMessage($this->translator->translate('front.auth.flash.login', NULL, array('name' => $name)), 'success');
			$this->redirect(':Front:Default:default');

		} catch (Nette\Security\AuthenticationException $e) {
			$this->flashMessage($e->getMessage(), 'error');
			$form->addError($e->getMessage());
		}
	}

}
