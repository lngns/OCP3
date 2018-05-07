<?php
/**
 * Created by PhpStorm.
 * User: Longinus
 * Date: 05/07/2018
 * Time: 09:55 AM
 */
namespace PHOC;

abstract class Configuration
{
    static private $Configuration;

    /** @PHOC\ClassInit */
    static public function __Init()
    {
        if(!self::$Configuration)
        {
            $configuration = Runtime::GetXmlConfiguration();

            $entryClass = (string) $configuration->{"entry-class"};
            $resourceDirectory = (string) $configuration->{"resource-directory"};
            $entryClass = \str_replace(array("/", "\\"), DIRECTORY_SEPARATOR, $entryClass);
            $resourceDirectory = \str_replace(array("/", "\\"), DIRECTORY_SEPARATOR, $resourceDirectory);

            $baseUrl = (string) $configuration->{"base-url"};
            if($baseUrl[\strlen($baseUrl) - 1] === '/')
                $baseUrl = \substr($baseUrl, 0, \strlen($baseUrl) - 1);

            self::$Configuration = [
                "BaseUrl" => $baseUrl,
                "LogsFile" => (string) $configuration->{"logs-file"},
                "EntryClass" => $entryClass,
                "ResourceDirectory" => $resourceDirectory,
                "DefaultSqlCharset" => (string) $configuration->{"default-sql-charset"},
                "DefaultSqlPort" => (string) $configuration->{"default-sql-port"}
            ];
        }
    }
    static public function __callStatic($name, $arguments)
    {
        if(isset(self::$Configuration[$name]))
            return self::$Configuration[$name];
        else
            throw new \UnexpectedValueException("Invalid Configuration Index " . $name . ".");
    }
}