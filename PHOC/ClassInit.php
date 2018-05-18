<?php
/**
 * Created by PhpStorm.
 * User: Longinus
 * Date: 05/06/2018
 * Time: 11:40 AM
 */
namespace PHOC;

/** @PHOC\Annotation(@Method) */
final class ClassInit
{
    public function __construct($entity)
    {
        \call_user_func($entity["Symbol"]);
    }
}