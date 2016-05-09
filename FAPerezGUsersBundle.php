<?php

	namespace FAPerezG\UsersBundle;

	use FAPerezG\UsersBundle\DependencyInjection\Compiler\ValidationPass;
	use Symfony\Component\DependencyInjection\ContainerBuilder;
	use Symfony\Component\HttpKernel\Bundle\Bundle;

	class FAPerezGUsersBundle extends Bundle {
		public function build (ContainerBuilder $container) {
			parent::build ($container);
			$container->addCompilerPass (new ValidationPass());
		}

		public function getParent () {
			return 'FOSUserBundle';
		}
	}
