<?php
	namespace FAPerezG\UsersBundle\Mailer;

	use FAPerezG\UsersBundle\Model\UserInterface;
	use FOS\UserBundle\Mailer\MailerInterface;
	use FOS\UserBundle\Model\UserInterface as BaseUserInterface;
	use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

	class TwigSwiftMailer implements MailerInterface {
		protected $mailer;
		protected $router;
		protected $twig;
		protected $parameters;

		public function __construct (\Swift_Mailer $mailer, UrlGeneratorInterface $router, \Twig_Environment $twig, array $parameters) {
			$this->mailer     = $mailer;
			$this->router     = $router;
			$this->twig       = $twig;
			$this->parameters = $parameters;
		}

		public function sendConfirmationEmailMessage (BaseUserInterface $user) {
			if (!($user instanceof UserInterface)) {
				throw new \InvalidArgumentException ('This mailer has been created to use FAPerezG\UsersBundle\Model\UserInterface as a parameter');
			}
			$locale   = $user->getLocale () ? : UserInterface::LOCALE_DEFAULT;
			$template = str_replace ('{_locale}', $locale, $this->parameters ['template']['confirmation']);
			$url      = $this->router->generate (
				'fos_user_registration_confirm',
				array (
					'token'   => $user->getConfirmationToken (),
					'_locale' => $locale,
				),
				true
			);
			$context  = array (
				'user'            => $user,
				'confirmationUrl' => $url,
			);

			$this->sendMessage ($template, $context, $this->parameters['from_email']['confirmation'], $user->getEmail ());
		}

		public function sendResettingEmailMessage (BaseUserInterface $user) {
			if (!($user instanceof UserInterface)) {
				throw new \InvalidArgumentException ('This mailer has been created to use FAPerezG\UsersBundle\Model\UserInterface as a parameter');
			}
			$locale   = $user->getLocale () ? : UserInterface::LOCALE_DEFAULT;
			$template = str_replace ('{_locale}', $locale, $this->parameters ['template']['resetting']);
			$url      = $this->router->generate (
				'fos_user_resetting_reset',
				array (
					'token'   => $user->getConfirmationToken (),
					'_locale' => $locale,
				),
				true
			);
			$context  = array (
				'user'            => $user,
				'confirmationUrl' => $url,
			);
			$this->sendMessage ($template, $context, $this->parameters['from_email']['resetting'], $user->getEmail ());
		}

		/**
		 * @param string $templateName
		 * @param array $context
		 * @param string $fromEmail
		 * @param string $toEmail
		 */
		protected function sendMessage ($templateName, $context, $fromEmail, $toEmail) {
			$template = $this->twig->loadTemplate ($templateName);
			$subject  = $template->renderBlock ('subject', $context);
			$textBody = $template->renderBlock ('body_text', $context);
			$htmlBody = $template->renderBlock ('body_html', $context);

			$message = \Swift_Message::newInstance ()
				->setSubject ($subject)
				->setFrom ($fromEmail)
				->setTo ($toEmail);

			if (!empty($htmlBody)) {
				$message->setBody ($htmlBody, 'text/html')
					->addPart ($textBody, 'text/plain');
			} else {
				$message->setBody ($textBody);
			}

			$this->mailer->send ($message);
		}
	}