<?php
/**
 * Created by PhpStorm.
 * User: Longinus
 * Date: 05/06/2018
 * Time: 07:07 AM
 * @noinspection PhpUndefinedMethodInspection -- SessionVars not detected
 */

class BlogMain
{
    /** @PHOC\SessionVar("email") */
    static public $userEmail;

    /** @PHOC\Entry */
    static public function Main()
    {
        \session_start();
        \PHOC\WebInterface::Dispatch(BlogMain::class);
    }

    /** @PHOC\Route("/") */
    static public function Index()
    {
        if(!self::$userEmail->Get())
            self::$userEmail->Set("");
        echo("In BlogMain::Index(). Email: " . self::$userEmail);
    }
    /** @PHOC\Route("/archives/{*}") */
    static public function Archives()
    {
        echo("In BlogMain::Archives(). Email: " . self::$userEmail);
    }
}
