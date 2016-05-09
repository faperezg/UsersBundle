<?php
	namespace FAPerezG\UsersBundle\Command;

	use FAPerezG\UsersBundle\Util\UserManipulator;
	use Symfony\Component\Console\Output\OutputInterface;

	class PromoteUserCommand extends RoleCommand {
		/**
		 * @see Command
		 */
		protected function configure () {
			parent::configure ();

			$this
				->setName ('fos:user:promote')
				->setDescription ('Promotes a user by setting a role')
				->setHelp (<<<EOT
The <info>fos:user:promote</info> command promotes a user by setting a role

  <info>php app/console fos:user:promote faperezg@gmail.com ROLE_CUSTOM</info>
  <info>php app/console fos:user:promote --super faperezg@gmail.com</info>
EOT
				);
		}

		protected function executeRoleCommand (UserManipulator $manipulator, OutputInterface $output, $email, $super, $role) {
			if ($super) {
				$manipulator->promote ($email);
				$output->writeln (sprintf ('User "%s" has been promoted as a super administrator.', $email));
			} else {
				if ($manipulator->setRole ($email, $role)) {
					$output->writeln (sprintf ('Role "%s" has been added to user "%s".', $role, $email));
				} else {
					$output->writeln (sprintf ('User "%s" did already have "%s" role.', $email, $role));
				}
			}
		}
	}