<?php
	namespace FAPerezG\UsersBundle\Model;

	use FOS\UserBundle\Model\UserInterface;
	use FOS\UserBundle\Model\UserManager as BaseUserManager;
	use FOS\UserBundle\Util\CanonicalizerInterface;
	use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

	abstract class UserManager extends BaseUserManager {
		public function __construct (EncoderFactoryInterface $encoderFactory, CanonicalizerInterface $emailCanonicalizer) {
			parent::__construct ($encoderFactory, $emailCanonicalizer, $emailCanonicalizer);
		}

		public function findUserByUsername ($username) {
			return $this->findUserByEmail ($username);
		}

		public function findUserByUsernameOrEmail ($usernameOrEmail) {
			return $this->findUserByEmail ($usernameOrEmail);
		}

		public function updateCanonicalFields (UserInterface $user) {
			$user->setEmailCanonical ($this->canonicalizeEmail ($user->getEmail ()));
		}

		protected function canonicalizeUsername ($username) {
			return $this->canonicalizeEmail ($username);
		}
	}