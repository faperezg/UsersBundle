<?php
	namespace FAPerezG\UsersBundle\Tests\Command;

	use FAPerezG\UsersBundle\Command\CreateUserCommand;

	class CreateUserCommandTest extends AbstractUserCommandTest {
		protected function getCommandClassName () {
			return CreateUserCommand::class;
		}

		protected function getCommandAlias () {
			return 'fos:user:create';
		}

		public function testInteractiveExecution () {
			$notAnEmail = 'jack';
			$notALocale = 'spanish';
			$empty      = '';
			$stream     = $this->getInputStream ("$empty\n$notAnEmail\n{$this->email}\n$empty\n12345678\n$empty\nJack Sparrow\n$notALocale\nen\n");
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

				$this->assertContains ($message = 'Please choose a password: ', $output, 'Command should ask for password');
				$this->assertEquals (2, $times = substr_count ($output, $message), "Command should ask for password 2 times. Got $times");
				$this->assertContains ($message = 'Password can not be empty', $output, 'Command should reject empty password');
				$this->assertEquals (1, $times = substr_count ($output, $message), "Empty password message should be shown 1 time. Shown $times times");

				$this->assertContains ($message = 'Please choose the full name: ', $output, 'Command should ask for full name');
				$this->assertEquals (2, $times = substr_count ($output, $message), "Command should ask for full name 2 times. Got $times");
				$this->assertContains ($message = 'Full name can not be empty', $output, 'Command should reject empty full name');
				$this->assertEquals (1, $times = substr_count ($output, $message), "Empty full name message should be shown 1 time. Shown $times times");

				$this->assertContains ($message = 'Please choose the locale: ', $output, 'Command should ask for locale');
				$this->assertEquals (2, $times = substr_count ($output, $message), "Command should ask for locale 2 times. Got $times");
				$this->assertContains ($message = "Locale $notALocale is invalid", $output, 'Command should reject invalid locale');
				$this->assertEquals (1, $times = substr_count ($output, $message), "Invalid locale message should be shown 1 time. Shown $times times");

				$this->assertContains ($message = "Created user {$this->email}", $output, 'Command should create user');
				$this->assertEquals (1, $times = substr_count ($output, $message), "Creation message should be shown 1 time. Shown $times times");
				$this->assertEquals (0, $statusCode = $this->commandTester->getStatusCode (), "Status code should be 0. Got $statusCode");

				unset ($output);
			} catch (\Exception $ie) {
				$e = $ie;
			} finally {
				fclose ($stream);
			}
			unset ($stream, $empty, $notALocale, $notAnEmail);
			if ($e) {
				throw $e;
			}
		}

		public function testNonInteractiveExecution () {
			$this->commandTester->execute ([
				'command'       => $this->command->getName (),
				'email'         => $this->email,
				'password'      => 12345678,
				'full-name'     => 'Jack Sparrow',
				'locale'        => 'en',
				'--inactive'    => true,
				'--super-admin' => true,
			]);
			$output = $this->commandTester->getDisplay ();

			$this->assertContains ($message = "Created user {$this->email}", $output, 'Command should create user');
			$this->assertEquals (1, $times = substr_count ($output, $message), "Creation message should be shown 1 time. Shown $times times");
			$this->assertEquals (0, $statusCode = $this->commandTester->getStatusCode (), "Status code should be 0. Got $statusCode");

			unset ($output);
		}
	}