<?php
namespace GetSky\FrontendModule\Controllers;

use Phalcon\Mvc\Controller;

class IndexController extends Controller
{

    public function indexAction()
    {
        print_r('index');
    }

    public function aboutAction()
    {
        print_r('about');
    }
}