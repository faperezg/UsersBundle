<?php
	namespace FAPerezG\UsersBundle\DependencyInjection\Compiler;

	use Symfony\Component\DependencyInjection\ContainerBuilder;
	use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
	use Symfony\Component\Config\Resource\FileResource;

	class ValidationPass implements CompilerPassInterface {
		/**
		 * {@inheritDoc}
		 */
		public function process (ContainerBuilder $container) {
			if (!$container->hasParameter ('fos_user.storage')) {
				return;
			}
			$storage = $container->getParameter ('fos_user.storage');
			if ('custom' === $storage) {
				return;
			}
			$validationFile = __DIR__ . '/../../Resources/config/storage-validation/' . $storage . '.xml';
			if ($container->hasDefinition ('validator.builder')) {
				// Symfony 2.5+
				$container->getDefinition ('validator.builder')->addMethodCall ('addXmlMapping', array ($validationFile));
				return;
			}
			// Old method of loading validation
			if (!$container->hasParameter ('validator.mapping.loader.xml_files_loader.mapping_files')) {
				return;
			}
			$files = $container->getParameter ('validator.mapping.loader.xml_files_loader.mapping_files');
			if (is_file ($validationFile)) {
				$files[] = realpath ($validationFile);
				$container->addResource (new FileResource($validationFile));
			}
			$container->setParameter ('validator.mapping.loader.xml_files_loader.mapping_files', $files);
		}
	}