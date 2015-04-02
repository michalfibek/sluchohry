<?php

namespace App;

use Nette,
	Nette\Application\Routers\RouteList,
	Nette\Application\Routers\Route,
	Nette\Application\Routers\SimpleRouter;


/**
 * Router factory.
 */
class RouterFactory extends Nette\Object
{
	/** @var bool */
	private $useHttps;
	public function __construct($useHttps)
	{
		$this->useHttps = $useHttps;
	}

	private static function presenter2path($s)
	{
	    $s = strtr($s, ':', '.');
	    $s = preg_replace('#([^.])(?=[A-Z])#', '$1-', $s);
	    $s = strtolower($s);
	    $s = rawurlencode($s);
	    return $s;
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

		$adminRouter[] = new Route('[<locale=cs cs|en>/]admin/<presenter>/<action>[/<id>]', array(
			'presenter' => 'Default',
			'action' => 'default',
			'id' => NULL,
		), $flags);

		$router[] = $frontRouter = new RouteList('Front');

		$frontRouter[] = new Route('[<locale=cs cs|en>/]<presenter>/<action>[/<id>]', array(
			'presenter' => array(
				Route::VALUE => 'Default',
				Route::PATTERN => '[^(s|game)][a-z][a-z0-9.-]*',
			),
			'action' => 'default',
			'id' => NULL,
		), $flags);

		$frontRouter[] = new Route('[<locale=cs cs|en>/]game/<presenter>/<action>[/<id>]', array(
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
