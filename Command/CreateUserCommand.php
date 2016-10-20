<?php
	namespace FAPerezG\UsersBundle\Command;

	use FAPerezG\UsersBundle\Model\User;
	use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
	use Symfony\Component\Console\Input\InputArgument;
	use Symfony\Component\Console\Input\InputInterface;
	use Symfony\Component\Console\Input\InputOption;
	use Symfony\Component\Console\Output\OutputInterface;
	use Symfony\Component\Console\Question\ChoiceQuestion;
	use Symfony\Component\Console\Question\Question;

	class CreateUserCommand extends ContainerAwareCommand {
		/**
		 * @see Command
		 */
		protected function configure () {
			$this
				->setName ('fos:user:create')
				->setDescription ('Create a user.')
				->setDefinition (array (
					new InputArgument('email', InputArgument::REQUIRED, 'The email'),
					new InputArgument('password', InputArgument::REQUIRED, 'The password'),
					new InputArgument('full-name', InputArgument::OPTIONAL, 'The full name'),
					new InputArgument('locale', InputArgument::OPTIONAL, 'The locale'),
					new InputOption('super-admin', null, InputOption::VALUE_NONE, 'Set the user as super admin'),
					new InputOption('inactive', null, InputOption::VALUE_NONE, 'Set the user as inactive'),
				))
				->setHelp (<<<EOT
The <info>fos:user:create</info> command creates a user:

  <info>php app/console fos:user:create faperezg@gmail.com</info>

This interactive shell will ask you for the email of the user and then a password.

You can alternatively specify the email, password, full name and locale as the first, second, third and fourth arguments:

  <info>php app/console fos:user:create faperezg@gmail.com mypassword "Felipe Pérez" es</info>

You can create a super admin via the super-admin flag:

  <info>php app/console fos:user:create faperezg@gmail.com --super-admin</info>

You can create an inactive user (will not be able to log in):

  <info>php app/console fos:user:create faperezg@gmail.com --inactive</info>

EOT
				);
		}

		/**
		 * @see Command
		 *
		 * @param InputInterface $input
		 * @param OutputInterface $output
		 *
		 * @return int|null|void
		 */
		protected function execute (InputInterface $input, OutputInterface $output) {
			$email       = $input->getArgument ('email');
			$password    = $input->getArgument ('password');
			$fullName    = $input->getArgument ('full-name');
			$locale      = $input->getArgument ('locale');
			$inactive    = $input->getOption ('inactive');
			$superadmin  = $input->getOption ('super-admin');
			$manipulator = $this->getContainer ()->get ('faperezg_users.util.user_manipulator');
			$manipulator->create ($email, $password, $fullName, $locale, !$inactive, $superadmin);
			$output->writeln (sprintf ('Created user <comment>%s</comment>', $email));
		}

		/**
		 * @see Command
		 *
		 * @param InputInterface $input
		 * @param OutputInterface $output
		 */
		protected function interact (InputInterface $input, OutputInterface $output) {
			if (!$input->getArgument ('email')) {
				$helper   = $this->getHelper ('question');
				$question = new Question ('Please choose an email: ');
				$question->setValidator (function ($value) {
					if (empty ($value)) {
						throw new \Exception ('Email can not be empty');
					}
					if (!filter_var ($value, FILTER_VALIDATE_EMAIL)) {
						throw new \Exception ("'$value' is not a valid email address");
					}
					return $value;
				});
				$email = $helper->ask ($input, $output, $question);
				$input->setArgument ('email', $email);
			}

			if (!$input->getArgument ('password')) {
				$helper   = $this->getHelper ('question');
				$question = new Question ('Please choose a password: ');
				$question->setHidden (true);
				$question->setHiddenFallback (true);
				$question->setValidator (function ($value) {
					if (empty ($value)) {
						throw new \Exception ('Password can not be empty');
					}
					return $value;
				});
				$password = $helper->ask ($input, $output, $question);
				$input->setArgument ('password', $password);
			}

			if (!$input->getArgument ('full-name')) {
				$helper   = $this->getHelper ('question');
				$question = new Question ('Please choose the full name: ');
				$question->setValidator (function ($value) {
					if (empty ($value)) {
						throw new \Exception ('Full name can not be empty');
					}
					return $value;
				});
				$fullName = $helper->ask ($input, $output, $question);
				$input->setArgument ('full-name', $fullName);
			}

			if (!$input->getArgument ('locale')) {
				$helper   = $this->getHelper ('question');
				$question = new ChoiceQuestion ('Please choose the locale: ', User::getAvailableLocales (), 0);
				$question->setErrorMessage ('Locale %s is invalid');
				$locale = $helper->ask ($input, $output, $question);
				$input->setArgument ('locale', $locale);
			}
		}
	}