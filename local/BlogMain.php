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
    /** @PHOC\SessionVar */
    static private $userEmail; //PHP doesn't allow non-constant expressions to initialize static fields
                               //Yet, such an annotation system allows it, which I find pretty funny

    /** @PHOC\Entry */
    static public function Main()
    {
        \session_start();
        \PHOC\WebInterface::Dispatch(BlogMain::class);
    }
    static public function Redirect(string $url)
    {
        if($url[0] !== '/')
            $url = '/' . $url;
        \header("Location: " . \PHOC\Configuration::BaseUrl() . $url);
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
    /** @PHOC\Route("/setEmail/{*}") */
    static public function SetEmail(string $email)
    {
        self::$userEmail->Set($email);
        self::Redirect("/");
    }
}
