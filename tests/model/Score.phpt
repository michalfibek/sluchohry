<?php

namespace Test;

use Nette,
	App,
	App\Model\Score,
	Tester,
	Tester\Assert;

$container = require __DIR__ . '/../bootstrap.php';


class ScoreTest extends Tester\TestCase
{
	private $container;

	/** @var Score */
	private $scoreModel;

	/** @var Game */
	private $gameModel;


	function __construct(Nette\DI\Container $container)
	{
		$this->container = $container;
//		$dbConnection = $this->container->getService('nette.database.default');
//		$db = new Nette\Database\Context($dbConnection);
//		$this->scoreModel = new Score($db);
		$this->scoreModel = $this->container->getByType('App\Model\Score');
		$this->gameModel = $this->container->getByType('App\Model\Game');
	}

	function setUp()
	{

	}

	protected function tearDown()
	{
		echo "\n";
	}

	public function getGameEndArgs()
	{
		return array(
//			array(
//				array(
//					'gameName' => 'pexeso',
//					'difficulty' => 1,
//					'songList' => '1,5,20,3,8,5',
//					'time' => 2000*1000,
//					'steps' => 38,
//				),
//			),
//			array(
//				array(
//					'gameName' => 'pexeso',
//					'difficulty' => 1,
//					'songList' => '1,5,20,3,18,51',
//					'time' => 2*1000,
//					'steps' => 12
//				)
//			),
//			array(
//				array(
//					'gameName' => 'pexeso',
//					'difficulty' => 1,
//					'songList' => '11,5,20,3,8,5,20,30,1,8',
//					'time' => 20*1000,
//					'steps' => 150
//				)
//			),
			array(
				array(
					'gameName' => 'melodicCubes',
					'difficulty' => 1,
					'cubeCount' => '9',
					'time' => 2*1000,
					'steps' => 1
				)
			),
			array(
				array(
					'gameName' => 'melodicCubes',
					'difficulty' => 2,
					'cubeCount' => '6',
					'time' => 2*1000,
					'steps' => 12
				)
			),
			array(
				array(
					'gameName' => 'melodicCubes',
					'difficulty' => 3,
					'cubeCount' => '9',
					'time' => 21*1000,
					'steps' => 150
				)
			),
		);
	}

	/**
	 * @dataProvider getGameEndArgs
	 */
	function testProcessGameEndResult($result)
	{
		$gameId = $this->gameModel->getByName($result['gameName'])->id;
		$result = $this->scoreModel->processGameEndResult(1, $gameId, $result);

		echo $result['score'];

//		Assert::type('array', $result);

//		Assert::contains(array('score', 'personalRecord', 'gameRecord'), $score);
//		Assert::contains('score', $score);
//		Assert::contains('personalRecord', $score);
//		Assert::contains('gameRecord', $score);

	}

}


$test = new ScoreTest($container);
$test->run();
