<?php
	namespace FAPerezG\UsersBundle\Doctrine;

	use Doctrine\Common\Persistence\ObjectManager;
	use FAPerezG\UsersBundle\Model\UserManager as BaseUserManager;
	use FOS\UserBundle\Model\UserInterface;
	use FOS\UserBundle\Util\CanonicalizerInterface;
	use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

	class UserManager extends BaseUserManager {
		protected $objectManager;
		protected $class;
		protected $repository;

		public function __construct (EncoderFactoryInterface $encoderFactory, CanonicalizerInterface $emailCanonicalizer, ObjectManager $om, $class) {
			parent::__construct ($encoderFactory, $emailCanonicalizer);
			$this->objectManager = $om;
			$this->repository    = $om->getRepository ($class);
			$metadata    = $om->getClassMetadata ($class);
			$this->class = $metadata->getName ();
		}

		/**
		 * Deletes a user.
		 *
		 * @param UserInterface $user
		 *
		 * @return void
		 */
		public function deleteUser (UserInterface $user) {
			$this->objectManager->remove ($user);
			$this->objectManager->flush ();
		}

		/**
		 * Finds one user by the given criteria.
		 *
		 * @param array $criteria
		 *
		 * @return UserInterface
		 */
		public function findUserBy (array $criteria) {
			return $this->repository->findOneBy ($criteria);
		}

		/**
		 * Returns a collection with all user instances.
		 *
		 * @return \Traversable
		 */
		public function findUsers () {
			return $this->repository->findAll ();
		}

		/**
		 * Returns the user's fully qualified class name.
		 *
		 * @return string
		 */
		public function getClass () {
			return $this->class;
		}

		/**
		 * Reloads a user.
		 *
		 * @param UserInterface $user
		 *
		 * @return void
		 */
		public function reloadUser (UserInterface $user) {
			$this->objectManager->refresh ($user);
		}

		/**
		 * Updates a user.
		 *
		 * @param UserInterface $user
		 *
		 * @param bool $andFlush
		 */
		public function updateUser (UserInterface $user, $andFlush = true) {
			$this->updateCanonicalFields ($user);
			$this->updatePassword ($user);
			$this->objectManager->persist ($user);
			if ($andFlush) {
				$this->objectManager->flush ();
			}
		}
	}