<?php
	namespace FAPerezG\UsersBundle\Tests\Util;

	use Doctrine\Common\Persistence\ObjectManager;
	use FAPerezG\UsersBundle\Model\UserInterface;
	use FAPerezG\UsersBundle\Tests\DummyUser;
	use FAPerezG\UsersBundle\Tests\DummyUserManager;
	use FAPerezG\UsersBundle\Util\UserManipulator;
	use FOS\UserBundle\Util\Canonicalizer;
	use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;

	class UserManipulatorTest extends \PHPUnit_Framework_TestCase {
		/** @var Canonicalizer */
		private $canonicalizer;
		/** @var DummyUserManager */
		private $userManager;
		/** @var UserManipulator */
		private $userManipulator;

		private function getUserManager (Canonicalizer $canonicalizer) {
			$userClass = 'FAPerezG\UsersBundle\Tests\DummyUser';
			/** @var \PHPUnit_Framework_MockObject_MockObject|\Doctrine\Common\Persistence\Mapping\ClassMetadata $classMetaData */
			$classMetaData = $this->createMock ('Doctrine\Common\Persistence\Mapping\ClassMetadata');
			$classMetaData->expects ($this->any ())
				->method ('getName')
				->will ($this->returnValue ($userClass));
			/** @var \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface $encoderFactory */
			$encoderFactory = $this->createMock ('Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface');
			$encoderFactory->expects ($this->any ())
				->method ('getEncoder')
				->will ($this->returnValue (new BCryptPasswordEncoder (4)));
			/** @var \PHPUnit_Framework_MockObject_MockObject|\Doctrine\Common\Persistence\ObjectRepository $repository */
			$repository = $this->createMock ('Doctrine\Common\Persistence\ObjectRepository');
			/** @var \PHPUnit_Framework_MockObject_MockObject|\Doctrine\Common\Persistence\ObjectManager $objectManager */
			$objectManager = $this->createMock ('Doctrine\Common\Persistence\ObjectManager');
			$objectManager->expects ($this->any ())
				->method ('getRepository')
				->with ($this->equalTo ($userClass))
				->will ($this->returnValue ($repository));
			$objectManager->expects ($this->any ())
				->method ('getClassMetadata')
				->with ($this->equalTo ($userClass))
				->will ($this->returnValue ($classMetaData));
			return new DummyUserManager ($encoderFactory, $canonicalizer, $objectManager, $userClass);
		}

		/**
		 * @param $email
		 *
		 * @return DummyUser
		 */
		protected function loadTestUser ($email) {
			return $this->userManager->findUserByEmail ($email);
		}

		/**
		 * @param $email
		 * @param $active
		 * @param $role
		 *
		 * @return DummyUser
		 */
		protected function saveTestUser ($email, $role, $active = null) {
			$user = $this->userManager->findUserByEmail ($email);
			if ($role) {
				$user->addRole ($role);
			}
			if ($active !== null) {
				$user->setEnabled ($active);
			}
			$this->userManager->updateUser ($user);
		}

		protected function setUp () {
			if (!interface_exists ('Doctrine\Common\Persistence\ObjectManager')) {
				$this->markTestSkipped ('Doctrine Common has to be installed for this test to run.');
			}
			parent::setUp ();
			$this->canonicalizer   = new Canonicalizer ();
			$this->userManager = $this->getUserManager ($this->canonicalizer);
			$this->userManipulator = new UserManipulator ($this->userManager);
		}

		protected function tearDown () {
			unset ($this->userManipulator, $this->canonicalizer, $this->userManager);
			parent::tearDown ();
		}

		public function testCreate () {
			$email      = 'hector@example.org';
			$password   = '12345678';
			$fullName   = 'Héctor Barbossa';
			$locale     = 'en';
			$active     = true;
			$superAdmin = false;
			$user       = $this->userManipulator->create ($email, $password, $fullName, $locale, $active, $superAdmin);
			$this->assertEquals ($email, $user->getEmail (), 'Emails do not match');
			$this->assertEquals ($fullName, $user->getFullName (), 'Full names do not match');
			$this->assertEquals ($locale, $user->getLocale (), 'Locales do not match');
			$this->assertEquals ($active, $user->isEnabled (), 'IsEnabled do not match');
			$this->assertEquals ($superAdmin, $user->isSuperAdmin (), 'IsSuperAdmin do not match');
			$this->assertEquals ($this->canonicalizer->canonicalize ($email), $user->getEmailCanonical (), 'Canonical emails do not match');
			$this->assertNotEmpty ($user->getPassword (), 'Password not created');
			$this->assertNull ($user->getPlainPassword (), 'Plain password not erased');
			unset ($user, $superAdmin, $active, $locale, $fullName, $password, $email);
		}

		public function testActivate () {
			$email      = 'jack@example.org';
			$this->saveTestUser ($email, null, false);
			$user = $this->loadTestUser ($email);
			$this->assertFalse ($user->isEnabled (), 'User should be inactive');
			$this->userManipulator->activate ($email);
			$this->assertTrue ($user->isEnabled (), 'User should be active');
			unset ($user, $email);
		}

		public function testDeactivate () {
			$email = 'jack@example.org';
			$this->saveTestUser ($email, null, true);
			$user = $this->loadTestUser ($email);
			$this->assertTrue ($user->isEnabled (), 'User should be active');
			$this->userManipulator->deactivate ($email);
			$this->assertFalse ($user->isEnabled (), 'User should be inactive');
			unset ($user, $email);
		}

		public function testSetNonAssignedRole () {
			$email = 'jack@example.org';
			$role  = 'ROLE_TEST';
			$user = $this->loadTestUser ($email);
			$this->assertFalse (in_array ($role, $user->getRoles ()), "User should NOT have $role role");
			$result = $this->userManipulator->setRole ($email, $role);
			$this->assertTrue ($result, 'Manipulator did not set role');
			$this->assertTrue (in_array ($role, $user->getRoles ()), "User should have $role role");
			unset ($result, $user, $role, $email);
		}

		public function testSetAssignedRole () {
			$email = 'jack@example.org';
			$role  = 'ROLE_TEST';
			$this->saveTestUser ($email, $role);
			$user  = $this->loadTestUser ($email);
			$this->assertTrue (in_array ($role, $user->getRoles ()), "User should have $role role");
			$result = $this->userManipulator->setRole ($email, $role);
			$this->assertFalse ($result, 'Manipulator did set role');
			$this->assertTrue (in_array ($role, $user->getRoles ()), "User should have $role role");
			unset ($result, $user, $role, $email);
		}

		public function testRemoveAssignedRole () {
			$email = 'jack@example.org';
			$role  = 'ROLE_TEST';
			$this->saveTestUser ($email, $role);
			$user = $this->loadTestUser ($email);
			$this->assertTrue (in_array ($role, $user->getRoles ()), "User should have $role role");
			$result = $this->userManipulator->removeRole ($email, $role);
			$this->assertTrue ($result, 'Manipulator did not remove role');
			$this->assertFalse (in_array ($role, $user->getRoles ()), "User should not have $role role");
			unset ($result, $user, $role, $email);
		}

		public function testRemoveNonAssignedRole () {
			$email = 'jack@example.org';
			$role  = 'ROLE_TEST';
			$user  = $this->loadTestUser ($email);
			$this->assertFalse (in_array ($role, $user->getRoles ()), "User should NOT have $role role");
			$result = $this->userManipulator->removeRole ($email, $role);
			$this->assertFalse ($result, 'Manipulator removed role');
			$this->assertFalse (in_array ($role, $user->getRoles ()), "User should not have $role role");
			unset ($result, $user, $role, $email);
		}

		public function testPromote () {
			$email = 'jack@example.org';
			$user  = $this->loadTestUser ($email);
			$this->assertFalse ($user->isSuperAdmin (), 'User should NOT be super admin');
			$this->userManipulator->promote ($email);
			$this->assertTrue ($user->isSuperAdmin (), 'User should be super admin');
			$this->assertTrue (in_array (DummyUser::ROLE_SUPER_ADMIN, $user->getRoles ()), 'User should have super admin role');
			unset ($user, $email);
		}

		public function testDemote () {
			$email = 'jack@example.org';
			$this->saveTestUser ($email, DummyUser::ROLE_SUPER_ADMIN);
			$user  = $this->loadTestUser ($email);
			$this->assertTrue ($user->isSuperAdmin (), 'User should be super admin');
			$this->userManipulator->demote ($email);
			$this->assertFalse ($user->isSuperAdmin (), 'User should not be super admin');
			$this->assertFalse (in_array (DummyUser::ROLE_SUPER_ADMIN, $user->getRoles ()), 'User should not have super admin role');
			unset ($user, $email);
		}
	}