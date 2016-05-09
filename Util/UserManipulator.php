<?php
	namespace FAPerezG\UsersBundle\Util;

	use FAPerezG\UsersBundle\Model\User;
	use FOS\UserBundle\Model\UserInterface;
	use FOS\UserBundle\Model\UserManagerInterface;

	class UserManipulator {
		/**
		 * User manager
		 *
		 * @var UserManagerInterface
		 */
		private $userManager;

		public function __construct (UserManagerInterface $userManager) {
			$this->userManager = $userManager;
		}

		/**
		 * Creates a user and returns it.
		 *
		 * @param string $email
		 * @param string $password
		 * @param string $fullName
		 * @param Boolean $active
		 * @param Boolean $superadmin
		 *
		 * @return \FOS\UserBundle\Model\UserInterface
		 */
		public function create ($email, $password, $fullName, $active, $superadmin) {
			$user = $this->userManager->createUser ();
			if ($user instanceof User) {
				$user->setFullName ($fullName);
			}
			$user->setEmail ($email);
			$user->setPlainPassword ($password);
			$user->setEnabled ((Boolean) $active);
			$user->setSuperAdmin ((Boolean) $superadmin);
			$this->userManager->updateUser ($user);
			return $user;
		}

		/**
		 * Activates the given user.
		 *
		 * @param string $email
		 */
		public function activate ($email) {
			$user = $this->userManager->findUserByEmail ($email);
			if (!$user) {
				throw new \InvalidArgumentException(sprintf ('User identified by "%s" does not exist.', $email));
			}
			$user->setEnabled (true);
			$this->userManager->updateUser ($user);
		}

		/**
		 * Deactivates the given user.
		 *
		 * @param string $email
		 */
		public function deactivate ($email) {
			$user = $this->userManager->findUserByEmail ($email);
			if (!$user) {
				throw new \InvalidArgumentException(sprintf ('User identified by "%s" does not exist.', $email));
			}
			$user->setEnabled (false);
			$this->userManager->updateUser ($user);
		}

		/**
		 * Changes the password for the given user.
		 *
		 * @param string $email
		 * @param string $password
		 */
		public function changePassword ($email, $password) {
			$user = $this->userManager->findUserByEmail ($email);
			if (!$user) {
				throw new \InvalidArgumentException(sprintf ('User identified by "%s" does not exist.', $email));
			}
			$user->setPlainPassword ($password);
			$this->userManager->updateUser ($user);
		}

		/**
		 * Adds role to the given user.
		 *
		 * @param string $email
		 * @param string $role
		 *
		 * @return Boolean true if role was added, false if user already had the role
		 */
		public function setRole ($email, $role) {
			$user = $this->userManager->findUserByEmail ($email);
			if (!$user) {
				throw new \InvalidArgumentException(sprintf ('User identified by "%s" does not exist.', $email));
			}
			if ($user->hasRole ($role)) {
				return false;
			}
			$user->setRoles ([$role]);
			$this->userManager->updateUser ($user);
			return true;
		}

		/**
		 * Removes role from the given user.
		 *
		 * @param string $email
		 * @param string $role
		 *
		 * @return Boolean true if role was removed, false if user didn't have the role
		 */
		public function removeRole ($email, $role) {
			$user = $this->userManager->findUserByEmail ($email);
			if (!$user) {
				throw new \InvalidArgumentException(sprintf ('User identified by "%s" does not exist.', $email));
			}
			if (!$user->hasRole ($role)) {
				return false;
			}
			$user->setRoles ([UserInterface::ROLE_DEFAULT]);
			$this->userManager->updateUser ($user);
			return true;
		}

		/**
		 * Promotes the given user.
		 *
		 * @param string $email
		 */
		public function promote ($email) {
			$user = $this->userManager->findUserByEmail ($email);
			if (!$user) {
				throw new \InvalidArgumentException(sprintf ('User identified by "%s" does not exist.', $email));
			}
			$user->setSuperAdmin (true);
			$this->userManager->updateUser ($user);
		}

		/**
		 * Demotes the given user.
		 *
		 * @param string $email
		 */
		public function demote ($email) {
			$user = $this->userManager->findUserByEmail ($email);
			if (!$user) {
				throw new \InvalidArgumentException(sprintf ('User identified by "%s" does not exist.', $email));
			}
			$user->setSuperAdmin (false);
			$this->userManager->updateUser ($user);
		}
	}