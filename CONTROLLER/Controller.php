<?php
namespace Tom\Blog\Controller;

class Controller{

    protected $twig;

    public function __construct(){

        $loader = new \Twig_Loader_Filesystem('VIEW/frontend');
        $this->twig = new \Twig_Environment($loader, [
            //'cache' => __DIR__ . '/tmp',
            'debug' => true
        ]);
        $this->twig->addGlobal('session', $_SESSION);
        $this->twig->addExtension(new \Twig_Extension_Debug());
    }

}