<?php
/**
 * Created by PhpStorm.
 * User: Longinus
 * Date: 05/28/2018
 * Time: 12:11 PM
 */
namespace Blog;

final class Report
{
    public $Id;
    public $CommentId;
    public $Date;
    public $Reason;

    //not in SQL table
    public $CommentAuthor;
    public $CommentEmail;
    public $ArticleId;
    public $ArticleTitle;
    private function __construct(int $id, int $comment, string $date, $reason, string $author, string $email, int $article = 0, string $title = "")
    {
        $this->Id = $id; $this->CommentId = $comment;
        $this->Date = $date; $this->Reason = $reason;
        $this->CommentAuthor = $author; $this->CommentEmail = $email;
        $this->ArticleId = $article; $this->ArticleTitle = $title;
    }

    //$reason is unused - I'll implement it maybe
    static public function WriteReport(int $comment, string $reason = NULL)
    {
        $stmt = BlogMain::GetSqlConnection()->prepare("
            INSERT INTO reports (comment_id, date) VALUES (?, NOW())
        ");
        $stmt->bindParam(1, $comment, \PDO::PARAM_INT);
        $stmt->execute();
    }
    static public function ReadReport(int $id): Report
    {
        $stmt = BlogMain::GetSqlConnection()->prepare("
            SELECT reports.id, reports.comment_id, reports.date, reports.reason,
              comments.author, comments.email, articles.id, articles.title
            FROM (reports
              INNER JOIN comments ON reports.comment_id = comments.id)
            INNER JOIN articles ON comments.article_id = articles.id
            WHERE reports.id = ?
        ");
        $stmt->bindParam(1, $id, \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_NUM);
        if($row === NULL)
            throw new \InvalidArgumentException("There is no id " . $id . ".");
        return new Report($row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7]);
    }
    static public function GetAllReports(): array //Report[]
    {
        $query = BlogMain::GetSqlConnection()->query("
            SELECT reports.id, reports.comment_id, reports.date, reports.reason,
              comments.author, comments.email, articles.id, articles.title
            FROM ((reports
              INNER JOIN comments ON reports.comment_id = comments.id)
            INNER JOIN articles ON comments.article_id = articles.id)
            ORDER BY reports.id DESC
        ");
        $batch = [];
        while($row = $query->fetch(\PDO::FETCH_NUM))
            $batch[] = new Report($row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7]);
        return $batch;
    }
    static public function GetLastReportsFrom(int $commentId): array //Report[]
    {
        $stmt = BlogMain::GetSqlConnection()->prepare("
            SELECT reports.id, reports.comment_id, reports.date, reports.reason,
              comments.author, comments.email, articles.id, articles.title
            FROM (reports
              INNER JOIN comments ON reports.comment_id = comments.id)
            INNER JOIN articles ON comments.article_id = articles.id
            WHERE reports.comment_id = ?
            ORDER BY reports.id DESC
        ");
        $stmt->bindParam(1, $commentId, \PDO::PARAM_INT);
        $stmt->execute();
        $batch = [];
        while($row = $stmt->fetch(\PDO::FETCH_NUM))
            $batch[] = new Report($row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7]);
        return $batch;
    }
    static public function DeleteReport(int $id)
    {
        $stmt = BlogMain::GetSqlConnection()->prepare("
            DELETE FROM reports WHERE id = ?
        ");
        $stmt->bindParam(1, $id, \PDO::PARAM_INT);
        $stmt->execute();
    }
}