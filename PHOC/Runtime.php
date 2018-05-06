<?php
/**
 * Created by PhpStorm.
 * User: Longinus
 * Date: 04/19/2018
 * Time: 09:18 PM
 */
namespace PHOC;

abstract class Runtime
{
    static private $EntryPoint;

    static public function SetEntryPoint($entryPoint)
    {
        if(self::$EntryPoint)
            throw new \RuntimeException("Entry Point already defined.");
        self::$EntryPoint = $entryPoint;
    }
    static public function GetEntryPoint()
    {
        return self::$EntryPoint;
    }
    static public function Autoload($classname)
    {
        $class = \ltrim($classname, "\\");
        $file = "";
        if(($lastNsPos = \strrpos($class, "\\")) !== false)
        {
            $namespace = \substr($class, 0, $lastNsPos);
            $class     = \substr($class, $lastNsPos + 1);
            $file  = \str_replace("\\", DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $file .= $class . ".php";
        if(\file_exists($file))
        {
            include_once($file);
            Annotations::GetAnnotations($classname, Annotations::T_CLASS);
        }
    }
    static public function Start()
    {
        \spl_autoload_register("\\PHOC\\Runtime::Autoload");
        include_once("PHOC" . DIRECTORY_SEPARATOR . "Annotations.php");

        $configuration = \simplexml_load_file("." . DIRECTORY_SEPARATOR . "configuration.xml");
        $entryClass = (string) $configuration->{"entry-class"};
        $resourceDir = (string) $configuration->{"resource-directory"};
        $entryClass = \str_replace(array("/", "\\"), DIRECTORY_SEPARATOR, $entryClass);
        $resourceDir = \str_replace(array("/", "\\"), DIRECTORY_SEPARATOR, $resourceDir);

        include_once($resourceDir . DIRECTORY_SEPARATOR . $entryClass . ".php");
        Annotations::GetAnnotations($entryClass, Annotations::T_CLASS);

        if(!self::$EntryPoint)
            throw new \RuntimeException("Entry Point not defined.");
        else if(!\is_callable(self::$EntryPoint))
            throw new \RuntimeException("Entry Point not callable.");
        \call_user_func(self::$EntryPoint); //because PHP
    }
}