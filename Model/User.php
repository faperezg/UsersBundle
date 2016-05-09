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

		public function serialize () {
			return serialize (array (
				$this->password,
				$this->expired,
				$this->locked,
				$this->credentialsExpired,
				$this->enabled,
				$this->id,
				$this->expiresAt,
				$this->credentialsExpireAt,
				$this->email,
				$this->emailCanonical,
			));
		}

		public function unserialize ($serialized) {
			$data = unserialize ($serialized);
			// add a few extra elements in the array to ensure that we have enough keys when unserializing
			// older data which does not include all properties.
			$data = array_merge ($data, array_fill (0, 2, null));

			list(
				$this->password,
				$this->expired,
				$this->locked,
				$this->credentialsExpired,
				$this->enabled,
				$this->id,
				$this->expiresAt,
				$this->credentialsExpireAt,
				$this->email,
				$this->emailCanonical
				) = $data;
		}
	}