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

    /** @ClassInit */
    static public function __Init()
    {
        if(!self::$Configuration)
        {
            $configuration = Runtime::GetXmlConfiguration();

            $entryClass = (string) $configuration->{"entry-class"};
            $resourceDirectory = (string) $configuration->{"resource-directory"};
            $resourceDirectory = \str_replace(array("/", "\\"), DIRECTORY_SEPARATOR, $resourceDirectory);

            $baseUrl = (string) $configuration->{"base-url"};
            if($baseUrl[\strlen($baseUrl) - 1] === '/')
                $baseUrl = \substr($baseUrl, 0, \strlen($baseUrl) - 1);

            $databases = [];
            if(isset($configuration->sqlserver))
            {
                foreach($configuration->sqlserver as $server)
                {
                    if(isset($server["ignore-if"]) && Environment::{(string) $server["ignore-if"]}() !== NULL)
                        continue;
                    if(isset($server["only-if"]) && Environment::{(string) $server["only-if"]}() === NULL)
                        continue;

                    $id = (string) $server["id"];
                    if(isset($server["conf"]))
                    {
                        $server = \simplexml_load_file(\str_replace(["/", "\\"], DIRECTORY_SEPARATOR, $server["conf"]));
                        $server = $server->{"sqlserver"};
                    }
                    $port = isset($server->{"port"}) ? $server->{"port"} : $configuration->{"default-sql-port"};
                    $charset = isset($server->{"charset"}) ? $server->{"charset"} : $configuration->{"default-sql-charset"};
                    $databases[$id] = new class(
                        (string) $server->{"host"},
                        (string) $port,
                        (string) $server->{"driver"},
                        (string) $server->{"schema"},
                        (string) $charset,
                        (string) $server->{"user"},
                        (string) $server->{"password"}
                    ) extends Struct {
                        public $Host;
                        public $Port;
                        public $Driver;
                        public $Schema;
                        public $Charset;
                        public $User;
                        public $Password;
                    };
                }
            }

            self::$Configuration = [
                "BaseUrl" => $baseUrl,
                "LogsFile" => (string) $configuration->{"logs-file"},
                "EntryClass" => $entryClass,
                "ResourceDirectory" => $resourceDirectory,
                "DefaultSqlCharset" => (string) $configuration->{"default-sql-charset"},
                "DefaultSqlPort" => (string) $configuration->{"default-sql-port"},
                "SqlServers" => $databases
            ];
        }
    }
    static public function __callStatic($name, $arguments)
    {
        if($name === "SqlServers" && isset($arguments[0]))
        {
            if(isset(self::$Configuration["SqlServers"][$arguments[0]]))
                return self::$Configuration["SqlServers"][$arguments[0]];
            else
                throw new \UnexpectedValueException("Invalid SqlServer Id: " . $name . ".");
        }
        if(isset(self::$Configuration[$name]))
            return self::$Configuration[$name];
        else
            throw new \UnexpectedValueException("Invalid Configuration Index: " . $name . ".");
    }
}