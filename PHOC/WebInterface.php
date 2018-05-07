<?php
/**
 * Created by PhpStorm.
 * User: Longinus
 * Date: 05/07/2018
 * Time: 07:22 AM
 */
namespace PHOC;

class WebInterface
{
    static public $Interfaces = [];
    static public function RegisterRoute($interface, $route, $handler)
    {
        if(isset(self::$Interfaces[$interface]))
            self::$Interfaces[$interface][$route] = $handler;
        else
            self::$Interfaces[$interface] = [$route => $handler];
    }
    static public function RemoveRoute($interface, $route)
    {
        if(isset(self::$Interfaces[$interface], self::$Interfaces[$interface][$route]))
        {
            $handler = self::$Interfaces[$interface][$route];
            self::$Interfaces[$interface][$route] = NULL;
            return $handler;
        }
        return NULL;
    }


}