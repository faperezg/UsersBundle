<?php
	namespace FAPerezG\UsersBundle\Command;

	use FAPerezG\UsersBundle\Model\UserInterface;
	use FAPerezG\UsersBundle\Util\UserManipulator;
	use Symfony\Component\Console\Output\OutputInterface;

	class DemoteUserCommand extends RoleCommand {
		/**
		 * @see Command
		 */
		protected function configure () {
			parent::configure ();

			$this
				->setName ('fos:user:demote')
				->setDescription ('Demote a user by unsetting a role')
				->setHelp (<<<EOT
The <info>fos:user:demote</info> command demotes a user by unsetting a role

  <info>php app/console fos:user:demote faperezg@gmail.com ROLE_CUSTOM</info>
  <info>php app/console fos:user:demote --super faperezg@gmail.com</info>
EOT
				);
		}

		protected function executeRoleCommand (UserManipulator $manipulator, OutputInterface $output, $email, $super, $role) {
			if (($super) || ($role == UserInterface::ROLE_SUPER_ADMIN)) {
				$manipulator->demote ($email);
				$output->writeln ("User '$email' has been demoted as a simple user");
			} else {
				if ($manipulator->removeRole ($email, $role)) {
					$output->writeln ("Role '$role' has been removed from user '$email'");
				} else {
					$output->writeln ("User '$email' did not have '$role' role");
				}
			}
		}
	}