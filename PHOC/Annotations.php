<?php
/**
 * Created by PhpStorm.
 * User: Longinus
 * Date: 05/06/2018
 * Time: 08:07 AM
 */
namespace PHOC;

abstract class Annotations
{
    const T_CLASS = "Class";
    const T_METHOD = "Method";
    const T_FUNCTION = "Function";
    static private $List = array();

    static public function GetAnnotations($symbol, $type = self::T_CLASS)
    {
        if($symbol[0] !== '\\')
            $symbol = '\\' . $symbol;
        if(isset(self::$List[$type . ':' . $symbol]))
            return self::$List[$type . ':' . $symbol];
        $class = "\\Reflection" . $type;
        if(!\class_exists($class))
            throw new \InvalidArgumentException("Type " . $type . " is not supported.");
        try
        {
            if($type === self::T_METHOD)
            {
                $parts = \explode("::", $symbol);
                $reflection = new $class($parts[0], $parts[1]);
            }
            else
                $reflection = new $class($symbol);
        }
        catch(\Exception $e)
        {
            throw new \InvalidArgumentException("Symbol " . $symbol . " does not exist or is not supported.");
        }
        $objects = [];
        /** @noinspection PhpUndefinedMethodInspection -- IDE is not smart enough to get that $reflection is a ReflectionSomething */
        $doc = $reflection->getDocComment();
        $annotations = self::ParseDocComment($doc);
        foreach($annotations["Annotations"] as $annotation)
        {
            $class = $annotation["Class"];
            if(!\class_exists($class))
                throw new \UnexpectedValueException("Class " . $class . " does not exist.");
            $arguments = $annotation["Arguments"];
            $len = \count($arguments);
            for($i = 0; $i < $len; ++$i)
                $arguments[$i] = eval("return " . $arguments[$i] . ";");
            \array_unshift($arguments, array("Type" => $type, "Symbol" => $symbol));
            $objects[] = new $class(...$arguments);
        }
        self::$List[$type . ':' . $symbol] = $objects;
        if($type === self::T_CLASS)
        {
            /** @noinspection PhpUndefinedMethodInspection -- IDE is not smart enough to get that $reflection is a ReflectionClass */
            foreach($reflection->getMethods() as $method)
                Annotations::GetAnnotations($symbol . "::" . $method->name, self::T_METHOD);
        }
        return $objects;
    }
    static public function ParseDocComment($source)
    {
        $errors = [];
        $annotations = [];
        $source = \substr($source, 3, \strlen($source) - 5);
        $lines = \preg_split("/\r\n|\n|\r/", $source);
        foreach($lines as $line)
        {
            $line = \ltrim($line);
            if(empty($line))
                continue;
            if($line[0] === '*')
                $line = \ltrim(\substr($line, 1));
            if($line[0] !== '@')
                continue;
            $buffer = "";
            $args = array();
            $len = \strlen($line);
            for($i = 1; $i < $len; ++$i)
            {
                $char = $line[$i];
                if($char === ' ' || $char === '\t' || $char === '\0' || $char === '\0x0B')
                    break;
                else if($char === '(')
                {
                    $depth = 1;
                    $argBuffer = "";
                    for($j = $i + 1; $j < $len; ++$j)
                    {
                        $char = $line[$j];
                        if($char === '(')
                            ++$depth;
                        else if($char === ')' && --$depth === 0)
                            break;
                        if($char === ',' && $depth === 1)
                        {
                            $args[] = \trim($argBuffer);
                            $argBuffer = "";
                        }
                        else
                            $argBuffer .= $char;
                    }
                    if($depth !== 0)
                    {
                        $errors[] = $buffer;
                        break;
                    }
                    if(!empty($argBuffer))
                        $args[] = \trim($argBuffer);
                    $i = $j;
                }
                else
                    $buffer .= $char;
            }
            $annotations[] = [
                "Class" => $buffer,
                "Arguments" => $args
            ];
        }
        return [
            "Annotations" => $annotations,
            "Errors" => $errors
        ];
    }
    static public function ForceUpdate()
    {
        foreach(\get_declared_classes() as $symbol)
        {
            if($symbol[0] !== '\\')
                $symbol = '\\' . $symbol;
            /** @noinspection PhpUnhandledExceptionInspection -- Symbol provided by the PHP engine. */
            $reflection = new \ReflectionClass($symbol);
            if(!isset(self::$List["Class:" . $symbol]) && !$reflection->isInternal())
                Annotations::GetAnnotations($symbol, self::T_CLASS);
        }
        foreach(\get_defined_functions()["user"] as $symbol)
        {
            if(!isset(self::$List["Function:" . $symbol]))
                Annotations::GetAnnotations($symbol, self::T_FUNCTION);
        }
    }

    /** @PHOC\UnitTest */
    static public function __UnitTest()
    {
        //One Annotation
        $code = "
        /**
         * @Foo
         */
        ";
        $expected = [
            "Annotations" => [
                0 => [
                    "Class" => "Foo",
                    "Arguments" => []
                ]
            ],
            "Errors" => []
        ];
        assert(self::ParseDocComment($code) === $expected);

        //Multiple Annotations and ignorance of other useless data
        $code = "
        /**
         * @Foo
         * @Bar
         * yolo
         * @Baz
         */
        ";
        $expected = [
            "Annotations" => [
                0 => [
                    "Class" => "Foo",
                    "Arguments" => []
                ],
                1 => [
                    "Class" => "Bar",
                    "Arguments" => []
                ],
                2 => [
                    "Class" => "Baz",
                    "Arguments" => []
                ]
            ],
            "Errors" => []
        ];
        assert(self::ParseDocComment($code) === $expected);

        //Argument Parsing
        $code = "
        /**
         * @Foo(Bar)
         * @Bar
         * sdfsdf
         * @Baz(\"Hello\", 42, launchMissiles(89, new Rocket(\$_Runtime)), M_PI)
         * @Qux(3.14)
         */
        ";
        $expected = [
            "Annotations" => [
                0 => [
                    "Class" => "Foo",
                    "Arguments" => [
                        0 => "Bar"
                    ]
                ],
                1 => [
                    "Class" => "Bar",
                    "Arguments" => []
                ],
                2 => [
                    "Class" => "Baz",
                    "Arguments" => [
                        0 => "\"Hello\"",
                        1 => "42",
                        2 => "launchMissiles(89, new Rocket(\$_Runtime))",
                        3 => "M_PI"
                    ]
                ],
                3 => [
                    "Class" => "Qux",
                    "Arguments" => [
                        0 => "3.14"
                    ]
                ]
            ],
            "Errors" => []
        ];
        assert(self::ParseDocComment($code) === $expected);
    }
}