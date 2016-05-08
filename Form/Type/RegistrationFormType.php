<?php
	namespace FAPerezG\UsersBundle\Form\Type;

	use Symfony\Component\Form\AbstractType;
	use Symfony\Component\Form\Extension\Core\Type\EmailType;
	use Symfony\Component\Form\Extension\Core\Type\PasswordType;
	use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
	use Symfony\Component\Form\Extension\Core\Type\TextType;
	use Symfony\Component\Form\FormBuilderInterface;
	use Symfony\Component\OptionsResolver\OptionsResolver;

	class RegistrationFormType extends AbstractType {
		private $class;

		/**
		 * @param string $class The User class name
		 */
		public function __construct ($class) {
			$this->class = $class;
		}

		public function buildForm (FormBuilderInterface $builder, array $options) {
			$builder
				->add ('fullName', TextType::class, array (
					'label'              => 'label.full_name',
					'translation_domain' => 'FAPerezGUsersBundle',
				))->add ('email', EmailType::class, array (
					'label'              => 'form.email',
					'translation_domain' => 'FOSUserBundle',
				))->add ('plainPassword', RepeatedType::class, array (
					'type'            => PasswordType::class,
					'options'         => array ('translation_domain' => 'FOSUserBundle'),
					'first_options'   => array ('label' => 'form.password'),
					'second_options'  => array ('label' => 'form.password_confirmation'),
					'invalid_message' => 'fos_user.password.mismatch',
				));
		}

		public function configureOptions (OptionsResolver $resolver) {
			$resolver->setDefaults (array (
				'data_class'    => $this->class,
				'csrf_token_id' => 'registration',
			));
		}

	}