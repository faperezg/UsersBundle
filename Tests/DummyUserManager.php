<?php
	namespace FAPerezG\UsersBundle\Tests;

	use Doctrine\Common\Persistence\ObjectManager;
	use FAPerezG\UsersBundle\Doctrine\UserManager;
	use FAPerezG\UsersBundle\Entity\User;
	use FOS\UserBundle\Model\UserInterface;
	use FOS\UserBundle\Util\CanonicalizerInterface;
	use Symfony\Component\DependencyInjection\ContainerInterface;
	use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

	class DummyUserManager extends UserManager {
		/** @var UserInterface */
		private $user;

		public function __construct (EncoderFactoryInterface $encoderFactory, CanonicalizerInterface $emailCanonicalizer, ObjectManager $om, $class) {
			parent::__construct ($encoderFactory, $emailCanonicalizer, $om, $class);
			$this->user = new DummyUser ();
		}

		public function createUser () {
			return new DummyUser ();
		}

		public function deleteUser (UserInterface $user) {
			$this->user = null;
		}

		public function findUserBy (array $criteria) {
			return $this->user;
		}

		public function findUserByEmail ($email) {
			return $this->user;
		}

		public function findUsers () {
			return [$this->user];
		}

		public function getClass () {
			return UserInterface::class;
		}

		public function reloadUser (UserInterface $user) {
		}

		public function updatePassword (UserInterface $user) {
			if (0 !== strlen ($password = $user->getPlainPassword ())) {
				$encoder = $this->getEncoder ($user);
				$user->setPassword ($encoder->encodePassword ($password, null));
				$user->eraseCredentials ();
			}
		}

		public function updateUser (UserInterface $user) {
			$this->updateCanonicalFields ($user);
			$this->updatePassword ($user);
			$this->user = $user;
		}

		public static function getInstance (ContainerInterface $container) {
			/** @var EncoderFactoryInterface $encoderFactory */
			$encoderFactory = $container->get ('security.encoder_factory');
			/** @var CanonicalizerInterface $emailCanonicalizer */
			$emailCanonicalizer = $container->get ('fos_user.util.email_canonicalizer');
			/** @var ObjectManager $om */
			$om = $container->get ('fos_user.entity_manager');
			return new DummyUserManager ($encoderFactory, $emailCanonicalizer, $om, User::class);
		}
	}