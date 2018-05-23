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
    static private $Admin;  //PHP doesn't allow non-constant expressions to initialize static fields
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
        BlogMain::Archives(0);
    }
    /** @PHOC\Route(404) */
    static public function Error404()
    {
        \header("HTTP/1.0 404 Page Not Found");
        echo("Error 404. Page Not Found");
    }
    /** @PHOC\Route("/archives/{i}") */
    static public function Archives(int $i)
    {
        if($i === 0)
            $articles = Article::GetLastArticles(5);
        else
            $articles = Article::GetArticlesFromId($i * 5, 5);
        $lastPage = (int) (Article::GetArticleCount() / 5);
        \PHOC\Template::RenderFile("archives.html")([
            "PageName" => !$i ? "Acceuil" : "Archives",
            "ACP" => false,
            "Articles" => $articles,
            "PageId" => $i,
            "LastPageId" => $lastPage
        ]);
    }
    /** @PHOC\Route("/article/{*?}.{i}") */
    static public function Article(int $id)
    {
        try
        {
            $limits = Article::GetLimits();
            $article = Article::ReadArticle($id);
            \PHOC\Template::RenderFile("article.html")([
                "CurrentRequest" => $_SERVER["REQUEST_URI"],
                "PageName" => $article->Title,
                "Article" => $article,
                "Limits" => $limits,
                "ACP" => false
            ]);
        }
        catch(\InvalidArgumentException $ex)
        {
            BlogMain::Error404();
        }
    }
    /** @PHOC\Route("/article/{*?}.{i}/next") */
    static public function ArticleNext(int $id)
    {
        if($next = Article::GetNextId($id))
        {
            $title = \preg_replace("/\s/", "-", $next["title"]);
            self::Redirect("/article/" . $title . "." . $next["id"]);
        }
        else
            self::Error404();
    }
    /** @PHOC\Route("/article/{*?}.{i}/previous") */
    static public function ArticlePrevious(int $id)
    {
        if($next = Article::GetPreviousId($id))
        {
            $title = \preg_replace("/\s/", "-", $next["title"]);
            self::Redirect("/article/" . $title . "." . $next["id"]);
        }
        else
            self::Error404();
    }
    /** @PHOC\Route("/admin/") */
    static public function Admin()
    {
        if(self::$Admin->Get())
            echo("logged in");
        else
            echo("login form");
    }
    /** @PHOC\Route("/admin/{i}") */
    static public function AdminArchives(int $id)
    {
        if(!self::$Admin->Get())
            self::Redirect("/admin/");
        echo("acp");
    }
    /** @PHOC\Route("/_service/{a}") */
    static public function Service(string $service)
    {
        switch($service)
        {
        case "login":
            echo("login");
            break;
        case "logout":
            echo("logout");
            break;
        case "write":
            echo("write");
            break;
        case "edit":
            echo("edit");
            break;
        case "comment":
            echo("comment");
            break;
        case "report":
            echo("report");
            break;
        case "deleteArticle":
            echo("deleteArticle");
            break;
        case "deleteComment":
            echo("deleteComment");
            break;
        default:
            BlogMain::Error404();
        }
    }
}
