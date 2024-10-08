<?php

namespace App;

use Nette,
	Nette\Application\Routers\RouteList,
	Nette\Application\Routers\Route,
	Nette\Application\Routers\SimpleRouter;
use Tracy\Debugger;


/**
 * Router factory.
 */
class RouterFactory extends Nette\Object
{
	/** @var bool */
	private $useHttps;

	public function __construct(Nette\Http\Request $httpRequest)
	{
		if (in_array($httpRequest->getUrl()->getHost(), array('sluchohry.cz', 'dp.sluchohry.cz', 'sluchohry.no2.cz')))
			$this->useHttps = true;
		else
			$this->useHttps = false;
	}

	/**
	 * @return \Nette\Application\IRouter
	 */
	public function create()
	{
		$flags = $this->useHttps ? Route::SECURED : 0;

		$router = new RouteList();

		$router[] = new Route('index.php', 'Front:Default:default', $flags | Route::ONE_WAY);

		$router[] = $adminRouter = new RouteList('Admin');

		$localeDef = '[<locale=cs cs|en>/]';

		$adminRouter[] = new Route($localeDef.'admin/<presenter>/<action>[/<id>]', array(
			'presenter' => 'Default',
			'action' => 'default',
			'id' => NULL,
		), $flags);

		$router[] = $frontRouter = new RouteList('Front');

		$frontRouter[] = new Route($localeDef.'<presenter>/<action>[/<id>]', array(
			'presenter' => array(
				Route::VALUE => 'Default',
				Route::PATTERN => '[^(s|game)][a-z][a-z0-9.-]*',
			),
			'action' => 'default',
			'id' => NULL,
		), $flags);

		$frontRouter[] = new Route($localeDef.'game/<presenter>/<action>[/<id>]', array(
			'module' => 'Game',
			'presenter' => array(
				Route::VALUE => 'Default',
			),
			'action' => 'default',
			'id' => NULL,
		), $flags);


		return $router;
	}

}
