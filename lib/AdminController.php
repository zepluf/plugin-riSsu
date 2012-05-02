<?php

namespace plugins\riSsu;

use Symfony\Component\HttpFoundation\Request;

use plugins\riSimplex\Controller;
use plugins\riPlugin\Plugin;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller{

    public function __construct(){
        parent::__construct();
    }

    public function indexAction(Request $request){
        $this->view->getHelper('php::holder')->add('main', $this->view->render('riSsu::index.php'));
        return $this->render('riSsu::admin_layout');        
    }
    
    public function resetAction(Request $request){
        die('here');
    }
}