<?php
	namespace FAPerezG\UsersBundle\Tests\Command;

	use FAPerezG\UsersBundle\Command\DeactivateUserCommand;

	class DeactivateUserCommandTest extends AbstractUserCommandTest {
		protected function getCommandClassName () {
			return DeactivateUserCommand::class;
		}

		protected function getCommandAlias () {
			return 'fos:user:deactivate';
		}

		public function testInteractiveExecution () {
			$registeredEmail = $this->email;
			$notAnEmail      = 'jack';
			$empty           = '';
			$stream          = $this->getInputStream ("$empty\n$notAnEmail\n$registeredEmail");
			$e               = null;
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

				$this->assertContains ($message = "User '$registeredEmail' has been deactivated", $output, 'Command should deactivate user');
				$this->assertEquals (1, $times = substr_count ($output, $message), "Deactivation message should be shown 1 time. Shown $times times");
				$this->assertEquals (0, $statusCode = $this->commandTester->getStatusCode (), "Status code should be 0. Got $statusCode");

				$user = $this->loadTestUser ();
				$this->assertFalse ($user->isEnabled (), 'Command did not deactivate user');

				unset ($output);
			} catch (\Exception $ie) {
				$e = $ie;
			} finally {
				fclose ($stream);
			}
			unset ($stream, $empty, $notAnEmail, $registeredEmail);
			if ($e) {
				throw $e;
			}
		}

		public function testNonInteractiveExecution () {
			$this->commandTester->execute ([
				'command' => $this->command->getName (),
				'email'   => $this->email,
			]);
			$output = $this->commandTester->getDisplay ();

			$this->assertContains ($message = "User '{$this->email}' has been deactivated", $output, 'Command should deactivate user');
			$this->assertEquals (1, $times = substr_count ($output, $message), "Deactivation message should be shown 1 time. Shown $times times");
			$this->assertEquals (0, $statusCode = $this->commandTester->getStatusCode (), "Status code should be 0. Got $statusCode");

			$user = $this->loadTestUser ();
			$this->assertFalse ($user->isEnabled (), 'Command did not deactivate user');

			unset ($output);
		}
	}