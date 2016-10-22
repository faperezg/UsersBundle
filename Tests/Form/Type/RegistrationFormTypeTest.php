<?php
	namespace FAPerezG\UsersBundle\Tests\Form\Type;

	use FAPerezG\UsersBundle\Entity\User;
	use FAPerezG\UsersBundle\Form\Type\RegistrationFormType;
	use Symfony\Component\Form\Extension\Core\CoreExtension;
	use Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension;
	use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
	use Symfony\Component\Form\Forms;
	use Symfony\Component\Form\PreloadedExtension;
	use Symfony\Component\Form\Test\TypeTestCase;
	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\HttpFoundation\RequestStack;
	use Symfony\Component\Validator\ConstraintViolationList;
	use Symfony\Component\Validator\Validation;

	class RegistrationFormTypeTest extends TypeTestCase {
		private $requestStack;

		protected function setUp () {
			$request = new Request ();
			$request->setLocale ('es');
			$this->requestStack = new RequestStack ();
			$this->requestStack->push ($request);

			/** @var $validator \Symfony\Component\Validator\Validator\ValidatorInterface|\PHPUnit_Framework_MockObject_MockObject */
			$validator = $this->createMock ('\Symfony\Component\Validator\Validator\ValidatorInterface');
			$validator->method ('validate')->will ($this->returnValue (new ConstraintViolationList ()));
			$formTypeExtension = new FormTypeValidatorExtension ($validator);
			$coreExtension     = new CoreExtension();

			$this->factory = Forms::createFormFactoryBuilder ()
				->addExtensions ($this->getExtensions ())
				->addExtension ($coreExtension)
				->addTypeExtension ($formTypeExtension)
				->getFormFactory ();

			parent::setUp ();
		}

		protected function tearDown () {
			parent::tearDown ();
			unset ($this->requestStack, $this->factory);
		}

		protected function getExtensions () {
			// create a type instance with the mocked dependencies
			$type = new RegistrationFormType ($this->requestStack, User::class);
			return array (
				// register the type instances with the PreloadedExtension
				new PreloadedExtension (array ($type), array ()),
				new ValidatorExtension (Validation::createValidator ()),
			);
		}

		public function testSubmitValidData () {
			$formData = array (
				'email'         => 'jack@example.org',
				'fullName'      => 'Jack Sparrow',
				'plainPassword' => '12345678',
			);

			$object = RegistrationUser::fromArray ($formData);
			$form   = $this->factory->create (RegistrationFormType::class, $object);

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

	class RegistrationUser extends User {
		public static function fromArray ($data) {
			$object = new RegistrationUser ();
			foreach ($data as $key => $value) {
				$method = 'set' . ucfirst ($key);
				if (method_exists ($object, $method)) {
					$object->$method ($value);
				}
			}
			return $object;
		}
	}