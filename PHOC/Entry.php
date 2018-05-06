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
    public function __construct($entity, ...$args)
    {
        Runtime::SetEntryPoint($entity["Symbol"]);
    }
}