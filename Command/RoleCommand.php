<?php
	namespace FAPerezG\UsersBundle\Command;

	use FAPerezG\UsersBundle\Util\UserManipulator;
	use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
	use Symfony\Component\Console\Input\InputArgument;
	use Symfony\Component\Console\Input\InputInterface;
	use Symfony\Component\Console\Input\InputOption;
	use Symfony\Component\Console\Output\OutputInterface;
	use Symfony\Component\Console\Question\Question;

	abstract class RoleCommand extends ContainerAwareCommand {
		/**
		 * @see Command
		 */
		protected function configure () {
			$this->setDefinition (array (
				new InputArgument ('email', InputArgument::REQUIRED, 'The email'),
				new InputArgument ('role', InputArgument::OPTIONAL, 'The role'),
				new InputOption ('super', null, InputOption::VALUE_NONE, 'Instead specifying role, use this to quickly add the super administrator role'),
			));
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
			$email = $input->getArgument ('email');
			$role  = $input->getArgument ('role');
			$super = (true === $input->getOption ('super'));
			if ((null !== $role) && ($super)) {
				throw new \InvalidArgumentException ('You can pass either the role or the --super option (but not both simultaneously)');
			}
			if ((null === $role) && (!$super)) {
				throw new \RuntimeException ('Not enough arguments');
			}
			$manipulator = $this->getContainer ()->get ('faperezg_users.util.user_manipulator');
			$this->executeRoleCommand ($manipulator, $output, $email, $super, $role);
		}

		/**
		 * @see Command
		 *
		 * @param UserManipulator $manipulator
		 * @param OutputInterface $output
		 * @param string $email
		 * @param boolean $super
		 * @param string $role
		 *
		 * @return void
		 */
		abstract protected function executeRoleCommand (UserManipulator $manipulator, OutputInterface $output, $email, $super, $role);

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

			if ((true !== $input->getOption ('super')) && (!$input->getArgument ('role'))) {
				$helper   = $this->getHelper ('question');
				$question = new Question ('Please choose a role: ');
				$question->setValidator (function ($value) {
					if (empty ($value)) {
						throw new \Exception ('Role can not be empty');
					}
					return $value;
				});
				$role = $helper->ask ($input, $output, $question);
				$input->setArgument ('role', $role);
			}
		}
	}