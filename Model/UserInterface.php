<?php
	namespace FAPerezG\UsersBundle\Model;

	use FOS\UserBundle\Model\UserInterface as BaseUserInterface;

	interface UserInterface extends BaseUserInterface  {
		const LOCALE_DEFAULT = 'es';
		const LOCALE_EN = 'en';
		const LOCALE_ES = 'es';

		public function getFullName ();

		public function getLocale ();

		public function getLocaleName ($userLocale);

		public function setFullName ($fullName);

		public function setLocale ($locale);

		public static function getAvailableLocales ();
	}