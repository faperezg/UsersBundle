<?php
	namespace FAPerezG\UsersBundle\Tests\Form\Type;

	use FAPerezG\UsersBundle\Entity\User;
	use FAPerezG\UsersBundle\Form\Type\ProfileFormType;
	use Symfony\Bundle\FrameworkBundle\Validator\ConstraintValidatorFactory;
	use Symfony\Component\Form\Extension\Core\CoreExtension;
	use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
	use Symfony\Component\Form\Forms;
	use Symfony\Component\Form\PreloadedExtension;
	use Symfony\Component\Form\Test\TypeTestCase;
	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\HttpFoundation\RequestStack;
	use Symfony\Component\Validator\Validation;

	class ProfileFormTypeTest extends TypeTestCase {
		private $requestStack;

		protected function setUp () {
			$request = new Request ();
			$request->setLocale ('es');
			$this->requestStack = new RequestStack ();
			$this->requestStack->push ($request);
			$coreExtension = new CoreExtension ();
			$this->factory = Forms::createFormFactoryBuilder ()
				->addExtensions ($this->getExtensions ())
				->addExtension ($coreExtension)
				->getFormFactory ();
			parent::setUp ();
		}

		protected function tearDown () {
			parent::tearDown ();
			unset ($this->requestStack, $this->factory);
		}

		protected function getExtensions () {
			/** @var \Symfony\Component\DependencyInjection\ContainerInterface $container */
			$container                  = $this->createMock ('\Symfony\Component\DependencyInjection\ContainerInterface');
			$userPasswordValidator      = $this->createMock ('\Symfony\Component\Security\Core\Validator\Constraints\UserPasswordValidator');
			$constraintValidatorFactory = new ConstraintValidatorFactory ($container, [
				'security.validator.user_password' => $userPasswordValidator,
			]);
			$validator                  = Validation::createValidatorBuilder ()->setConstraintValidatorFactory ($constraintValidatorFactory)->getValidator ();
			$type                       = new ProfileFormType ($this->requestStack, User::class);
			return [
				new PreloadedExtension ([$type], []),
				new ValidatorExtension ($validator),
			];
		}

		public function testSubmitValidData () {
			$formData = [
				'email'            => 'jack@example.org',
				'fullName'         => 'Jack Sparrow',
				'current_password' => '12345678',
			];

			$object = ProfileUser::fromArray ($formData);
			$form   = $this->factory->create (ProfileFormType::class, $object);

			// submit the data to the form directly
			$form->submit ($formData);

			$this->assertTrue ($form->isValid ());
			$this->assertTrue ($form->isSynchronized ());
			$this->assertEquals ($object, $form->getData ());

			$view     = $form->createView ();
			$children = $view->children;

			foreach (array_keys ($formData) as $key) {
				$this->assertArrayHasKey ($key, $children);
			}

			unset ($children, $view, $form, $object, $formData);
		}
	}

	class ProfileUser extends User {
		public static function fromArray ($data) {
			$object = new ProfileUser ();
			foreach ($data as $key => $value) {
				$method = 'set' . ucfirst ($key);
				if (method_exists ($object, $method)) {
					$object->$method ($value);
				}
			}
			return $object;
		}
	}