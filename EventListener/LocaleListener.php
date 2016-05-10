<?php
	namespace FAPerezG\UsersBundle\EventListener;

	use Symfony\Component\EventDispatcher\EventSubscriberInterface;
	use Symfony\Component\HttpKernel\Event\GetResponseEvent;
	use Symfony\Component\HttpKernel\KernelEvents;

	class LocaleListener implements EventSubscriberInterface {
		private $defaultLocale;

		public function __construct ($defaultLocale = 'es') {
			$this->defaultLocale = $defaultLocale;
		}

		public function onKernelRequest (GetResponseEvent $event) {
			$request = $event->getRequest ();
			if (!$request->hasPreviousSession ()) {
				return;
			}
			// try to see if the locale has been set as a session parameter
			if ($request->getSession ()->has ('_locale')) {
				$request->setLocale ($request->getSession ()->get ('_locale'));
			} else if (!$request->attributes->has ('_locale')) {
				// try to see if the locale has been set as a _locale routing parameter
				$request->setLocale ($this->defaultLocale);
			}
		}

		public static function getSubscribedEvents () {
			return array (
				// must be registered after the default Locale listener
				KernelEvents::REQUEST => [
					['onKernelRequest', 15],
				],
			);
		}
	}