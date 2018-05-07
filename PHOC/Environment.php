<?php
/**
 * Created by PhpStorm.
 * User: Longinus
 * Date: 05/06/2018
 * Time: 11:38 AM
 */
namespace PHOC;

abstract class Environment
{
    static private $Environment;

    /** @PHOC\ClassInit */
    static public function __Init()
    {
        if(!self::$Environment)
        {
            self::$Environment = [];
            foreach(Runtime::GetXmlConfiguration()->environment->add as $value)
                self::$Environment[(string) $value["name"]] = (string) $value;
        }
    }
    static public function __callStatic($name, $arguments)
    {
        if(isset(self::$Environment[$name]))
            return self::$Environment[$name];
        else
            //throw new \UnexpectedValueException("Invalid Environment Index " . $name . ".");
            return NULL;
    }
    static public function __GetEnvironment()
    {
        return self::$Environment;
    }
}