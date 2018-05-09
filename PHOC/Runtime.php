<?php
/**
 * Created by PhpStorm.
 * User: Longinus
 * Date: 04/19/2018
 * Time: 09:18 PM
 * @noinspection PhpUndefinedMethodInspection -- calls Configuration::__callStatic()
 */
namespace PHOC;

abstract class Runtime
{
    static private $EntryPoint;
    static private $Configuration;

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
    static public function GetXmlConfiguration()
    {
        return self::$Configuration;
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
            /** @noinspection PhpIncludeInspection -- that's an autoloader */
            include_once($file);
            Annotations::GetAnnotations($classname, Annotations::T_CLASS);
        }
    }
    static public function Start()
    {
        \spl_autoload_register("\\PHOC\\Runtime::Autoload");

        $configuration = \simplexml_load_file("." . DIRECTORY_SEPARATOR . "configuration.xml");
        self::$Configuration = $configuration;

        Annotations::RegisterAnnotationToIgnore("noinspection");

        /** @noinspection PhpIncludeInspection -- is supposed to be here. It's not our problem if it isn't */
        include_once(Configuration::ResourceDirectory() . DIRECTORY_SEPARATOR . Configuration::EntryClass() . ".php");
        Annotations::GetAnnotations(Configuration::EntryClass(), Annotations::T_CLASS);

        if(!self::$EntryPoint)
            throw new \RuntimeException("Entry Point not defined.");
        else if(!\is_callable(self::$EntryPoint))
            throw new \RuntimeException("Entry Point not callable.");
        if(PHP_SAPI === "cli" || PHP_SAPI === "cgi" || PHP_SAPI === "cgi-fcgi")
            $_argv = $_SERVER["argv"];
        else
            \parse_str($_SERVER["QUERY_STRING"], $_argv);
        $_argc = \count($_argv);
        $_argv[$_argc] = NULL; //argv[argc] is NULL as per POSIX
        \call_user_func(self::$EntryPoint, $_argc, $_argv, Environment::__GetEnvironment());
    }
}