<?php
/**
 * Created by PhpStorm.
 * User: Longinus
 * Date: 05/06/2018
 * Time: 07:07 AM
 */

class BlogMain
{
    /** @PHOC\Entry */
    static public function Main()
    {
        \session_start();
        \PHOC\WebInterface::Dispatch(BlogMain::class);
    }

    /** @PHOC\Route("/") */
    static public function Index()
    {
        echo("In BlogMain::Index()");
    }
    /** @PHOC\Route("/archives/{*}") */
    static public function Archives()
    {
        echo("In BlogMain::Archives()");
    }
}
