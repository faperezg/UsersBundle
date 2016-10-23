<?php
	namespace FAPerezG\UsersBundle\Tests\Command;

	use FAPerezG\UsersBundle\Model\UserInterface;
	use FAPerezG\UsersBundle\Tests\DummyUserManager;
	use Symfony\Bundle\FrameworkBundle\Console\Application;
	use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
	use Symfony\Component\Console\Command\Command;
	use Symfony\Component\Console\Helper\QuestionHelper;
	use Symfony\Component\Console\Tester\CommandTester;

	abstract class AbstractUserCommandTest extends KernelTestCase {
		/* Test parameters */
		protected $email = 'jack@example.org';
		/** @var Command */
		protected $command;
		/** @var QuestionHelper */
		protected $helper;
		/** @var CommandTester */
		protected $commandTester;
		/** @var DummyUserManager */
		protected $userManager;

		protected function getInputStream ($input) {
			$stream = fopen ('php://memory', 'r+', false);
			fputs ($stream, $input);
			rewind ($stream);
			return $stream;
		}

		/**
		 * @return UserInterface
		 */
		protected function loadTestUser () {
			return $this->userManager->findUserByEmail ($this->email);
		}

		protected function saveTestUser ($role) {
			$user = $this->userManager->findUserByEmail ($this->email);
			$user->addRole ($role);
			$this->userManager->updateUser ($user);
		}

		protected function setUp () {
			gc_enable ();
			parent::setUp ();
			$commandClass = $this->getCommandClassName ();
			if ((!$commandClass) || (!class_exists ($commandClass))) {
				$this->markTestSkipped ('Method getCommandClass does not return a valid class name');
			}
			$command = new $commandClass ();
			if ((!$command) || (!($command instanceof Command))) {
				$this->markTestSkipped ("Can not create an instance of $commandClass or it is not an instance of Symfony\\Component\\Console\\Command\\Command");
			}
			$commandAlias = $this->getCommandAlias ();
			if (!$commandAlias) {
				$this->markTestSkipped ('Method getCommandClass does not return a valid alias');
			}

			self::bootKernel ();
			$application = new Application (self::$kernel);
			$application->add ($command);
			$this->command       = $application->find ($commandAlias);
			$this->helper        = $this->command->getHelper ('question');
			$this->commandTester = new CommandTester ($this->command);
			$container           = self::$kernel->getContainer ();
			$this->userManager   = DummyUserManager::getInstance ($container);
			$container->set ('faperezg_users.user_manager', $this->userManager);
		}

		protected function tearDown () {
			unset ($this->userManager, $this->commandTester, $this->helper, $this->command);
			self::ensureKernelShutdown ();
			parent::tearDown ();
			gc_collect_cycles ();
		}

		abstract protected function getCommandClassName ();

		abstract protected function getCommandAlias ();
	}