<?php
/**
 * Created by PhpStorm.
 * User: Longinus
 * Date: 05/06/2018
 * Time: 07:07 AM
 */
use \PHOC\Entry;

class BlogMain
{
    /** @PHOC\Entry */
    static public function Main($argc, $argv, $env)
    {
        echo("Hello World!<br />");
        echo("Argc: " . $argc . "<br />");
        echo("Argv: ");
        var_dump($argv);
        echo("<br />");
        if($env["Debug"])
            echo("Is in Debug Mode.<br />");
    }
    /**
     * @Route("/")
     * @Foo(1+1, "test")
     */
    static public function Test() {}
}
class AnnotationTest
{
    public $args;
    public function __construct(...$args)
    {
        $this->args = $args;
    }
}
class Route extends AnnotationTest {}
class Foo extends AnnotationTest {}
