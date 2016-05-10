<?php
	namespace FAPerezG\UsersBundle\Form\Type;

	use FAPerezG\UsersBundle\Entity\User;
	use Symfony\Component\Form\AbstractType;
	use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
	use Symfony\Component\Form\Extension\Core\Type\EmailType;
	use Symfony\Component\Form\Extension\Core\Type\PasswordType;
	use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
	use Symfony\Component\Form\Extension\Core\Type\TextType;
	use Symfony\Component\Form\FormBuilderInterface;
	use Symfony\Component\HttpFoundation\RequestStack;
	use Symfony\Component\Intl\Intl;
	use Symfony\Component\OptionsResolver\OptionsResolver;

	class RegistrationFormType extends AbstractType {
		private $class;

		private $locale;

		/**
		 * @param RequestStack $requestStack
		 * @param string $class The User class name
		 * @internal param Request $request
		 * @internal param Session $session
		 */
		public function __construct (RequestStack $requestStack, $class) {
			$this->locale = $requestStack->getCurrentRequest ()->getLocale ();
			$this->class  = $class;
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
				))->add ('locale', ChoiceType::class, array (
					'choices'                   => $this->getAvailableLocalesAsChoices (),
					'choices_as_values'         => true,
					'choice_translation_domain' => false,
					'data'                      => $this->locale,
					'label'                     => 'label.locale',
					'placeholder'               => '',
					'translation_domain'        => 'FAPerezGUsersBundle',
				));
		}

		public function configureOptions (OptionsResolver $resolver) {
			$resolver->setDefaults (array (
				'data_class'    => $this->class,
				'csrf_token_id' => 'registration',
			));
		}

	}