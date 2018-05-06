<?php
/**
 * Created by PhpStorm.
 * User: Longinus
 * Date: 05/06/2018
 * Time: 11:40 AM
 */
namespace PHOC;

final class ClassInit
{
    public function __construct($entity)
    {
        if($entity["Type"] != Annotations::T_CLASS)
            call_user_func($entity["Symbol"]);
    }
}