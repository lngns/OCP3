<?php
/**
 * Created by PhpStorm.
 * User: Longinus
 * Date: 05/06/2018
 * Time: 07:07 AM
 */
use \PHOC\Entry;

class BlogMain
{
    /** @PHOC\Entry */
    static public function Main()
    {
        \session_start();
        echo("Hello World!<br />");
        var_dump(\PHOC\WebInterface::$Interfaces);
    }

    /** @PHOC\Route("/") */
    public function Index()
    {
        //...
    }
    /** @PHOC\Route("/archives/*") */
    public function Archives()
    {
        //...
    }
}
