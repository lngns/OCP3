<?php
/**
 * Created by PhpStorm.
 * User: Longinus
 * Date: 05/07/2018
 * Time: 07:41 AM
 */
namespace PHOC;

/** @PHOC\Annotation(@Method) */
final class Route
{
    public function __construct(array $entity, string $route)
    {
        if($entity["Type"] !== Annotations::T_METHOD)
            throw new \InvalidArgumentException("@Route is applicable only on methods.");
        if(!is_string($route) && $route !== 404)
            throw new \InvalidArgumentException("Route::__construct() expects string as argument 2.");
        if(empty($route))
            throw new \InvalidArgumentException("Route::__construct() expects non-empty string as argument 2.");
        $interface = explode("::", $entity["Symbol"])[0];
        WebInterface::RegisterRoute($interface, $route, $entity["Symbol"]);
    }
}