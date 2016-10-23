<?php
	namespace FAPerezG\UsersBundle\Tests\Model;

	use FAPerezG\UsersBundle\Model\UserManager;
	use FAPerezG\UsersBundle\Entity\User;
	use FOS\UserBundle\Util\Canonicalizer;
	use Symfony\Component\Security\Core\Encoder\EncoderFactory;

	class UserManagerTest extends \PHPUnit_Framework_TestCase {
		/** @var \PHPUnit_Framework_MockObject_MockObject|UserManager */
		private $manager;
		/** @var \PHPUnit_Framework_MockObject_MockObject|EncoderFactory */
		private $encoderFactory;
		/** @var \PHPUnit_Framework_MockObject_MockObject|Canonicalizer */
		private $emailCanonicalizer;

		protected function setUp () {
			gc_enable ();
			$this->encoderFactory        = $this->createMock ('Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface');
			$this->emailCanonicalizer    = $this->createMock ('FOS\UserBundle\Util\Canonicalizer');

			$this->manager = $this->getUserManager (array (
				$this->encoderFactory,
				$this->emailCanonicalizer,
			));
		}

		protected function tearDown () {
			parent::tearDown ();
			gc_collect_cycles ();
		}

		/**
		 * @return \PHPUnit_Framework_MockObject_MockObject|User
		 */
		private function getUser () {
			return $this->getMockBuilder ('FAPerezG\UsersBundle\Model\User')
				->getMockForAbstractClass ();
		}

		private function getUserManager (array $args) {
			return $this->getMockBuilder ('FAPerezG\UsersBundle\Model\UserManager')
				->setConstructorArgs ($args)
				->getMockForAbstractClass ();
		}

		public function testUpdateCanonicalFields () {
			$user = $this->getUser ();
			$user->setEmail ('User@Example.com');

			$this->emailCanonicalizer->expects ($this->once ())
				->method ('canonicalize')
				->with ('User@Example.com')
				->will ($this->returnCallback ('strtolower'));

			$this->manager->updateCanonicalFields ($user);
			$this->assertEquals ('user@example.com', $user->getUsernameCanonical ());
			$this->assertEquals ('user@example.com', $user->getEmailCanonical ());
		}

		public function testUpdatePassword () {
			$encoder = $this->createMock ('Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface');
			$user    = $this->getUser ();
			$user->setPlainPassword ('password');

			$this->encoderFactory->expects ($this->once ())
				->method ('getEncoder')
				->will ($this->returnValue ($encoder));

			$encoder->expects ($this->once ())
				->method ('encodePassword')
				->with ('password', $user->getSalt ())
				->will ($this->returnValue ('encodedPassword'));

			$this->manager->updatePassword ($user);
			$this->assertEquals ('encodedPassword', $user->getPassword (), '->updatePassword() sets encoded password');
			$this->assertNull ($user->getPlainPassword (), '->updatePassword() erases credentials');
		}

		public function testFindUserByUsername () {
			$this->manager->expects ($this->once ())
				->method ('findUserBy')
				->with ($this->equalTo (array ('emailCanonical' => 'jack@example.com')));
			$this->emailCanonicalizer->expects ($this->once ())
				->method ('canonicalize')
				->with ('jack@example.com')
				->will ($this->returnValue ('jack@example.com'));

			$this->manager->findUserByUsername ('jack@example.com');
		}

		public function testFindUserByEmail () {
			$this->manager->expects ($this->once ())
				->method ('findUserBy')
				->with ($this->equalTo (array ('emailCanonical' => 'jack@email.org')));
			$this->emailCanonicalizer->expects ($this->once ())
				->method ('canonicalize')
				->with ('jack@email.org')
				->will ($this->returnValue ('jack@email.org'));

			$this->manager->findUserByEmail ('jack@email.org');
		}

		public function testFindUserByEmailLowercasesTheEmail () {
			$this->manager->expects ($this->once ())
				->method ('findUserBy')
				->with ($this->equalTo (array ('emailCanonical' => 'jack@email.org')));
			$this->emailCanonicalizer->expects ($this->once ())
				->method ('canonicalize')
				->with ('JaCk@EmAiL.oRg')
				->will ($this->returnValue ('jack@email.org'));

			$this->manager->findUserByEmail ('JaCk@EmAiL.oRg');
		}

		public function testFindUserByUsernameOrEmailWithEmail () {
			$this->manager->expects ($this->once ())
				->method ('findUserBy')
				->with ($this->equalTo (array ('emailCanonical' => 'jack@email.org')));
			$this->emailCanonicalizer->expects ($this->once ())
				->method ('canonicalize')
				->with ('JaCk@EmAiL.oRg')
				->will ($this->returnValue ('jack@email.org'));

			$this->manager->findUserByUsernameOrEmail ('JaCk@EmAiL.oRg');
		}
	}