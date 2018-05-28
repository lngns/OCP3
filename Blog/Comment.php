<?php
/**
 * Created by PhpStorm.
 * User: Longinus
 * Date: 05/23/2018
 * Time: 05:13 PM
 */
namespace Blog;

class Comment
{
    public $Id;
    public $ArticleId;
    public $Date;
    public $Author;
    public $Email;
    public $Message;
    public $Notification;
    public $ReplyTo;
    private function __construct(int $id, int $articleId, string $date, string $author, string $email, string $message, bool $notif, int $replyTo)
    {
        $this->Id = $id; $this->ArticleId = $articleId; $this->Date = $date; $this->Author = $author;
        $this->Email = $email; $this->Message = $message; $this->Notification = $notif; $this->ReplyTo = $replyTo;
    }

    static public function WriteComment(int $article, string $author, string $email, string $message, bool $notif = false, int $replyTo = 0)
    {
        $stmt = BlogMain::GetSqlConnection()->prepare("
            INSERT INTO comments (article_id, date, author, email, message, notification, reply_to) VALUES (?, NOW(), ?, ?, ?, ?, ?)
        ");
        $stmt->bindParam(1, $article, \PDO::PARAM_INT);
        $stmt->bindParam(2, $author);
        $stmt->bindParam(3, $email);
        $stmt->bindParam(4, $message);
        $stmt->bindParam(5, $notif, \PDO::PARAM_BOOL);
        $stmt->bindParam(6, $replyTo, \PDO::PARAM_INT);
        $stmt->execute();
    }
    static public function ReadComment(int $id): Comment
    {
        $stmt = BlogMain::GetSqlConnection()->prepare(
            "SELECT id, article_id, date, author, email, message, notification, reply_to FROM comments WHERE id = ?"
        );
        $stmt->bindParam(1, $id, \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_NUM);
        if($row === false)
            throw new \InvalidArgumentException("There is no id " . $id . ".");
        return new Comment($row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7]);
    }
    static public function GetLastComments(int $article): array //Comment[]
    {
        $stmt = BlogMain::GetSqlConnection()->prepare("
            SELECT id, article_id, date, author, email, message, notification, reply_to FROM comments
            WHERE article_id = ? ORDER BY id DESC
        ");
        $stmt->bindParam(1, $article, \PDO::PARAM_INT);
        $stmt->execute();
        $batch = [];
        while($row = $stmt->fetch(\PDO::FETCH_NUM))
            $batch[] = new Comment($row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7]);
        return $batch;
    }
    static public function DeleteComment(int $id)
    {
        $stmt = BlogMain::GetSqlConnection()->prepare(
            "DELETE FROM comments WHERE id = ?"
        );
        $stmt->bindParam(1, $id, \PDO::PARAM_INT);
        $stmt->execute();
    }
}