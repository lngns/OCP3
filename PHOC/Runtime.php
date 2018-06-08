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

    static public function SetEntryPoint(callable $entryPoint)
    {
        if(self::$EntryPoint)
            throw new \RuntimeException("Entry Point already defined.");
        self::$EntryPoint = $entryPoint;
    }
    static public function GetEntryPoint(): callable
    {
        return self::$EntryPoint;
    }
    static public function GetXmlConfiguration(): \SimpleXMLElement
    {
        return self::$Configuration;
    }
    static public function CurrentRequest(): string
    {
        return $_SERVER["REQUEST_URI"];
    }
    /** @throws AnnotationException */
    static public function Autoload(string $classname)
    {
        $class = \ltrim($classname, "\\");
        $file = ".." . DIRECTORY_SEPARATOR;
        if(($lastNsPos = \strrpos($class, "\\")) !== false)
        {
            $namespace = \substr($class, 0, $lastNsPos);
            $class     = \substr($class, $lastNsPos + 1);
            $file     .= \str_replace("\\", DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
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
        \error_reporting(E_ALL);
        \ini_set("display_errors", 1);
        \spl_autoload_register("\\PHOC\\Runtime::Autoload");

        $configuration = \simplexml_load_file(".." . DIRECTORY_SEPARATOR . "configuration.xml");
        self::$Configuration = $configuration;

        /** @noinspection PhpUnhandledExceptionInspection -- no AnnotationExceptions are thrown by the framework itself */
        self::Autoload(Configuration::EntryClass());

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

        try
        {
            \call_user_func(self::$EntryPoint, $_argc, $_argv, Environment::__GetEnvironment());
        }
        catch(\Throwable $ex)
        {
            try
            {
                Template::ResetBuffer();
                \header("HTTP/1.0 500 Internal Server Error");
                if(Environment::Debug())
                {
                    Template::RenderFile(Configuration::ErrorHandlerTemplate())([
                        "File" => $ex->getFile(),
                        "Line" => $ex->getLine(),
                        "Class" => get_class($ex),
                        "Message" => $ex->getMessage(),
                        "StackTrace" => $ex->getTrace()
                    ]);
                }
                else
                    echo("500. Internal Server Error");
            }
            catch(\Throwable $ex)
            {
                Template::ResetBuffer();
                //var_dump($ex);
                die("The program has encountered a serious error.");
            }
        }
    }
}