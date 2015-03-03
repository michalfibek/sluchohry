<?php
namespace App\Presenters;

use Nette,
	App\Model;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{
	/** @var Nette\Database\Context */
    private $database;

    /**
     * Sign-in form factory.
     * @return Nette\Application\UI\Form
     */
    protected function createComponentSignInForm()
    {
        $form = new Nette\Application\UI\Form;
        $form->addText('username', 'Username:')
            ->setRequired('Please enter your username.');

        $form->addPassword('password', 'Password:')
            ->setRequired('Please enter your password.');

        $form->addCheckbox('remember', 'Keep me signed in');

        $form->addSubmit('send', 'Sign in');

        // call method signInFormSucceeded() on success
        $form->onSuccess[] = array($this, 'signInFormSucceeded');
        return $form;
    }


    public function signInFormSucceeded($form, $values)
    {
        if ($values->remember) {
            $this->getUser()->setExpiration('14 days', FALSE);
        } else {
            $this->getUser()->setExpiration('20 minutes', TRUE);
        }

        try {
            $this->getUser()->login($values->username, $values->password);
            $this->redirect('Homepage:');

        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }


    public function actionSignOut()
    {
        $this->getUser()->logout();
        $this->flashMessage('You have been signed out.');
        $this->redirect('Homepage:');
    }

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

	public function renderDefault()
	{
		// $this->template->posts = $this->database->table('posts')
		// 	->order('created_at DESC')
		// 	->limit(5);
	}

}
