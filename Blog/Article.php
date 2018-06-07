<?php
/**
 * Created by PhpStorm.
 * User: Longinus
 * Date: 05/09/2018
 * Time: 07:08 AM
 */
namespace Blog;

final class Article
{
    public $Id;
    public $Title;
    public $Abstract;
    public $Body;
    public $Date;
    public $LastUpdateDate;
    public $Published;
    private function __construct(int $id, string $title, $abstract, $body, string $date, $last, bool $published)
    {
        $this->Id = $id; $this->Title = $title; $this->Abstract = $abstract;
        $this->Body = $body; $this->Date = $date; $this->LastUpdateDate = $last;
        $this->Published = $published;
    }

    static public function GetLastArticles(int $count, bool $includeUnpublished = false): array //Article[]
    {
        $stmt = BlogMain::GetSqlConnection()->prepare("
            SELECT id, title, abstract, date, last_update_date, published FROM articles
            " . (!$includeUnpublished ? "WHERE published = 1" : "") . " ORDER BY id DESC LIMIT ?
        ");
        $stmt->bindParam(1, $count, \PDO::PARAM_INT);
        $stmt->execute();
        $batch = [];
        while($row = $stmt->fetch(\PDO::FETCH_NUM))
            $batch[] = new Article($row[0], $row[1], $row[2], NULL, $row[3], $row[4], $row[5]);
        return $batch;
    }
    static public function GetArticlesFromId(int $start, int $count, bool $includeUnpublished = false): array //Article[]
    {
        $stmt = BlogMain::GetSqlConnection()->prepare("
            SELECT id, title, abstract, date, last_update_date, published FROM articles
            WHERE id <= (SELECT MAX(id) - ? FROM articles " . (!$includeUnpublished ? "WHERE published = 1" : "") . ")
            " . (!$includeUnpublished ? "AND published = 1" : "") . " ORDER BY id DESC LIMIT ?
        ");
        $stmt->bindParam(1, $start, \PDO::PARAM_INT);
        $stmt->bindParam(2, $count, \PDO::PARAM_INT);
        $stmt->execute();
        $batch = [];
        while($row = $stmt->fetch(\PDO::FETCH_NUM))
            $batch[] = new Article($row[0], $row[1], $row[2], NULL, $row[3], $row[4], $row[5]);
        return $batch;
    }
    static public function GetArticleCount(bool $includeUnpublished = false): int
    {
        return BlogMain::GetSqlConnection()->query(
            "SELECT COUNT(*) FROM articles WHERE published = " . ((int) !$includeUnpublished)
        )->fetch()["COUNT(*)"];
    }
    static public function GetNextId(int $id) //: int?
    {
        $stmt = BlogMain::GetSqlConnection()->prepare("
            SELECT id, title FROM articles WHERE id > ? AND published = 1 LIMIT 1
        ");
        $stmt->bindParam(1, $id, \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        if($row)
            return $row;
        return NULL;
    }
    static public function GetPreviousId(int $id) //: int?
    {
        $stmt = BlogMain::GetSqlConnection()->prepare("
            SELECT id, title FROM articles WHERE id < ? AND published = 1 ORDER BY id DESC LIMIT 1
        ");
        $stmt->bindParam(1, $id, \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        if($row)
            return $row;
        return NULL;
    }
    static public function GetLimits(bool $includeUnpublished = false)
    {
        $res = BlogMain::GetSqlConnection()->query("
            SELECT MAX(id), MIN(id) FROM articles " . (!$includeUnpublished ? "WHERE published = 1" : "") . "
        ")->fetchAll()[0];
        return new class((int) $res["MAX(id)"], (int) $res["MIN(id)"]) extends \PHOC\Struct
        {
            public $Max;
            public $Min;
        };
    }
    static public function ReadArticle(int $id): Article
    {
        $stmt = BlogMain::GetSqlConnection()->prepare(
            "SELECT id, title, body, date, last_update_date FROM articles WHERE id = ?"
        );
        $stmt->bindParam(1, $id, \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_NUM);
        if($row === false)
            throw new \InvalidArgumentException("There is no id " . $id . ".");
        return new Article($row[0], $row[1], NULL, $row[2], $row[3], $row[4], true);
    }
    static public function WriteArticle(string $title, string $body, bool $publish)
    {
        if(\strlen($body) < 751)
            $abstract = $body;
        else
            $abstract = \preg_replace("/\s+?(\S+)?$/", "", substr($body, 0, 751));
        $stmt = BlogMain::GetSqlConnection()->prepare(
            "INSERT INTO articles (title, abstract, body, published, date) VALUES (?, ?, ?, ?, NOW())"
        );
        $stmt->bindParam(1, $title);
        $stmt->bindParam(2, $abstract);
        $stmt->bindParam(3, $body);
        $stmt->bindParam(4, $publish, \PDO::PARAM_BOOL);
        $stmt->execute();
    }
    static public function EditArticle(int $id, string $title, string $body)
    {
        if(\strlen($body) < 751)
            $abstract = $body;
        else
            $abstract = \preg_replace("/\s+?(\S+)?$/", "", substr($body, 0, 751));
        $stmt = BlogMain::GetSqlConnection()->prepare(
            "UPDATE articles SET title=?, abstract=?, body=?, last_update_date=NOW() WHERE id = ?"
        );
        $stmt->bindParam(1, $title);
        $stmt->bindParam(2, $abstract);
        $stmt->bindParam(3, $body);
        $stmt->bindParam(4, $id, \PDO::PARAM_INT);
        $stmt->execute();
    }
    static public function DeleteArticle(int $id)
    {
        $stmt = BlogMain::GetSqlConnection()->prepare(
            "DELETE FROM articles WHERE id = ?"
        );
        $stmt->bindParam(1, $id, \PDO::PARAM_INT);
        $stmt->execute();
    }
    static public function PublishArticle(int $id)
    {
        $stmt = BlogMain::GetSqlConnection()->prepare("
            UPDATE articles SET published=1 WHERE id = ?
        ");
        $stmt->bindParam(1, $id, \PDO::PARAM_INT);
        $stmt->execute();
    }
    static public function UnpublishArticle(int $id)
    {
        $stmt = BlogMain::GetSqlConnection()->prepare("
            UPDATE articles SET published=0 WHERE id = ?
        ");
        $stmt->bindParam(1, $id, \PDO::PARAM_INT);
        $stmt->execute();
    }
    //Convenience Function
    static public function GetLastReports(int $id): array //Report[]
    {
        return Report::GetLastReportsFrom($id);
    }
}