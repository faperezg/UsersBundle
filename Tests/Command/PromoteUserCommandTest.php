<?php
	namespace FAPerezG\UsersBundle\Tests\Command;

	use FAPerezG\UsersBundle\Command\PromoteUserCommand;
	use FAPerezG\UsersBundle\Model\UserInterface;

	class PromoteUserCommandTest extends AbstractUserCommandTest {
		protected function getCommandClassName () {
			return PromoteUserCommand::class;
		}

		protected function getCommandAlias () {
			return 'fos:user:promote';
		}

		public function testInteractiveExecutionWithCommonRole () {
			$notAnEmail = 'jack';
			$empty      = '';
			$role       = 'ROLE_TEST';
			$stream     = $this->getInputStream ("$empty\n$notAnEmail\n{$this->email}\n$empty\n$role");
			$e          = null;
			try {
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

				$this->assertContains ($message = "Role '$role' has been added to user '{$this->email}'", $output, 'Promotion message should be shown');
				$this->assertEquals (1, $times = substr_count ($output, $message), "Promotion message should be shown 1 time. Shown $times times");
				$this->assertEquals (0, $statusCode = $this->commandTester->getStatusCode (), "Status code should be 0. Got $statusCode");

				$user = $this->loadTestUser ();
				$this->assertTrue ($user->hasRole ($role), 'Command did not promote user');

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

		public function testInteractiveExecutionWithAlreadyAssignedRole () {
			$role   = 'ROLE_TEST';
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

				$this->assertContains ($message = "User '{$this->email}' did already have '$role' role.", $output, 'Assigned role message should be shown');
				$this->assertEquals (1, $times = substr_count ($output, $message), "Assigned role message should be shown 1 time. Shown $times times");
				$this->assertEquals (0, $statusCode = $this->commandTester->getStatusCode (), "Status code should be 0. Got $statusCode");

				unset ($output);
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

		public function testInteractiveExecutionWithSuperAdminRole () {
			$role   = UserInterface::ROLE_SUPER_ADMIN;
			$stream = $this->getInputStream ("{$this->email}\n$role");
			$e      = null;
			try {
				$this->helper->setInputStream ($stream);
				$this->commandTester->execute (['command' => $this->command->getName ()]);
				$output = $this->commandTester->getDisplay ();

				$this->assertContains ($message = 'Please choose an email: ', $output, 'Command should ask for email');
				$this->assertEquals (1, $times = substr_count ($output, $message), "Command should ask for email 1 time. Got $times");

				$this->assertContains ($message = 'Please choose a role: ', $output, 'Command should ask for role');
				$this->assertEquals (1, $times = substr_count ($output, $message), "Command should ask for role 1 time. Got $times");

				$this->assertContains ($message = "User '{$this->email}' has been promoted as a super administrator", $output, 'Promotion message should be shown');
				$this->assertEquals (1, $times = substr_count ($output, $message), "Promotion message should be shown 1 time. Shown $times times");
				$this->assertEquals (0, $statusCode = $this->commandTester->getStatusCode (), "Status code should be 0. Got $statusCode");

				$user = $this->loadTestUser ();
				$this->assertTrue ($user->hasRole ($role), "User should have $role role");
				$this->assertTrue ($user->isSuperAdmin (), 'Command did not promote user');

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

		public function testInteractiveExecutionWithSuperAdminFlag () {
			$role   = UserInterface::ROLE_SUPER_ADMIN;
			$stream = $this->getInputStream ("{$this->email}\n$role");
			$e      = null;
			try {
				$this->helper->setInputStream ($stream);
				$this->commandTester->execute ([
					'command' => $this->command->getName (),
					'--super' => true,
				]);
				$output = $this->commandTester->getDisplay ();

				$this->assertContains ($message = 'Please choose an email: ', $output, 'Command should ask for email');
				$this->assertEquals (1, $times = substr_count ($output, $message), "Command should ask for email 1 time. Got $times");

				$this->assertContains ($message = "User '{$this->email}' has been promoted as a super administrator", $output, 'Promotion message should be shown');
				$this->assertEquals (1, $times = substr_count ($output, $message), "Promotion message should be shown 1 time. Shown $times times");
				$this->assertEquals (0, $statusCode = $this->commandTester->getStatusCode (), "Status code should be 0. Got $statusCode");

				$user = $this->loadTestUser ();
				$this->assertTrue ($user->hasRole ($role), "User should have super admin role");
				$this->assertTrue ($user->isSuperAdmin (), 'Command did not promote user');

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
			$this->commandTester->execute ([
				'command' => $this->command->getName (),
				'email'   => $this->email,
				'role'    => $role,
			]);
			$output = $this->commandTester->getDisplay ();

			$this->assertContains ($message = "Role '$role' has been added to user '{$this->email}'", $output, 'Promotion message should be shown');
			$this->assertEquals (1, $times = substr_count ($output, $message), "Promotion message should be shown 1 time. Shown $times times");
			$this->assertEquals (0, $statusCode = $this->commandTester->getStatusCode (), "Status code should be 0. Got $statusCode");

			$user = $this->loadTestUser ();
			$this->assertTrue ($user->hasRole ($role), "User should have $role role");

			unset ($user, $output, $role);
		}

		public function testNonInteractiveExecutionWithSuperAdminRole () {
			$role = UserInterface::ROLE_SUPER_ADMIN;
			$this->commandTester->execute ([
				'command' => $this->command->getName (),
				'email'   => $this->email,
				'role'    => $role,
			]);
			$output = $this->commandTester->getDisplay ();

			$this->assertContains ($message = "User '{$this->email}' has been promoted as a super administrator", $output, 'Promotion message should be shown');
			$this->assertEquals (1, $times = substr_count ($output, $message), "Promotion message should be shown 1 time. Shown $times times");
			$this->assertEquals (0, $statusCode = $this->commandTester->getStatusCode (), "Status code should be 0. Got $statusCode");

			$user = $this->loadTestUser ();
			$this->assertTrue ($user->hasRole ($role), "User should have super admin role");
			$this->assertTrue ($user->isSuperAdmin (), 'Command did not promote user');

			unset ($user, $output, $role);
		}

		public function testNonInteractiveExecutionWithSuperAdminFlag () {
			$role = UserInterface::ROLE_SUPER_ADMIN;
			$this->commandTester->execute ([
				'command' => $this->command->getName (),
				'email'   => $this->email,
				'--super' => true,
			]);
			$output = $this->commandTester->getDisplay ();

			$this->assertContains ($message = "User '{$this->email}' has been promoted as a super administrator", $output, 'Promotion message should be shown');
			$this->assertEquals (1, $times = substr_count ($output, $message), "Promotion message should be shown 1 time. Shown $times times");
			$this->assertEquals (0, $statusCode = $this->commandTester->getStatusCode (), "Status code should be 0. Got $statusCode");

			$user = $this->loadTestUser ();
			$this->assertTrue ($user->hasRole ($role), "User should have super admin role");
			$this->assertTrue ($user->isSuperAdmin (), 'Command did not promote user');

			unset ($user, $output, $role);
		}
	}

