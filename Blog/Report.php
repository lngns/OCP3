<?php
/**
 * Created by PhpStorm.
 * User: Longinus
 * Date: 05/28/2018
 * Time: 12:11 PM
 */
namespace Blog;

class Report
{
    public $Id;
    public $CommentId;
    public $Date;
    public $Reason;
    private function __construct(int $id, int $comment, string $date, string $reason)
    {
        $this->Id = $id; $this->CommentId = $comment;
        $this->Date = $date; $this->Reason = $reason;
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
            SELECT id, comment_id, date, reason FROM reports WHERE id = ?
        ");
        $stmt->bindParam(1, $id, \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_NUM);
        if($row === NULL)
            throw new \InvalidArgumentException("There is no id " . $id . ".");
        return new Report($row[0], $row[1], $row[2], $row[3]);
    }
    static public function GetAllReports(): array //Report[]
    {
        $query = BlogMain::GetSqlConnection()->query("
            SELECT id, comment_id, date, reason FROM reports ORDER BY id DESC
        ");
        $batch = [];
        while($row = $query->fetch(\PDO::FETCH_NUM))
            $batch[] = new Report($row[0], $row[1], $row[2], $row[3]);
        return $batch;
    }
    static public function GetLastReportsFrom(int $commentId): array //Report[]
    {
        $stmt = BlogMain::GetSqlConnection()->prepare("
            SELECT id, comment_id, date, reason FROM reports WHERE comment_id = ? ORDER BY id DESC
        ");
        $stmt->bindParam(1, $commentId, \PDO::PARAM_INT);
        $stmt->execute();
        $batch = [];
        while($row = $stmt->fetch(\PDO::FETCH_NUM))
            $batch[] = new Report($row[0], $row[1], $row[2], $row[3]);
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