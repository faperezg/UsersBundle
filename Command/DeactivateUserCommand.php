<?php
	namespace FAPerezG\UsersBundle\Command;

	use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
	use Symfony\Component\Console\Input\InputArgument;
	use Symfony\Component\Console\Input\InputInterface;
	use Symfony\Component\Console\Output\OutputInterface;

	class DeactivateUserCommand extends ContainerAwareCommand {
		/**
		 * @see Command
		 */
		protected function configure () {
			$this
				->setName ('fos:user:deactivate')
				->setDescription ('Deactivate a user')
				->setDefinition (array (
					new InputArgument('email', InputArgument::REQUIRED, 'The email'),
				))
				->setHelp (<<<EOT
The <info>fos:user:deactivate</info> command deactivates a user (will not be able to log in)

  <info>php app/console fos:user:deactivate faperezg@gmail.com</info>
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
			$email    = $input->getArgument ('email');
			$manipulator = $this->getContainer ()->get ('faperezg_users.util.user_manipulator');
			$manipulator->deactivate ($email);
			$output->writeln (sprintf ('User "%s" has been deactivated.', $email));
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
		}
	}