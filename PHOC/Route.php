<?php
/**
 * Created by PhpStorm.
 * User: Longinus
 * Date: 05/07/2018
 * Time: 07:41 AM
 */
namespace PHOC;

/** @Annotation(@Method) */
final class Route
{
    public function __construct(array $entity, string $route)
    {
        if(empty($route))
            throw new \InvalidArgumentException("Route::__construct() expects non-empty string as argument 2.");
        $interface = explode("::", $entity["Symbol"])[0];
        WebInterface::RegisterRoute($interface, $route, $entity["Symbol"]);
    }
}