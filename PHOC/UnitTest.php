<?php
/**
 * Created by PhpStorm.
 * User: Longinus
 * Date: 05/06/2018
 * Time: 11:48 AM
 */
namespace PHOC;

final class UnitTest
{
    public function __construct($entity)
    {
        \var_dump($entity);
        if($entity["Type"] !== Annotations::T_CLASS)
        {
            if(Environment::Debug())
                \call_user_func($entity["Symbol"]);
        }
    }

    static private $_utTest;
    /** @PHOC\UnitTest */
    static public function __dummy()
    {
        self::$_utTest = 42;
    }
    /** @PHOC\UnitTest */
    static public function __unittest()
    {
        assert(self::$_utTest === 42);
    }
}