<?php
/**
 * Created by PhpStorm.
 * User: Longinus
 * Date: 05/06/2018
 * Time: 11:48 AM
 */
namespace PHOC;

/** @Annotation(@Method, @Function) */
final class UnitTest
{
    public function __construct(array $entity)
    {
        if(Environment::Debug())
        {
            $parts = \explode("::", $entity["Symbol"]);
            /** @noinspection PhpUnhandledExceptionInspection */
            $method = new \ReflectionMethod($parts[0], $parts[1]);
            $method->setAccessible(true);
            $method->invoke(NULL);
        }
    }

    static private $_utTest;
    /** @UnitTest */
    static public function __Dummy()
    {
        self::$_utTest = 42;
    }
    /** @UnitTest */
    static public function __UnitTest()
    {
        assert(self::$_utTest === 42);
    }
}