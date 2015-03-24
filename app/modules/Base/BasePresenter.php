<?php
namespace App\Module\Base\Presenters;

use Nette,
	App\Model;
use Tracy\Debugger;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	/** @persistent */
	public $locale;

	/** @var \Kdyby\Translation\Translator @inject */
	public $translator;
	public $onStartup = array();

	protected function startup()
	{
		parent::startup();

		if (!in_array($this->name, array('Front:Default', 'Front:Auth'))) {
			if (!$this->user->isLoggedIn()) {
				if ($this->user->getLogoutReason() === Nette\Security\IUserStorage::INACTIVITY) {
					$this->flashMessage('front.auth.flash.sessionTimeout');
				}

				$this->redirect(':Front:Default:', array(
					'backlink' => $this->storeRequest()
				));

			} else {
				if (!$this->user->isAllowed($this->name, $this->action)) {
					$this->flashMessage('front.auth.flash.accessDenied', 'error');
					$this->redirect(':Front:Auth:login');
				}
			}
		}
		$this->onStartup($this);

	}

	protected function createTemplate($class = NULL)
	{
		$template = parent::createTemplate($class);
		$template->addFilter('songTime', function ($s, $precision = 'seconds') {
			return $this->getSongTimeFormat($s, $precision);
		});
		return $template;
	}

	/**
	 * @param $inputTime input time in whole milliseconds
	 * @param $precision seconds or milliseconds
	 * @return string properly formatted time
	 */
	protected function getSongTimeFormat($inputTime, $precision = 'seconds')
	{
		$milliseconds = $inputTime % 1000;
		$inputTime = floor($inputTime / 1000);
		$seconds = $inputTime % 60;
		$inputTime = floor($inputTime / 60);
		$minutes = $inputTime % 60;
		$inputTime = floor($inputTime / 60);

		$minutes = ($minutes == 0) ? '00' : $minutes;
		$seconds = ($seconds == 0) ? '00' : $seconds;
		$milliseconds = ($milliseconds == 0) ? '00' : $milliseconds;

		$returnTime = $minutes;
		if ($precision == 'seconds' || 'milliseconds') $returnTime .= ':'.$seconds;
		if ($precision == 'milliseconds') $returnTime .= ':'.$milliseconds;

		return (string)$returnTime;
	}

}
