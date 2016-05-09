<?php
	namespace FAPerezG\UsersBundle\Tests\Model;

	use FAPerezG\UsersBundle\Model\User;

	class UserTest extends \PHPUnit_Framework_TestCase {
		public function testEmail () {
			$user = $this->getUser ();
			$this->assertNull ($user->getEmail ());

			$user->setEmail ('tony@mail.org');
			$this->assertEquals ('tony@mail.org', $user->getEmail ());
		}

		public function testIsPasswordRequestNonExpired () {
			$user                = $this->getUser ();
			$passwordRequestedAt = new \DateTime('-10 seconds');

			$user->setPasswordRequestedAt ($passwordRequestedAt);

			$this->assertSame ($passwordRequestedAt, $user->getPasswordRequestedAt ());
			$this->assertTrue ($user->isPasswordRequestNonExpired (15));
			$this->assertFalse ($user->isPasswordRequestNonExpired (5));
		}

		public function testIsPasswordRequestAtCleared () {
			$user                = $this->getUser ();
			$passwordRequestedAt = new \DateTime('-10 seconds');

			$user->setPasswordRequestedAt ($passwordRequestedAt);
			$user->setPasswordRequestedAt (null);

			$this->assertFalse ($user->isPasswordRequestNonExpired (15));
			$this->assertFalse ($user->isPasswordRequestNonExpired (5));
		}

		public function testTrueHasRole () {
			$user        = $this->getUser ();
			$defaultrole = User::ROLE_DEFAULT;
			$newrole     = 'ROLE_X';
			$this->assertTrue ($user->hasRole ($defaultrole));
			$user->addRole ($defaultrole);
			$this->assertTrue ($user->hasRole ($defaultrole));
			$user->addRole ($newrole);
			$this->assertTrue ($user->hasRole ($newrole));
		}

		public function testFalseHasRole () {
			$user    = $this->getUser ();
			$newrole = 'ROLE_X';
			$this->assertFalse ($user->hasRole ($newrole));
			$user->addRole ($newrole);
			$this->assertTrue ($user->hasRole ($newrole));
		}

		/**
		 * @return User
		 */
		protected function getUser () {
			return $this->getMockForAbstractClass ('FAPerezG\UsersBundle\Model\User');
		}
	}