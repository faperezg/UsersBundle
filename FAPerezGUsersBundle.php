<?php

	namespace FAPerezG\UsersBundle;

	use Symfony\Component\HttpKernel\Bundle\Bundle;

	class FAPerezGUsersBundle extends Bundle {
		public function getParent () {
			return 'FOSUserBundle';
		}
	}
