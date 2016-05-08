<?php
	namespace FAPerezG\UsersBundle\Model;

	use FOS\UserBundle\Model\User as BaseUser;

	abstract class User extends BaseUser {
		protected $id;

		protected $fullName;

		public function __construct () {
			parent::__construct ();
			$this->username          = $this->email;
			$this->usernameCanonical = $this->emailCanonical;
			$this->addRole (User::ROLE_DEFAULT);
		}

		public function getFullName () {
			return $this->fullName;
		}

		public function getUsername () {
			return $this->email;
		}

		public function getUsernameCanonical () {
			return $this->emailCanonical;
		}

		public function setFullName ($fullName) {
			$this->fullName = $fullName;
			return $this;
		}

		public function setUsername ($username) {
			return $this;
		}

		public function setUsernameCanonical ($usernameCanonical) {
			return $this;
		}

		public function __toString () {
			return (string) $this->getEmail ();
		}
	}