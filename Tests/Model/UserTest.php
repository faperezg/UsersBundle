<?php
	namespace FAPerezG\UsersBundle\Tests\Model;

	use FAPerezG\UsersBundle\Model\User;
	use FAPerezG\UsersBundle\Tests\DummyUser;
	use Symfony\Component\Translation\Loader\XliffFileLoader;
	use Symfony\Component\Translation\Translator;
	use Symfony\Component\Validator\Constraints\Valid;
	use Symfony\Component\Validator\ConstraintViolation;
	use Symfony\Component\Validator\Validation;

	class UserTest extends \PHPUnit_Framework_TestCase {
		protected function setUp () {
			gc_enable ();
			parent::setUp ();
		}

		protected function tearDown () {
			parent::tearDown ();
			gc_collect_cycles ();
		}

		/**
		 * @return User
		 */
		private function getUser () {
			return $this->getMockForAbstractClass ('FAPerezG\UsersBundle\Model\User');
		}

		private function getEmptyDummyUser () {
			$user = new DummyUser ();
			$user->setEmail (null)
				->setPlainPassword (null)
				->setFullName (null);
			return $user;
		}

		/**
		 * @return \Symfony\Component\Validator\ValidatorInterface
		 */
		private function getValidator () {
			return Validation::createValidatorBuilder ()
				->addXmlMappings ([
					__DIR__ . '/../../Resources/config/validation.xml',
				])
				->getValidator ();
		}

		private function getTranslators () {
			$translators = [];
			foreach (['en', 'es'] as $locale) {
				$translator = new Translator ($locale);
				$translator->addLoader ('xliff', new XliffFileLoader ());
				$translator->addResource ('xliff', __DIR__ . "/../../Resources/translations/validators.$locale.xliff", $locale);
				$translators [ $locale ] = $translator;
			}
			return $translators;
		}

		public function testEmail () {
			$user = $this->getUser ();
			$this->assertNull ($user->getEmail ());

			$user->setEmail ('jack@example.org');
			$this->assertEquals ('jack@example.org', $user->getEmail ());
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

		public function testUserValidations () {
			$translators = $this->getTranslators ();
			$validator  = $this->getValidator ();

			// Default test
			$user   = $this->getEmptyDummyUser ();
			$errors = $validator->validate ($user, new Valid (), ['FAPerezGUsers_Profile', 'FAPerezGUsers_Registration', 'Default']);
			$this->assertEquals (3, count ($errors));
			/** @var ConstraintViolation $error */
			foreach ($errors as $error) {
				$translationID = $error->getMessage ();
				if (strpos ($translationID, 'fos_user') === 0) {
					continue;
				}
				/** @var Translator $translator */
				foreach ($translators as $locale => $translator) {
					$translationMessage = $translator->trans ($translationID);
					$this->assertNotEquals ($translationID, $translationMessage, "Translation ID '$translationID' not found for locale '$locale'");
				}
			}
			$n = count ($errors);
			for ($i = $n - 1; $i >= 0; $i--) {
				if (($errors [ $i ]->getPropertyPath () == 'email') || ($errors [ $i ]->getPropertyPath () == 'plainPassword') || ($errors [ $i ]->getPropertyPath () == 'fullName')) {
					$errors->remove ($i);
				}
			}
			$this->assertEquals (0, count ($errors), 'Undetected errors found: ' . count ($errors));
			$user->setFullName ('Jack Sparrow')
				->setEmail ('jack@example.org')
				->setPlainPassword ('12345678');
			$errors = $validator->validate ($user, new Valid (), ['FAPerezGUsers_Profile', 'FAPerezGUsers_Registration', 'Default']);
			$this->assertEquals (0, count ($errors), 'User should have no errors. Got ' . count ($errors));
		}
	}