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
    static public function Main()
    {
        echo("Hello World!");
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
