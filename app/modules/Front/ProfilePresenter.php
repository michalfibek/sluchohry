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

        return $form;
    }

}
