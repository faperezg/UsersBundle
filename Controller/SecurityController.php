<?php

	namespace FAPerezG\UsersBundle\Controller;

	use FOS\UserBundle\Controller\SecurityController as BaseSecurityController;

	class SecurityController extends BaseSecurityController {
		protected function renderLogin (array $data) {
			$request = $this->container->get ('request_stack')->getCurrentRequest ();
			$session = $request->getSession ();
			$session->set ('app.last_session_id', $session->getId ());
			$targetPath =  $request->request->get ('_target_path', null) ? : $request->query->get ('redirect', null);
			if ($targetPath) {
				$data ['target_path'] = $targetPath;
			}
			return parent::renderLogin ($data);
		}
	}
