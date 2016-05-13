<?php
	namespace FAPerezG\UsersBundle\Controller;

	use FOS\UserBundle\Controller\RegistrationController as BaseRegistrationController;

	class RegistrationController extends BaseRegistrationController {
		/**
		 * @param string $action
		 * @param string $value
		 * @param string $translationDomain
		 */
		protected function setFlash ($action, $value, $translationDomain = 'FOSUserBundle') {
			$translator = $this->container->get ('translator');
			$locale = $this->container->get ('request_stack')->getCurrentRequest ()->getLocale ();
			$this->container->get ('session')->getFlashBag ()->set ($action, $translator->trans ($value, [], $translationDomain, $locale));
		}
	}