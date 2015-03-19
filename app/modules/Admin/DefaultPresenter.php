<?php

namespace App\Module\Admin\Presenters;

use Nette,
	App\Model,
	Nette\Application\UI\Form;
use Tracy\Debugger;


/**
 * Sign in/out presenters.
 */
class DefaultPresenter extends \App\Module\Base\Presenters\BasePresenter
{
	/** @var Nette\Database\Context */
	private $database;
	private $songList;
	private $song;
	private $songMarkers;
	private $genreList;
	private $songBaseDir;
	private $songDefaultFormat;

	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}

	protected function startup()
	{
		parent::startup();

		// TODO autorizovat korektne uzivatele podle urovne opravneni! + ukladat request pro aktualni stranku a vracet zpet pri prihlaseni
		/* user authorization */
		if ($this->user->isLoggedIn()) {
//			if (!$this->user->isAllowed($this->name, $this->action)) { // check if user is allowed
//				$this->flashMessage("You are not allowed for this module.", "error");
//				$this->redirect("Homepage");
//			}
		} else {
//		} else if ($this->action != "login") {
//			if ($this->action != "default") {
//				if ($this->user->getLogoutReason() === User::INACTIVITY) {
//					$this->flashMessage("You have been logged out due to inactivity.");
//				} else {
//					$this->flashMessage("You are not logged.", "error");
//				}
//			}
			$this->redirect(":Front:Default:");
		}
	}

}
