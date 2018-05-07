<?php
/**
 * Created by PhpStorm.
 * User: Longinus
 * Date: 05/06/2018
 * Time: 09:19 AM
 */
namespace PHOC;

final class Entry
{
    public function __construct($entity)
    {
        if($entity["Type"] === Annotations::T_CLASS)
            $func = [$entity["Symbol"], "Main"];
        if($entity["Type"] === Annotations::T_METHOD || isset($func))
        {
            if(isset($func))
                $entry = $func;
            else
                $entry = \explode("::", $entity["Symbol"]);
            if(\class_exists($entry[0]) && \method_exists($entry[0], $entry[1]))
            {
                /** @noinspection PhpUnhandledExceptionInspection -- No throwing paths can possibly be followed */
                $method = new \ReflectionMethod($entry[0], $entry[1]);
                if(!$method->isPublic() || !$method->isStatic())
                    throw new \UnexpectedValueException("Entry Point must be a function or a class having a Main method.");
            }
            else
                throw new \UnexpectedValueException("Entry Point must be a function or a class having a Main method.");
        }
        else
            $entry = $entity["Symbol"];
        Runtime::SetEntryPoint($entry);
    }
}