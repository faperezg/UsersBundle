<?php

	namespace FAPerezG\UsersBundle\Controller;

	use Symfony\Bundle\FrameworkBundle\Controller\Controller;

	class UsersController extends Controller {
		public function indexAction ($name) {
			return $this->render ('', array ('name' => $name));
		}
	}
