<?php
	namespace FAPerezG\UsersBundle\Model;

	use FOS\UserBundle\Model\User as BaseUser;
	use Symfony\Component\Intl\Intl;
	use Symfony\Component\Security\Core\User\EquatableInterface;
	use Symfony\Component\Security\Core\User\UserInterface;

	abstract class User extends BaseUser implements EquatableInterface {
		const LOCALE_DEFAULT = 'es';
		const LOCALE_EN = 'en';
		const LOCALE_ES = 'es';

		protected $id;

		protected $fullName;

		protected $locale;

		public function __construct () {
			parent::__construct ();
			$this->username          = $this->email;
			$this->usernameCanonical = $this->emailCanonical;
			$this->locale            = static::LOCALE_DEFAULT;
		}

		public function getFullName () {
			return $this->fullName;
		}

		public function getLocale () {
			return $this->locale;
		}

		public function getLocaleName ($userLocale) {
			if ((!$this->locale) || (!in_array ($this->locale, static::getAvailableLocales ()))) {
				$this->locale = static::LOCALE_DEFAULT;
			}
			return ucwords (Intl::getLanguageBundle ()->getLanguageName ($this->locale, null, $userLocale));
		}

		public function getUsername () {
			return $this->email;
		}

		public function getUsernameCanonical () {
			return $this->emailCanonical;
		}

		public function setFullName ($fullName) {
			$this->fullName = $fullName;
			return $this;
		}

		public function setLocale ($locale) {
			$this->locale = ($locale) && (in_array ($locale, static::getAvailableLocales ())) ? $locale : static::LOCALE_DEFAULT;
			return $this;
		}

		public function setUsername ($username) {
			return $this;
		}

		public function setUsernameCanonical ($usernameCanonical) {
			return $this;
		}

		public function __toString () {
			return (string) $this->getEmail ();
		}

		public function serialize () {
			return serialize (array (
				$this->password,
				$this->expired,
				$this->locked,
				$this->credentialsExpired,
				$this->enabled,
				$this->id,
				$this->expiresAt,
				$this->credentialsExpireAt,
				$this->email,
				$this->emailCanonical,
			));
		}

		public function unserialize ($serialized) {
			$data = unserialize ($serialized);
			// add a few extra elements in the array to ensure that we have enough keys when unserializing
			// older data which does not include all properties.
			$data = array_merge ($data, array_fill (0, 2, null));

			list(
				$this->password,
				$this->expired,
				$this->locked,
				$this->credentialsExpired,
				$this->enabled,
				$this->id,
				$this->expiresAt,
				$this->credentialsExpireAt,
				$this->email,
				$this->emailCanonical
				) = $data;
		}

		public static function getAvailableLocales () {
			return [
				static::LOCALE_EN,
				static::LOCALE_ES,
			];
		}

		public function isEqualTo (UserInterface $user) {
			if (!($user instanceof User)) {
				return false;
			}
			if ($this->getLocale () != $user->getLocale ()) {
				return false;
			}
			// Check that the roles are the same, in any order
			if (count ($this->getRoles ()) != count ($user->getRoles ())) {
				return false;
			}
			foreach ($this->getRoles () as $role) {
				if (!in_array ($role, $user->getRoles ())) {
					return false;
				}
			}
			return true;
		}
	}