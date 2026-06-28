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
class RouterFactory
{
	use Nette\SmartObject;

	public function create(): Nette\Routing\Router
	{
		$router = new RouteList();

		$router->addRoute('index.php', 'Front:Default:default', Route::ONE_WAY);

		$router[] = $adminRouter = new RouteList('Admin');

		$localeDef = '[<locale=cs cs|en>/]';

		$adminRouter->addRoute($localeDef.'admin/<presenter>/<action>[/<id>]', array(
			'presenter' => 'Default',
			'action' => 'default',
			'id' => NULL,
		));

		$router[] = $frontRouter = new RouteList('Front');

		$frontRouter->addRoute($localeDef.'<presenter>/<action>[/<id>]', array(
			'presenter' => array(
				Route::VALUE => 'Default',
				Route::PATTERN => '[^(s|game)][a-z][a-z0-9.-]*',
			),
			'action' => 'default',
			'id' => NULL,
		));

		$frontRouter->addRoute($localeDef.'game/<presenter>/<action>[/<id>]', array(
			'module' => 'Game',
			'presenter' => array(
				Route::VALUE => 'Default',
			),
			'action' => 'default',
			'id' => NULL,
		));


		return $router;
	}

}
