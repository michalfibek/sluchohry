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

    /** @inject @var Components\IUserProfileFactory */
    public $userProfile;

    /** @inject @var Model\EventListeners\UserListener */
    public $logger;

    /**
     * List all users
     *
     */
    public function actionDefault()
    {
        $this['userProfile']
            ->edit($this->user->getId());
    }

    public function	renderDefault()
    {

    }

    /**
     * @return Form
     */
    protected function createComponentUserProfile()
    {
        $form = $this->userProfile->create();
        $form->setDefaultSignals();
        $form->onSuccessAdd[] = function ($values) {
            $this->logger->onUserProfileSuccessAdd($this->user, $values);
        };
        $form->onSuccessEdit[] = function ($values) {
            $this->logger->onUserProfileSuccessEdit($this->user, $values);
        };
        $form->onReturnAction[] = function() {
            $this->redirect(':Front:Default:');
        };
        $form->onFailAction[] = function() {
            $this->redirect('this');
        };

        return $form;
    }

}
