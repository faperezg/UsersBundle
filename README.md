FAPerezGUsersBundle
=============

This bundle extends FOSUserBundle and apply some changes.

Features include:

- Username removed from User entity. Users will be identified by email address.
- Salt removed from User entity. That means only BCrypt is supported as password encoder.
- Only Doctrine is supported (MongoDB and CouchDB included). No Propel support.
- At this moment, only ES and EN translations are provided.

Installation
------------

- Install and configure FOSUserBundle following the documentation (https://symfony.com/doc/1.3.x/bundles/FOSUserBundle/index.html)
- Set bcrypt as pasword encoder
- Download FAPerezGUsersBundle.
- Enable the Bundle **AFTER** FOSUserBundle.
- Create your User entity, extending FAPerezG\UsersBundle\Entity\User
- Change FOSUserBundle configuration values:


	fos_user:
    	user_class: AppBundle\Entity\User

    	profile:
        	form:
            	type:               FAPerezG\UsersBundle\Form\Type\ProfileFormType
            	validation_groups:  [FAPerezGUsers_Profile, Default]

    	registration:
        	form:
            	type:               FAPerezG\UsersBundle\Form\Type\RegistrationFormType
            	validation_groups:  [FAPerezGUsers_Registration, Default]

    	service:
        	user_manager:           faperezg_users.user_manager

License
-------

This bundle is under the MIT license. See the complete license in the bundle:

    Resources/meta/LICENSE