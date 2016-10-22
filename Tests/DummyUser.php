<?php
	namespace FAPerezG\UsersBundle\Tests;

	use FAPerezG\UsersBundle\Model\User;

	class DummyUser extends User {
		public function __construct () {
			parent::__construct ();
			$this
				->setEmail ('jack@example.org')
				->setPlainPassword ('12345678')
				->setFullName ('Jack Sparrow')
				->setLocale ('en')
				->setEnabled (true);
		}
	}