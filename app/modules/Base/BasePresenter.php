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

	/** @inject @var \App\Model\Avatar */
	public $avatar;


	public $onStartup = array();

	protected function startup()
	{
		parent::startup();

		setlocale(LC_ALL, 'cs_CZ.UTF-8');

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

//				Debugger::barDump($this->getAction(), 'action');
//				Debugger::barDump($resource, 'resource');
//				Debugger::barDump($privilege, 'privilege');

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
		if ($this->getHttpRequest()->getUrl()->getHost() === ('sluchohry.cz' || 'dp.sluchohry.cz'))
			$this->template->devServer = false;
		else
			$this->template->devServer = true;


		$this->template->avatarDir = $this->avatar->getDir();

		if ($this->user->isLoggedIn()) {
			$this->template->userAvatar = $this->avatar->getById($this->user->identity->avatar_id)->filename;
		} else {
			$this->template->userAvatar = $this->avatar->getDefault()->filename;
		}
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

		if ($minutes < 10 && $minutes != 0)
			$minutes = '0'.$minutes;
		elseif ($minutes == 0)
			$minutes = '00';

		if ($seconds < 10 && $seconds != 0)
			$seconds = '0'.$seconds;
		elseif ($seconds == 0)
			$seconds = '00';

		$milliseconds = ($milliseconds == 0) ? '00' : $milliseconds;

		$returnTime = $minutes;
		if ($precision == 'seconds' || 'milliseconds') $returnTime .= ':'.$seconds;
		if ($precision == 'milliseconds') $returnTime .= ':'.$milliseconds;

		return (string)$returnTime;
	}

	public function getModulePrefix()
	{
		$pos = strrpos($this->name, ':');
		if (is_int($pos)) {
			return explode(':', $this->getPresenter()->getName())[0];
		}

		return '';
	}

}
