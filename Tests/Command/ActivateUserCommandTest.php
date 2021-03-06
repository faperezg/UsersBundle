<?php
	namespace FAPerezG\UsersBundle\Tests\Command;

	use FAPerezG\UsersBundle\Command\ActivateUserCommand;

	class ActivateUserCommandTest extends AbstractUserCommandTest {
		protected function getCommandClassName () {
			return ActivateUserCommand::class;
		}

		protected function getCommandAlias () {
			return 'fos:user:activate';
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

				$this->assertContains ($message = "User '$registeredEmail' has been activated", $output, 'Activation message should be shown');
				$this->assertEquals (1, $times = substr_count ($output, $message), "Activation message should be shown 1 time. Shown $times times");
				$this->assertEquals (0, $statusCode = $this->commandTester->getStatusCode (), "Status code should be 0. Got $statusCode");

				$user = $this->loadTestUser ();
				$this->assertTrue ($user->isEnabled (), 'Command did not activate user');

				unset ($user, $output);
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
			$this->assertContains ($message = "User '{$this->email}' has been activated", $output, 'Command should activate user');
			$this->assertEquals (1, $times = substr_count ($output, $message), "Activation message should be shown 1 time. Shown $times times");
			$this->assertEquals (0, $statusCode = $this->commandTester->getStatusCode (), "Status code should be 0. Got $statusCode");

			$user = $this->loadTestUser ();
			$this->assertTrue ($user->isEnabled (), 'Command did not activate user');

			unset ($user, $output);
		}
	}