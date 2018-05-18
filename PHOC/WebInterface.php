<?php
/**
 * Created by PhpStorm.
 * User: Longinus
 * Date: 05/07/2018
 * Time: 07:22 AM
 */
namespace PHOC;

abstract class WebInterface
{
    static private $Interfaces = [];
    static private $ErrorHandler;

    /** @ClassInit */
    static public function __Init()
    {
        if(!self::$ErrorHandler)
        {
            self::$ErrorHandler = function () {
                \header("HTTP/1.0 500 Internal Server Error");
                echo(
                    "500 Internal Server Error; Web Interface Not Found. \n" .
                    "WebInterface::Dispatch() expects class with registered routes as argument 1."
                );
                die();
            };
        }
    }
    static public function RegisterRoute(string $interface, string $route, callable $handler)
    {
        $interface = \ltrim($interface, '\\');
        $route = \preg_quote($route, '#');
        if(isset(self::$Interfaces[$interface]))
            self::$Interfaces[$interface][$route] = $handler;
        else
            self::$Interfaces[$interface] = [$route => $handler];
    }
    static public function RemoveRoute(string $interface, string $route): callable
    {
        if(isset(self::$Interfaces[$interface], self::$Interfaces[$interface][$route]))
        {
            $handler = self::$Interfaces[$interface][$route];
            self::$Interfaces[$interface][$route] = NULL;
            return $handler;
        }
        return NULL;
    }
    static public function RegisterErrorHandler(callable $handler)
    {
        if(!\is_callable($handler))
            throw new \InvalidArgumentException("WebInterface::RegisterErrorHandler() expects callable as argument 1.");
        self::$ErrorHandler = $handler;
    }

    static public function Dispatch(string $interface, string $request = NULL)
    {
        if(!isset(self::$Interfaces[$interface]))
            \call_user_func(self::$ErrorHandler);
        if($request === NULL)
            $request = $_SERVER["REQUEST_URI"];
        if(($pos = \strpos($request, '?')) !== false)
            $request = \substr($request, 0, $pos);
        /** @noinspection PhpUndefinedMethodInspection -- calls Configuration::__callStatic() */
        $request = \substr($request, \strlen(Configuration::BaseUrl()));
        $match = self::Match($request, self::$Interfaces[$interface]);
        if($match["Route"] === 404)
        {
            \header("HTTP/1.0 404 Page Not Found");
            if(!isset(self::$Interfaces[$interface][404]))
                echo("Error 404. Page Not Found.");
            else
                \call_user_func(self::$Interfaces[$interface][404]);
        }
        else
            \call_user_func_array(self::$Interfaces[$interface][$match["Route"]], $match["Params"]);
    }
    static public function Match(string $request, array $routes): array
    {
        foreach($routes as $route => $handler)
        {
            //var_dump($route);
            if($route === "\\*")
                return ["Route" => $route, "Params" => []];
            else if(\strpos($route, '{') === false && \strcmp($route, $request) === 0)
                return ["Route" => $route, "Params" => []];
            else
            {
                $params = [];
                $regex = \strtr($route, [
                    "\\{\\*\\}" => "(.+)",
                    "\\{i\\}" => "([0-9]+)",
                    "\\{a\\}" => "([0-9a-zA-Z_]+)",
                    "\\{\\*\\?\\}" => "(?:.+)",
                    "\\{i\\?\\}" => "(?:[0-9]+)",
                    "\\{a\\?\\}" => "(?:[0-9a-zA-Z_]+)"
                ]);
                if(\preg_match("#^" . $regex . "$#", $request, $params))
                {
                    \array_shift($params);
                    return ["Route" => $route, "Params" => $params];
                }
            }
        }
        return ["Route" => 404, "Params" => []];
    }

    /** @UnitTest */
    static public function __UnitTest()
    {
        assert(self::Match("/foo/bar", ["/foo/bar" => NULL, "/" => NULL]) === ["Route" => "/foo/bar", "Params" => []]);
        assert(self::Match("/foo/bar", ["/" => NULL, "/foo/bar" => NULL]) === ["Route" => "/foo/bar", "Params" => []]);

        assert(self::Match("/foo/bar", ["/" => NULL, "*" => NULL]) === ["Route" => "*", "Params" => []]);
        assert(self::Match("/", ["/" => NULL, "*" => NULL]) === ["Route" => "/", "Params" => []]);
        assert(self::Match("/", ["*" => NULL, "/" => NULL]) === ["Route" => "*", "Params" => []]);

        assert(self::Match("/member/42", ["/member/{i}" => NULL]) === ["Route" => "/member/{i}", "Params" => ["42"]]);
        assert(self::Match("/member/lngns", ["/member/{i}" => NULL]) === ["Route" => 404, "Params" => []]);

        assert(self::Match("/member/lngns.42", ["/member/{a}.{i}" => NULL]) === ["Route" => "/member/{a}.{i}", "Params" => ["lngns", "42"]]);
        assert(self::Match("/foo/aezrzqeRZTВЕКПРХФ/bar", ["/foo/{*}/bar" => NULL]) === ["Route" => "/foo/{*}/bar", "Params" => ["aezrzqeRZTВЕКПРХФ"]]);

        assert(self::Match("/foo/bar", ["/{a?}/{a}" => NULL]) === ["Route" => "/{a?}/{a}", "Params" => ["bar"]]);
    }
}