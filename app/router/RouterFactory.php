<?php

namespace App;

use Nette,
	Nette\Application\Routers\RouteList,
	Nette\Application\Routers\Route,
	Nette\Application\Routers\SimpleRouter;


/**
 * Router factory.
 */
class RouterFactory
{

	/**
	 * @return \Nette\Application\IRouter
	 */
	public static function createRouter()
	{
		$router = new RouteList();

		$router[] = new Route('index.php', 'Front:Default:default', Route::ONE_WAY);

		$router[] = $adminRouter = new RouteList('Admin');

		$adminRouter[] = new Route('[<locale=cs cs|en>/]admin/<presenter>/<action>[/<id>]', array(
			'presenter' => 'Default',
			'action' => 'default',
			'id' => NULL,
		));

		$router[] = $frontRouter = new RouteList('Front');

		$frontRouter[] = new Route('[<locale=cs cs|en>/]/game/<presenter>/<action>[/<id>]', array(
			'module' => 'Game',
			'presenter' => 'Default',
			'action' => 'default',
			'id' => NULL,
		));

		$frontRouter[] = new Route('[<locale=cs cs|en>/]<presenter>/<action>[/<id>]', array(
			'presenter' => 'Default',
			'action' => 'default',
			'id' => NULL,
		));

		return $router;
	}

}
