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

	/**  @inject @var \Kdyby\Translation\Translator */
	public $translator;

	/** @inject @var \Nette\Security\IAuthorizator */
	public $acl;

	public $onStartup = array();

	protected function startup()
	{
		parent::startup();

		if (!in_array($this->name, array('Front:Default', 'Base:Error'))) {
			if (!$this->user->isLoggedIn()) {
				if ($this->user->getLogoutReason() === Nette\Security\IUserStorage::INACTIVITY) {
					$this->flashMessage('front.auth.flash.sessionTimeout');
				}

				$this->redirect(':Front:Default:', array(
					'backlink' => $this->storeRequest()
				));

			} else {
				$resource = $this->name;
				$privilege = $this->getAction();

				Debugger::barDump($this->getAction(), 'action');
				Debugger::barDump($resource, 'resource');
				Debugger::barDump($privilege, 'privilege');

				if (!$this->user->isAllowed($resource, $privilege)) {
					$this->flashMessage('front.auth.flash.accessDenied', 'error');
					$this->redirect(':Front:default:');
				}
			}
		}
		$this->onStartup($this);

	}

	protected function beforeRender()
	{
		// debug intention only
		if ($this->getHttpRequest()->getUrl()->getHost() === 'sluchohry.cz')
			$this->template->devServer = false;
		else
			$this->template->devServer = true;
	}

	/**
	 * Enables song duration formatting latte filter.
	 *
	 * @param null $class
	 * @return Nette\Application\UI\ITemplate
     */
	protected function createTemplate($class = NULL)
	{
		$template = parent::createTemplate($class);
		$template->addFilter('songTime', function ($s, $precision = 'seconds') {
			return $this->getSongTimeFormat($s, $precision);
		});
		return $template;
	}

	/**
	 * @param $inputTime int - input time in whole milliseconds
	 * @param $precision string - seconds or milliseconds
	 * @return string - properly formatted time
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
