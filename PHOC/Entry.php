<?php
/**
 * Created by PhpStorm.
 * User: Longinus
 * Date: 05/06/2018
 * Time: 09:19 AM
 */
namespace PHOC;

/** @Annotation(@Method, @Function) */
final class Entry
{
    public function __construct(array $entity)
    {
        if($entity["Type"] === Annotations::T_METHOD)
        {
            $entry = \explode("::", $entity["Symbol"]);
            /** @noinspection PhpUnhandledExceptionInspection -- Symbol existence already checked. */
            $method = new \ReflectionMethod($entry[0], $entry[1]);
            if(!$method->isPublic() || !$method->isStatic())
                throw new \UnexpectedValueException("Entry Point must be a static public function.");
        }
        else
            $entry = $entity["Symbol"];
        Runtime::SetEntryPoint($entry);
    }
}