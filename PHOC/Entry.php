<?php
/**
 * Created by PhpStorm.
 * User: Longinus
 * Date: 05/06/2018
 * Time: 09:19 AM
 */
namespace PHOC;

class Entry
{
    public function __construct($entity, ...$args)
    {
        echo(PHP_EOL . "<br />Entry Point is " . $entity["Symbol"] . PHP_EOL . "<br />");
    }
}