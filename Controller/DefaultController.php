<?php

namespace FAPerezG\UsersBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('FAPerezGUsersBundle:Default:index.html.twig');
    }
}
