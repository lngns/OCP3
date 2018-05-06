<?php
/**
 * Created by PhpStorm.
 * User: Longinus
 * Date: 04/19/2018
 * Time: 09:18 PM
 */
namespace PHOC;

class Runtime
{
    static public function Autoload($class)
    {
        $class = ltrim($class, "\\");
        $file = "";
        if(($lastNsPos = strrpos($class, "\\")) !== false)
        {
            $namespace = substr($class, 0, $lastNsPos);
            $class     = substr($class, $lastNsPos + 1);
            $file  = str_replace("\\", DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $file .= $class . ".php";
        if(file_exists($file))
            include_once($file);
    }
    static public function Start()
    {
        \spl_autoload_register("\\PHOC\\Runtime::Autoload");
        include_once("PHOC" . DIRECTORY_SEPARATOR . "Annotations.php");

        $configuration = \simplexml_load_file("." . DIRECTORY_SEPARATOR . "configuration.xml");
        $entryFile = (string) $configuration->{"entry-file"};
        $resourceDir = (string) $configuration->{"resource-directory"};
        $entryFile = str_replace(array("/", "\\"), DIRECTORY_SEPARATOR, $entryFile);
        $resourceDir = str_replace(array("/", "\\"), DIRECTORY_SEPARATOR, $resourceDir);

        include_once($resourceDir . DIRECTORY_SEPARATOR . $entryFile);
        \BlogMain::Main();

        Annotations::GetAnnotations("\\BlogMain", Annotations::T_CLASS);
        var_dump(Annotations::$List);
    }
}