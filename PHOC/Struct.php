<?php
/**
 * Created by PhpStorm.
 * User: Longinus
 * Date: 05/10/2018
 * Time: 06:01 AM
 */
namespace PHOC;

abstract class Struct
{
    public function __construct(...$args)
    {
        $i = 0;
        foreach($this as $field => $val)
        {
            $this->{$field} = $args[$i];
            ++$i;
        }
    }
}