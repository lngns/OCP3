<?php
/**
 * Created by PhpStorm.
 * User: Longinus
 * Date: 05/06/2018
 * Time: 07:07 AM
 * @noinspection PhpUndefinedMethodInspection -- SessionVars not detected
 */
namespace Blog;

class BlogMain
{
    /** @PHOC\SessionVar */
    static private $UserEmail; //PHP doesn't allow non-constant expressions to initialize static fields
                               //Yet, such an annotation system allows it, which I find pretty funny
    static private $Connection;

    /** @PHOC\Entry */
    static public function Main()
    {
        if(\PHOC\Environment::Debug())
            $sql = \PHOC\Configuration::SqlServers("Debug");
        else
            $sql = \PHOC\Configuration::SqlServers("Release");
        self::$Connection = new \PDO(
            $sql->Driver . ":host=" . $sql->Host . ";port=" . $sql->Port .
            ";dbname=" . $sql->Schema . ";charset=" . $sql->Charset,
            $sql->User,
            $sql->Password
        );
        self::$Connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        \session_start();
        \PHOC\WebInterface::Dispatch(BlogMain::class);
    }
    static public function Redirect(string $url)
    {
        if($url[0] !== '/')
            $url = '/' . $url;
        \header("Location: " . \PHOC\Configuration::BaseUrl() . $url);
    }
    static public function GetSqlConnection(): \PDO
    {
        return self::$Connection;
    }

    /** @PHOC\Route("/") */
    static public function Index()
    {
        if(!self::$UserEmail->Get())
            self::$UserEmail->Set("");
        echo("In BlogMain::Index(). Email: " . self::$UserEmail);
        var_dump(Article::ReadArticle(2));
    }
    /** @PHOC\Route("/archives/{i}") */
    static public function Archives(int $i)
    {
        echo("In BlogMain::Archives(). Email: " . self::$UserEmail);
        echo(" Page: " . $i);
    }
    /** @PHOC\Route("/setEmail/{*}") */
    static public function SetEmail(string $email)
    {
        self::$UserEmail->Set($email);
        self::Redirect("/");
    }
}
