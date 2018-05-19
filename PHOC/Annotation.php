<?php
/**
 * Created by PhpStorm.
 * User: Longinus
 * Date: 05/18/2018
 * Time: 03:49 AM
 */
namespace PHOC;

/** @Annotation(@Class) */
final class Annotation
{
    private $Types;
    public function __construct(array $entity, string... $types)
    {
        $this->Types = $types;
        Annotations::RegisterAnnotationClass($entity["Symbol"], ...$types);
    }
    public function GetTypes(): array //string[]
    {
        return $this->Types;
    }
}