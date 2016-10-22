<?php
	namespace FAPerezG\UsersBundle\Tests\Command;

	use FAPerezG\UsersBundle\Command\DemoteUserCommand;
	use FAPerezG\UsersBundle\Model\UserInterface;

	class DemoteUserCommandTest extends AbstractUserCommandTest {
		protected function getCommandClassName () {
			return DemoteUserCommand::class;
		}

		protected function getCommandAlias () {
			return 'fos:user:demote';
		}

		public function testInteractiveExecutionWithCommonRole () {
			$notAnEmail = 'jack';
			$empty      = '';
			$role       = 'ROLE_TEST';
			$stream     = $this->getInputStream ("$empty\n$notAnEmail\n{$this->email}\n$empty\n$role");
			$e          = null;
			try {
				$this->saveTestUser ($role);
				$this->helper->setInputStream ($stream);
				$this->commandTester->execute (['command' => $this->command->getName ()]);
				$output = $this->commandTester->getDisplay ();

				$this->assertContains ($message = 'Please choose an email: ', $output, 'Command should ask for email');
				$this->assertEquals (3, $times = substr_count ($output, $message), "Command should ask for email 3 times. Got $times");
				$this->assertContains ($message = 'Email can not be empty', $output, 'Command should reject empty email');
				$this->assertEquals (1, $times = substr_count ($output, $message), "Empty email message should be shown 1 time. Shown $times times");
				$this->assertContains ($message = "'$notAnEmail' is not a valid email address", $output, 'Command should reject non-valid email address');
				$this->assertEquals (1, $times = substr_count ($output, $message), "Non-valid email message should be shown 1 time. Shown $times times");

				$this->assertContains ($message = 'Please choose a role: ', $output, 'Command should ask for role');
				$this->assertEquals (2, $times = substr_count ($output, $message), "Command should ask for role 2 times. Got $times");
				$this->assertContains ($message = 'Role can not be empty', $output, 'Command should reject empty role');
				$this->assertEquals (1, $times = substr_count ($output, $message), "Empty role message should be shown 1 time. Shown $times times");

				$this->assertContains ($message = "Role '$role' has been removed from user '{$this->email}'", $output, 'Demotion message should be shown');
				$this->assertEquals (1, $times = substr_count ($output, $message), "Demotion message should be shown 1 time. Shown $times times");
				$this->assertEquals (0, $statusCode = $this->commandTester->getStatusCode (), "Status code should be 0. Got $statusCode");

				$user = $this->loadTestUser ();
				$this->assertFalse ($user->hasRole ($role), 'Command did not demote user');

				unset ($user, $output);
			} catch (\Exception $ie) {
				$e = $ie;
			} finally {
				fclose ($stream);
			}
			unset ($stream, $role, $empty, $notAnEmail);
			if ($e) {
				throw $e;
			}
		}

		public function testInteractiveExecutionWithUnknownRole () {
			$validRole   = 'ROLE_TEST';
			$invalidRole = 'ROLE_UNKNOWN';
			$stream      = $this->getInputStream ("{$this->email}\n$invalidRole");
			$e           = null;
			try {
				$this->saveTestUser ($validRole);
				$this->helper->setInputStream ($stream);
				$this->commandTester->execute (['command' => $this->command->getName ()]);
				$output = $this->commandTester->getDisplay ();

				$this->assertContains ($message = 'Please choose an email: ', $output, 'Command should ask for email');
				$this->assertEquals (1, $times = substr_count ($output, $message), "Command should ask for email 1 time. Got $times");

				$this->assertContains ($message = 'Please choose a role: ', $output, 'Command should ask for role');
				$this->assertEquals (1, $times = substr_count ($output, $message), "Command should ask for role 1 time. Got $times");

				$this->assertContains ($message = "User '{$this->email}' did not have '$invalidRole' role", $output, 'Invalid role message should be shown');
				$this->assertEquals (1, $times = substr_count ($output, $message), "Invalid role message should be shown 1 time. Shown $times times");
				$this->assertEquals (0, $statusCode = $this->commandTester->getStatusCode (), "Status code should be 0. Got $statusCode");

				$user = $this->loadTestUser ();
				$this->assertTrue ($user->hasRole ($validRole), 'Command did demote user');

				unset ($user, $output);
			} catch (\Exception $ie) {
				$e = $ie;
			} finally {
				fclose ($stream);
			}
			unset ($stream, $invalidRole, $validRole);
			if ($e) {
				throw $e;
			}
		}

		public function testInteractiveExecutionWithSuperAdminRole () {
			$role   = UserInterface::ROLE_SUPER_ADMIN;
			$stream = $this->getInputStream ("{$this->email}\n$role");
			$e      = null;
			try {
				$this->saveTestUser ($role);
				$this->helper->setInputStream ($stream);
				$this->commandTester->execute (['command' => $this->command->getName ()]);
				$output = $this->commandTester->getDisplay ();

				$this->assertContains ($message = 'Please choose an email: ', $output, 'Command should ask for email');
				$this->assertEquals (1, $times = substr_count ($output, $message), "Command should ask for email 1 time. Got $times");

				$this->assertContains ($message = 'Please choose a role: ', $output, 'Command should ask for role');
				$this->assertEquals (1, $times = substr_count ($output, $message), "Command should ask for role 1 time. Got $times");

				$this->assertContains ($message = "User '{$this->email}' has been demoted as a simple user", $output, 'Demotion message should be shown');
				$this->assertEquals (1, $times = substr_count ($output, $message), "Demotion message should be shown 1 time. Shown $times times");
				$this->assertEquals (0, $statusCode = $this->commandTester->getStatusCode (), "Status code should be 0. Got $statusCode");

				$user = $this->loadTestUser ();
				$this->assertFalse ($user->hasRole ($role), "User should not have $role role");
				$this->assertFalse ($user->isSuperAdmin (), 'Command did not demote user');

				unset ($user, $output);
			} catch (\Exception $ie) {
				$e = $ie;
			} finally {
				fclose ($stream);
			}
			unset ($stream, $role);
			if ($e) {
				throw $e;
			}
		}

		public function testNonInteractiveExecutionWithCommonRole () {
			$role = 'ROLE_TEST';
			$this->saveTestUser ($role);
			$this->commandTester->execute ([
				'command' => $this->command->getName (),
				'email'   => $this->email,
				'role'    => $role,
			]);
			$output = $this->commandTester->getDisplay ();

			$this->assertContains ($message = "Role '$role' has been removed from user '{$this->email}'", $output, 'Demotion message should be shown');
			$this->assertEquals (1, $times = substr_count ($output, $message), "Demotion message should be shown 1 time. Shown $times times");
			$this->assertEquals (0, $statusCode = $this->commandTester->getStatusCode (), "Status code should be 0. Got $statusCode");

			$user = $this->loadTestUser ();
			$this->assertFalse ($user->hasRole ($role), 'Command did not demote user');

			unset ($user, $output, $role);
		}

		public function testNonInteractiveExecutionWithSuperAdminRole () {
			$role = UserInterface::ROLE_SUPER_ADMIN;
			$this->saveTestUser ($role);
			$this->commandTester->execute ([
				'command' => $this->command->getName (),
				'email'   => $this->email,
				'role'    => $role,
			]);
			$output = $this->commandTester->getDisplay ();

			$this->assertContains ($message = "User '{$this->email}' has been demoted as a simple user", $output, 'Demotion message should be shown');
			$this->assertEquals (1, $times = substr_count ($output, $message), "Demotion message should be shown 1 time. Shown $times times");
			$this->assertEquals (0, $statusCode = $this->commandTester->getStatusCode (), "Status code should be 0. Got $statusCode");

			$user = $this->loadTestUser ();
			$this->assertFalse ($user->hasRole ($role), "User should not have $role role");
			$this->assertFalse ($user->isSuperAdmin (), 'Command did not demote user');

			unset ($user, $output, $role);
		}
	}

