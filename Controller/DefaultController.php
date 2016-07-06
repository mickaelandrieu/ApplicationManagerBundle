<?php

namespace Am\ApplicationManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Am\ApplicationManagerBundle\Utils\ApplicationReporter;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $reporter = new ApplicationReporter($this->get('kernel'));

        dump($reporter->report());
        return $this->render('ApplicationManagerBundle:Default:index.html.twig');
    }
}
