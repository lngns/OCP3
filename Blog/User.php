<?php
/**
 * Created by PhpStorm.
 * User: Longinus
 * Date: 05/28/2018
 * Time: 01:36 PM
 */
namespace Blog;

class User
{
    public $Id;
    public $User;
    public $Password;
    private function __construct(int $id, string $user, string $password)
    {
        $this->Id = $id;
        $this->User = $user;
        $this->Password = $password;
    }

    static public function ReadUser(int $id): User
    {
        $stmt = BlogMain::GetSqlConnection()->prepare("
            SELECT id, user, password FROM users WHERE id = ?
        ");
        $stmt->bindParam(1, $id, \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_NUM);
        if($row === false)
            throw new \InvalidArgumentException("There is no id " . $id . ".");
        return new User($row[0], $row[1], $row[2]);
    }
    static public function ReadUserFromName(string $name): User
    {
        $stmt = BlogMain::GetSqlConnection()->prepare("
            SELECT id, user, password FROM users WHERE user = ?
        ");
        $stmt->bindParam(1, $name);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_NUM);
        if($row === false)
            throw new \InvalidArgumentException("There is no user " . $name . ".");
        return new User($row[0], $row[1], $row[2]);
    }
    static public function WriteUser(string $username, string $password)
    {
        $stmt = BlogMain::GetSqlConnection()->prepare("
            INSERT INTO users (user, password) VALUES (?, ?)
        ");
        $stmt->bindParam(1, $username);
        $stmt->bindParam(2, \password_hash($password, \PASSWORD_BCRYPT));
        $stmt->execute();
    }
    static public function CheckUserPassword(User $user, string $password): bool
    {
        return \password_verify($password, $user->Password);
    }
    public function CheckPassword(string $password): bool
    {
        return self::CheckUserPassword($this, $password);
    }
}