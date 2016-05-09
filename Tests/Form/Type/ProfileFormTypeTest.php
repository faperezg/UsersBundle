<?php
	namespace FAPerezG\UsersBundle\Tests\Form\Type;

	use FAPerezG\UsersBundle\Entity\User;
	use FAPerezG\UsersBundle\Form\Type\ProfileFormType;
	use Symfony\Component\Form\Extension\Core\CoreExtension;
	use Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension;
	use Symfony\Component\Form\Forms;
	use Symfony\Component\Form\PreloadedExtension;
	use Symfony\Component\Form\Test\TypeTestCase;
	use Symfony\Component\Validator\ConstraintViolationList;

	class ProfileFormTypeTest extends TypeTestCase {
		protected function setUp () {
			parent::setUp ();

			/** @var $validator \Symfony\Component\Validator\Validator\ValidatorInterface|\PHPUnit_Framework_MockObject_MockObject */
			$validator = $this->getMock ('\Symfony\Component\Validator\Validator\ValidatorInterface');
			$validator->method ('validate')->will ($this->returnValue (new ConstraintViolationList()));
			$formTypeExtension = new FormTypeValidatorExtension($validator);
			$coreExtension     = new CoreExtension();

			$this->factory = Forms::createFormFactoryBuilder ()
				->addExtensions ($this->getExtensions ())
				->addExtension ($coreExtension)
				->addTypeExtension ($formTypeExtension)
				->getFormFactory ();
		}

		protected function getExtensions () {
			// create a type instance with the mocked dependencies
			$type = new ProfileFormType (User::class);
			return array (
				// register the type instances with the PreloadedExtension
				new PreloadedExtension(array ($type), array ()),
			);
		}

		public function testSubmitValidData () {
			$formData = array (
				'email'            => 'jack@example.org',
				'fullName'         => 'Jack Sparrow',
				'current_password' => '12345678',
			);

			$object = ProfileUser::fromArray ($formData);
			$type   = new ProfileFormType (User::class);
			$form   = $this->factory->create ($type, $object);

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