<?php
	namespace FAPerezG\UsersBundle\Command;

	use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
	use Symfony\Component\Console\Input\InputArgument;
	use Symfony\Component\Console\Input\InputInterface;
	use Symfony\Component\Console\Input\InputOption;
	use Symfony\Component\Console\Output\OutputInterface;

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
					new InputOption('super-admin', null, InputOption::VALUE_NONE, 'Set the user as super admin'),
					new InputOption('inactive', null, InputOption::VALUE_NONE, 'Set the user as inactive'),
				))
				->setHelp (<<<EOT
The <info>fos:user:create</info> command creates a user:

  <info>php app/console fos:user:create faperezg@gmail.com</info>

This interactive shell will ask you for the email of the user and then a password.

You can alternatively specify the email and password and full name as the first, second and third arguments:

  <info>php app/console fos:user:create faperezg@gmail.com mypassword "Felipe Pérez"</info>

You can create a super admin via the super-admin flag:

  <info>php app/console fos:user:create faperezg@gmail.com --super-admin</info>

You can create an inactive user (will not be able to log in):

  <info>php app/console fos:user:create faperezg@gmail.com --inactive</info>

EOT
				);
		}

		/**
		 * @see Command
		 * @param InputInterface $input
		 * @param OutputInterface $output
		 * @return int|null|void
		 */
		protected function execute (InputInterface $input, OutputInterface $output) {
			$email       = $input->getArgument ('email');
			$password    = $input->getArgument ('password');
			$fullName    = $input->getArgument ('full-name');
			$inactive    = $input->getOption ('inactive');
			$superadmin  = $input->getOption ('super-admin');
			$manipulator = $this->getContainer ()->get ('faperezg_users.util.user_manipulator');
			$manipulator->create ($email, $password, $fullName, !$inactive, $superadmin);
			$output->writeln (sprintf ('Created user <comment>%s</comment>', $email));
		}

		/**
		 * @see Command
		 * @param InputInterface $input
		 * @param OutputInterface $output
		 */
		protected function interact (InputInterface $input, OutputInterface $output) {
			if (!$input->getArgument ('email')) {
				$email = $this->getHelper ('dialog')->askAndValidate (
					$output,
					'Please choose an email: ',
					function ($email) {
						if (empty($email)) {
							throw new \Exception('Email can not be empty');
						}
						if (!filter_var ($email, FILTER_VALIDATE_EMAIL)) {
							throw new \Exception (sprintf ('"%s" is not a valid email address', $email));
						}
						return $email;
					}
				);
				$input->setArgument ('email', $email);
			}

			if (!$input->getArgument ('password')) {
				$password = $this->getHelper ('dialog')->askAndValidate (
					$output,
					'Please choose a password: ',
					function ($password) {
						if (empty($password)) {
							throw new \Exception('Password can not be empty');
						}
						return $password;
					}
				);
				$input->setArgument ('password', $password);
			}

			if (!$input->getArgument ('full-name')) {
				$fullName = $this->getHelper ('dialog')->askAndValidate (
					$output,
					'Please choose the full name: ',
					function ($fullName) {
						if (empty ($fullName)) {
							throw new \Exception('Full name can not be empty');
						}
						return $fullName;
					}
				);
				$input->setArgument ('full-name', $fullName);
			}
		}
	}