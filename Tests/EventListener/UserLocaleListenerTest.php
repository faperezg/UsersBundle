<?php
	namespace FAPerezG\UsersBundle\Tests\EventListener;

	use FAPerezG\UsersBundle\EventListener\UserLocaleListener;
	use FAPerezG\UsersBundle\Tests\DummyUser;
	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\HttpFoundation\Session\Session;
	use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
	use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
	use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

	class UserLocaleListenerTest extends \PHPUnit_Framework_TestCase {
		protected function setUp () {
			gc_enable ();
			parent::setUp ();
		}

		protected function tearDown () {
			parent::tearDown ();
			gc_collect_cycles ();
		}

		public function testOnKernelRequest () {
			$expectedLocale = 'anything';

			$user = new DummyUser ();
			$user->setLocale ($expectedLocale);

			$session = new Session (new MockArraySessionStorage ());

			$request = Request::create ('/', 'GET');

			$authenticationToken = new UsernamePasswordToken ('jack@example.org', '12345678', 'provider');
			$authenticationToken->setUser ($user);

			$event    = new InteractiveLoginEvent ($request, $authenticationToken);
			$listener = new UserLocaleListener ($session);
			$listener->onInteractiveLogin ($event);

			$this->assertTrue ($session->has ('_locale'), 'Session does not have _locale');
			$this->assertEquals ($session->get ('_locale'), $user->getLocale (), 'Locales do not match');

			unset ($listener, $event, $authenticationToken, $request, $session, $user, $expectedLocale);
		}
	}