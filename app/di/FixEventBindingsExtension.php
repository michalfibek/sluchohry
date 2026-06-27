<?php

namespace App\DI;

use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\ServiceDefinition;

/**
 * Kdyby\Events\DI\EventsExtension::autowireEvents() blindly binds every public
 * "on*" property on every service to its event manager. For our own classes
 * (BasePresenter::onGameStart, UserProfile::onSuccessAdd, ...) that's exactly
 * what we want. But it also catches framework-internal observability hooks
 * like Nette\Http\Session::$onStart/$onBeforeWrite (added in nette/http 3.1)
 * and Nette\Database\Connection::$onQuery — services our own dependency graph
 * legitimately needs to construct (security.user -> session, authenticator ->
 * database), which turns those bindings into a real DI circular dependency
 * (events.manager -> logger -> security.user -> ... -> events.manager again).
 *
 * The only core-framework binding we actually rely on is
 * Nette\Security\User::onLoggedIn/onLoggedOut (see UserListener::getSubscribedEvents()).
 * Strip every other auto-bound "on*" property on Nette\* services.
 *
 * Separately: EventsExtension::validateSubscribers() compares
 * `$stt->getEntity() !== 'addEventSubscriber'` expecting a bare string, but on
 * this nette/di version a setup added via addSetup('addEventSubscriber', ...)
 * normalizes to ['self' reference, 'addEventSubscriber'] (an array), so the
 * comparison is always true and the addEventSubscriber(@logger) call leaks
 * into the "optimized" LazyEventManager's setup — which is exactly the
 * `events.manager -> logger -> security.user -> events.manager` cycle, and is
 * redundant anyway since LazyEventManager already gets its listener map
 * directly. Strip leaked addEventSubscriber setup calls from events.manager.
 */
class FixEventBindingsExtension extends CompilerExtension
{
	private const KeptBindings = [
		'$onLoggedIn',
		'$onLoggedOut',
	];

	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		$manager = $builder->getDefinition('events.manager');
		$manager->setSetup(array_filter($manager->getSetup(), function ($statement) {
			$entity = $statement->getEntity();
			$method = is_array($entity) ? end($entity) : $entity;
			return $method !== 'addEventSubscriber';
		}));

		foreach ($builder->getDefinitions() as $definition) {
			if (!$definition instanceof ServiceDefinition) {
				continue;
			}

			$type = $definition->getType();
			if ($type === null || strncmp($type, 'Nette\\', 6) !== 0) {
				continue;
			}

			$definition->setSetup(array_filter($definition->getSetup(), function ($statement) {
				$entity = $statement->getEntity();
				$property = is_array($entity) ? end($entity) : $entity;
				return !is_string($property)
					|| $property[0] !== '$'
					|| in_array($property, self::KeptBindings, true);
			}));
		}
	}
}
