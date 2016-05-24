<?php
	namespace FAPerezG\UsersBundle\Form\Type;

	use FAPerezG\UsersBundle\Entity\User;
	use Symfony\Component\Form\AbstractType;
	use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
	use Symfony\Component\Form\Extension\Core\Type\EmailType;
	use Symfony\Component\Form\Extension\Core\Type\PasswordType;
	use Symfony\Component\Form\Extension\Core\Type\TextType;
	use Symfony\Component\Form\FormBuilderInterface;
	use Symfony\Component\HttpFoundation\RequestStack;
	use Symfony\Component\Intl\Intl;
	use Symfony\Component\OptionsResolver\OptionsResolver;
	use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

	class ProfileFormType extends AbstractType {
		private $class;

		private $locale;

		/**
		 * ProfileFormType constructor.
		 * @param RequestStack $requestStack
		 * @param $class
		 */
		public function __construct (RequestStack $requestStack, $class) {
			$this->locale = $requestStack->getCurrentRequest ()->getLocale ();
			$this->class = $class;
		}

		private function getAvailableLocalesAsChoices () {
			$availableLocales = [];
			$validLocales     = User::getAvailableLocales ();
			foreach ($validLocales as $validLocale) {
				$availableLocales [ucwords (Intl::getLanguageBundle ()->getLanguageName ($validLocale, null, $this->locale))] = $validLocale;
			}
			return $availableLocales;
		}

		public function buildForm (FormBuilderInterface $builder, array $options) {
			$this->buildUserForm ($builder);
			$builder
				->add ('fullName', TextType::class, array (
					'label'              => 'label.full_name',
					'translation_domain' => 'FAPerezGUsersBundle',
				))->add ('locale', ChoiceType::class, array (
					'choices'                   => $this->getAvailableLocalesAsChoices (),
					'choices_as_values'         => true,
					'choice_translation_domain' => false,
					'label'                     => 'label.locale',
					'translation_domain'        => 'FAPerezGUsersBundle',
				))->add ('current_password', PasswordType::class, array (
					'label'              => 'form.password',
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