<?php
	namespace FAPerezG\UsersBundle\CouchDocument;

	use Doctrine\ODM\CouchDB\DocumentManager;
	use FAPerezG\UsersBundle\Doctrine\UserManager as BaseUserManager;
	use FOS\UserBundle\Util\CanonicalizerInterface;
	use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

	class UserManager extends BaseUserManager {
		protected $dm;

		public function __construct (EncoderFactoryInterface $encoderFactory, CanonicalizerInterface $emailCanonicalizer, DocumentManager $dm, $class) {
			parent::__construct ($encoderFactory, $emailCanonicalizer, $dm, $class);
			$this->dm = $dm;
		}
	}