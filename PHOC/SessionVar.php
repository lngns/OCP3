<?php
/**
 * Created by PhpStorm.
 * User: Longinus
 * Date: 05/09/2018
 * Time: 03:57 AM
 */
namespace PHOC;

/** @Annotation(@Field) */
final class SessionVar
{
    public function __construct(array $entity, string $field = NULL)
    {
        $parts = \explode("::", $entity["Symbol"]);
        if($field === NULL)
            $field = $parts[1];
        /** @noinspection PhpUnhandledExceptionInspection -- already checked by annotation engine */
        $reflection = new \ReflectionProperty($parts[0], $parts[1]);
        $reflection->setAccessible(true);
        $reflection->setValue(new class($field) {
            private $field;

            public function __construct($field)
            {
                $this->field = $field;
            }
            public function __invoke($value = NULL)
            {
                if($value === NULL)
                    return isset($_SESSION[$this->field]) ? $_SESSION[$this->field] : NULL;
                return $_SESSION[$this->field] = $value;
            }
            public function __toString(): string
            {
                return (string) $this->__invoke();
            }
            public function Get()
            {
                return $this->__invoke(); //because PHP
            }
            public function Set($v)
            {
                return $this->__invoke($v); //because PHP
            }
        });
    }

    /** @SessionVar("__phoc_sv_utTest") */
    static private $data;

    /** @UnitTest */
    static public function __UnitTest()
    {
        $_SESSION["__phoc_sv_utTest"] = 42;
        assert(self::$data->Get() === 42);
    }
}