<?php
namespace App\Presenters;

use Nette,
	App\Model;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
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
	protected function getSongTimeFormat($inputTime, $precision)
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
