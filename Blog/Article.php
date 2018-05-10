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
    public $Body;
    public $Date;
    public $Title;
    public $Abstract;
    public $LastUpdateDate;
    private function __construct(int $id, $body, string $date, string $title, $abstract, string $last)
    {
        $this->Id = $id; $this->Body = $body; $this->Date = $date;
        $this->Title = $title; $this->Abstract = $abstract; $this->LastUpdateDate = $last;
    }

    static public function GetArticleBatch(int $start, int $length): array //Article[]
    {
        $stmt = BlogMain::GetSqlConnection()->prepare(
            "SELECT id, title, abstract, date, last_update_date FROM articles WHERE id >= ? ORDER BY id ASC LIMIT ?"
        );
        $stmt->bindParam(1, $start, \PDO::PARAM_INT);
        $stmt->bindParam(2, $length, \PDO::PARAM_INT);
        $stmt->execute();
        $batch = [];
        while($row = $stmt->fetch(\PDO::FETCH_NUM))
            $batch[] = new Article($row[0], NULL, $row[3], $row[1], $row[2], $row[4]);
        return $batch;
    }
    static public function ReadArticle(int $id): Article
    {
        $stmt = BlogMain::GetSqlConnection()->prepare("SELECT id, title, body, date, last_update_date FROM articles WHERE id = ?");
        $stmt->bindParam(1, $id);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_NUM);
        return new Article($row[0], $row[2], $row[3], $row[1], NULL, $row[4]);
    }
    static public function WriteArticle(string $title, string $body)
    {
        if(\strlen($body) < 251)
            $abstract = $body;
        else
            $abstract = \preg_replace("/\s+?(\S+)?$/", "", substr($body, 0, 251));
        $stmt = BlogMain::GetSqlConnection()->prepare(
            "INSERT INTO articles (title, abstract, body, date) VALUES (?, ?, ?, NOW())"
        );
        $stmt->bindParam(1, $title);
        $stmt->bindParam(2, $abstract);
        $stmt->bindParam(3, $body);
        $stmt->execute();
    }
    static public function UpdateArticle(int $id, string $title, string $body)
    {
        if(\strlen($body) < 251)
            $abstract = $body;
        else
            $abstract = \preg_replace("/\s+?(\S+)?$/", "", substr($body, 0, 251));
        $stmt = BlogMain::GetSqlConnection()->prepare(
            "UPDATE articles SET title=?, abstract=?, body=?, last_update_date=NOW() WHERE id = ?"
        );
        $stmt->bindParam(1, $title);
        $stmt->bindParam(2, $abstract);
        $stmt->bindParam(3, $body);
        $stmt->bindParam(4, $id, \PDO::PARAM_INT);
        $stmt->execute();
    }
}