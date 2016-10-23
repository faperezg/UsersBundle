<?php
	namespace FAPerezG\UsersBundle\Tests\EventListener;

	use FAPerezG\UsersBundle\EventListener\LocaleListener;
	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\HttpFoundation\Session\Session;
	use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
	use Symfony\Component\HttpKernel\Event\GetResponseEvent;
	use Symfony\Component\HttpKernel\HttpKernelInterface;

	class LocaleListenerTest extends \PHPUnit_Framework_TestCase {
		private $defaultLocale = 'es';
		/** @var HttpKernelInterface */
		private $kernel;

		private function createRequest (array $sessionAttributes = null) {
			$session = new Session (new MockArraySessionStorage ());
			$session->setName ('test_session');
			if (($sessionAttributes) && (count ($sessionAttributes) > 0)) {
				foreach ($sessionAttributes as $name => $value) {
					$session->set ($name, $value);
				}
			}

			$request = Request::create ('/', 'GET', [], ['test_session' => 'test value']);
			$request->setSession ($session);
			return $request;
		}

		protected function setUp () {
			gc_enable ();
			parent::setUp ();
			$this->kernel = $this->createMock ('\Symfony\Component\HttpKernel\HttpKernelInterface');
		}

		protected function tearDown () {
			unset ($this->kernel);
			parent::tearDown ();
			gc_collect_cycles ();
		}

		public function testOnKernelRequestWithNoSessionParameterOrRoutingParameter () {
			$expectedLocale = $this->defaultLocale;
			$request        = $this->createRequest ();
			$event          = new GetResponseEvent ($this->kernel, $request, HttpKernelInterface::MASTER_REQUEST);
			$listener       = new LocaleListener ();
			$listener->onKernelRequest ($event);
			$this->assertEquals ($expectedLocale, $request->getLocale (), 'Locales do not match');
			unset ($listener, $event, $request, $expectedLocale);
		}

		public function testOnKernelRequestWithSessionParameter () {
			$expectedLocale = 'en';
			$request        = $this->createRequest (['_locale' => $expectedLocale]);
			$event          = new GetResponseEvent ($this->kernel, $request, HttpKernelInterface::MASTER_REQUEST);
			$listener       = new LocaleListener ();
			$listener->onKernelRequest ($event);
			$this->assertEquals ($expectedLocale, $request->getLocale (), 'Locales do not match');
			unset ($listener, $event, $request, $expectedLocale);
		}

		public function testOnKernelRequestWithRoutingParameter () {
			$expectedLocale = 'en';
			$request        = $this->createRequest ();
			$request->attributes->set ('_locale', $expectedLocale);
			$event    = new GetResponseEvent ($this->kernel, $request, HttpKernelInterface::MASTER_REQUEST);
			$listener = new LocaleListener ();
			$listener->onKernelRequest ($event);
			$this->assertEquals ($expectedLocale, $request->getLocale (), 'Locales do not match');
			unset ($listener, $event, $request, $expectedLocale);
		}
	}