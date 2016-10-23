<?php
	namespace FAPerezG\UsersBundle\Tests\Doctrine;

	use FAPerezG\UsersBundle\Doctrine\UserManager;
	use FAPerezG\UsersBundle\Model\User;
	use FOS\UserBundle\Util\Canonicalizer;
	use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;

	class UserManagerTest extends \PHPUnit_Framework_TestCase {
		const USER_CLASS = 'FAPerezG\UsersBundle\Tests\DummyUser';
		/** @var UserManager */
		protected $userManager;
		/** @var \Doctrine\Common\Persistence\ObjectManager|\PHPUnit_Framework_MockObject_MockObject */
		protected $om;
		/** @var \Doctrine\Common\Persistence\ObjectRepository|\PHPUnit_Framework_MockObject_MockObject */
		protected $repository;

		/**
		 * @param $encoderFactory
		 * @param $canonicalizer
		 * @param $objectManager
		 * @param $userClass
		 *
		 * @return UserManager
		 */
		protected function createUserManager ($encoderFactory, $canonicalizer, $objectManager, $userClass) {
			return new UserManager ($encoderFactory, $canonicalizer, $objectManager, $userClass);
		}

		/**
		 * @return User
		 */
		protected function getUser () {
			$userClass = self::USER_CLASS;
			return new $userClass ();
		}

		public function setUp () {
			if (!interface_exists ('Doctrine\Common\Persistence\ObjectManager')) {
				$this->markTestSkipped ('Doctrine Common has to be installed for this test to run.');
			}
			gc_enable ();
			$c     = new Canonicalizer ();
			$class = $this->createMock ('Doctrine\Common\Persistence\Mapping\ClassMetadata');
			$ef    = $this->createMock ('Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface');
			$ef->expects ($this->any ())
				->method ('getEncoder')
				->will ($this->returnValue (new BCryptPasswordEncoder (4)));
			$this->repository = $this->createMock ('Doctrine\Common\Persistence\ObjectRepository');
			$this->om         = $this->createMock ('Doctrine\Common\Persistence\ObjectManager');
			$this->om->expects ($this->any ())
				->method ('getRepository')
				->with ($this->equalTo (self::USER_CLASS))
				->will ($this->returnValue ($this->repository));
			$this->om->expects ($this->any ())
				->method ('getClassMetadata')
				->with ($this->equalTo (self::USER_CLASS))
				->will ($this->returnValue ($class));
			$class->expects ($this->any ())
				->method ('getName')
				->will ($this->returnValue (self::USER_CLASS));
			$this->userManager = $this->createUserManager ($ef, $c, $this->om, self::USER_CLASS);
		}

		protected function tearDown () {
			parent::tearDown ();
			gc_collect_cycles ();
		}

		public function testDeleteUser () {
			$user = $this->getUser ();
			$this->om->expects ($this->once ())->method ('remove')->with ($this->equalTo ($user));
			$this->om->expects ($this->once ())->method ('flush');
			$this->userManager->deleteUser ($user);
		}

		public function testGetClass () {
			$this->assertEquals (self::USER_CLASS, $this->userManager->getClass ());
		}

		public function testFindUserBy () {
			$crit = array ("foo" => "bar");
			$this->repository->expects ($this->once ())->method ('findOneBy')->with ($this->equalTo ($crit))->will ($this->returnValue (array ()));
			$this->userManager->findUserBy ($crit);
		}

		public function testFindUsers () {
			$this->repository->expects ($this->once ())->method ('findAll')->will ($this->returnValue (array ()));
			$this->userManager->findUsers ();
		}

		public function testUpdateUser () {
			$user = $this->getUser ();
			$this->om->expects ($this->once ())->method ('persist')->with ($this->equalTo ($user));
			$this->om->expects ($this->once ())->method ('flush');
			$this->userManager->updateUser ($user);
			$this->assertEquals ($user->getEmail (), $user->getEmailCanonical (), 'email and emailCanonical do not match');
			$this->assertNotEmpty ($user->getPassword (), 'Password was not generated');
		}
	}