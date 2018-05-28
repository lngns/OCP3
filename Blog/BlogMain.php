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
    /** @PHOC\SessionVar */ //Yet, such an annotation system allows it, which I find pretty funny
    static private $LastPage;
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
        self::$LastPage->Set($_SERVER["REQUEST_URI"]);
    }
    static public function Redirect(string $url)
    {
        if($url[0] !== '/')
            $url = '/' . $url;
        \header("Location: " . \PHOC\Configuration::BaseUrl() . $url);
    }
    static public function GoBack(string $else = "/")
    {
        if(self::$LastPage->Get() !== NULL)
            \header("Location: " . self::$LastPage);
        else
            self::Redirect($else);
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
    static public function Error400()
    {
        \header("HTTP/1.0 400 Bad Request");
        echo("Error 400. Bad Request");
    }
    static public function Error403()
    {
        \header("HTTP/1.0 403 Forbidden");
        echo("Error 403. Forbidden");
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
            "CurrentRequest" => $_SERVER["REQUEST_URI"],
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
            $comments = Comment::GetLastComments($id);
            \PHOC\Template::RenderFile("article.html")([
                "CurrentRequest" => $_SERVER["REQUEST_URI"],
                "PageName" => $article->Title,
                "Comments" => $comments,
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
    /** @PHOC\Route("/admin") */
    static public function Admin()
    {
        \header("X-Robots-Tag: noindex,nofollow");
        if(self::$Admin->Get())
            self::AdminArchives(0);
        else
            \PHOC\Template::RenderFile("login.html")([
                "ACP" => false,
                "PageName" => "Login"
            ]);
    }
    /** @PHOC\Route("/admin/{i}") */
    static public function AdminArchives(int $i)
    {
        \header("X-Robots-Tag: noindex,nofollow");
        if(!self::$Admin->Get())
            self::Redirect("/admin/");
        if($i === 0)
            $articles = Article::GetLastArticles(5);
        else
            $articles = Article::GetArticlesFromId($i * 5, 5);
        $lastPage = (int) (Article::GetArticleCount() / 5);
        \PHOC\Template::RenderFile("admin.html")([
            "PageName" => "ACP",
            "ACP" => true,
            "Articles" => $articles,
            "PageId" => $i,
            "LastPageId" => $lastPage
        ]);
    }
    /** @PHOC\Route("/_service/{a}") */
    static public function Service(string $service)
    {
        \header("X-Robots-Tag: noindex,nofollow");
        switch($service)
        {
        case "comment":
            if(!isset($_POST["article_id"], $_POST["author"], $_POST["email"], $_POST["message"]))
            {
                BlogMain::Error400();
                return;
            }
            Comment::WriteComment((int) $_POST["article_id"], $_POST["author"], $_POST["email"], $_POST["message"]);
            BlogMain::GoBack();
            break;
        case "report":
            if(!isset($_GET["comment"]))
            {
                BlogMain::Error400();
                return;
            }
            Report::WriteReport((int) $_GET["comment"]);
            BlogMain::GoBack(); //ToTheFuture
            break;
        case "login":
            if(self::$Admin->Get())
            {
                BlogMain::Redirect("/admin");
                return;
            }
            if(!isset($_POST["user"], $_POST["password"]))
            {
                BlogMain::Error400();
                return;
            }
            try
            {
                $user = User::ReadUserFromName($_POST["user"]);
                if($user->CheckPassword($_POST["password"]))
                {
                    self::$Admin->Set(true);
                    BlogMain::Redirect("/admin");
                }
                else
                    BlogMain::Redirect("/admin?error");
            }
            catch(\InvalidArgumentException $ex)
            {
                BlogMain::Redirect("/admin?error");
            }
            break;
        case "logout":
            self::$Admin->Set(false);
            BlogMain::Redirect("/");
            break;
        case "write":
            if(!self::$Admin->Get())
            {
                BlogMain::Error403();
                return;
            }
            if(!isset($_POST["title"], $_POST["body"]))
            {
                BlogMain::Error400();
                return;
            }
            Article::WriteArticle($_POST["title"], $_POST["body"]);
            break;
        case "edit":
            if(!self::$Admin->Get())
            {
                BlogMain::Error403();
                return;
            }
            if(!isset($_POST["id"], $_POST["title"], $_POST["body"]))
            {
                BlogMain::Error400();
                return;
            }
            Article::EditArticle($_POST["id"], $_POST["title"], $_POST["body"]);
            break;
        case "deleteArticle":
            if(!self::$Admin->Get())
            {
                BlogMain::Error403();
                return;
            }
            if(!isset($_GET["article"]))
            {
                BlogMain::Error400();
                return;
            }
            Article::DeleteArticle((int) $_GET["article"]);
            BlogMain::GoBack();
            break;
        case "deleteComment":
            if(!self::$Admin->Get())
            {
                BlogMain::Error403();
                return;
            }
            if(!isset($_GET["comment"]))
            {
                BlogMain::Error400();
                return;
            }
            Report::DeleteReport((int) $_GET["comment"]);
            //This URI is designed to be accessed via AJAX. The `Ene` flag is there to prevent further requests
            if(!isset($_GET["Ene"]))
                BlogMain::GoBack();
            break;
        default:
            BlogMain::Error404();
        }
    }
}
