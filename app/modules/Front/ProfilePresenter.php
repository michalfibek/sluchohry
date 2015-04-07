<?php

namespace App\Module\Front;

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

    private $userRow;

    /**
     * List all users
     *
     */
    public function actionDefault()
    {

    }
}
