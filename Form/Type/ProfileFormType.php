<?php
	namespace FAPerezG\UsersBundle\Form\Type;

	use Symfony\Component\Form\AbstractType;
	use Symfony\Component\Form\Extension\Core\Type\EmailType;
	use Symfony\Component\Form\Extension\Core\Type\PasswordType;
	use Symfony\Component\Form\Extension\Core\Type\TextType;
	use Symfony\Component\Form\FormBuilderInterface;
	use Symfony\Component\OptionsResolver\OptionsResolver;
	use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

	class ProfileFormType extends AbstractType {
		private $class;

		public function __construct ($class) {
			$this->class = $class;
		}

		public function buildForm (FormBuilderInterface $builder, array $options) {
			$this->buildUserForm ($builder);
			$builder
				->add ('fullName', TextType::class, array (
					'label'              => 'label.full_name',
					'translation_domain' => 'FAPerezGUsersBundle',
				))->add ('current_password', PasswordType::class, array (
					'label'              => 'form.current_password',
					'translation_domain' => 'FOSUserBundle',
					'mapped'             => false,
					'constraints'        => new UserPassword(),
				));
		}

		public function configureOptions (OptionsResolver $resolver) {
			$resolver->setDefaults (array (
				'data_class'    => $this->class,
				'csrf_token_id' => 'profile',
			));
		}

		protected function buildUserForm (FormBuilderInterface $builder) {
			$builder
				->add ('email', EmailType::class, array (
					'label'              => 'form.email',
					'translation_domain' => 'FOSUserBundle',
				));
		}
	}