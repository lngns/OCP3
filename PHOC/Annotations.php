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
        if($type === self::T_CLASS)
        {
            foreach($reflection->getMethods() as $method)
                Annotations::GetAnnotations($symbol . "::" . $method->name, self::T_METHOD);
        }
        $objects = array();
        $doc = $reflection->getDocComment();
        $annotations = self::ParseDocComment($doc);
        foreach($annotations["Annotations"] as $annotation)
        {
            $class = $annotation["Class"];
            if(!\class_exists($class))
                throw new \UnexpectedValueException("Class " . $class . " does not exist.");
            $arguments = $annotation["Arguments"];
            $len = count($arguments);
            //var_dump($arguments);
            for($i = 0; $i < $len; ++$i)
                $arguments[$i] = eval("return " . $arguments[$i] . ";");
            //var_dump($arguments);
            array_unshift($arguments, array("Type" => $type, "Symbol" => $symbol));
            $objects[] = new $class(...$arguments);
        }
        self::$List[$type . ':' . $symbol] = $objects;
        return $objects;
    }
    /* TO TEST */
    static public function ParseDocComment($source)
    {
        $errors = array();
        $annotations = array();
        $source = substr($source, 3, strlen($source) - 5);
        $lines = \preg_split("/\r\n|\n|\r/", $source);
        foreach($lines as $line)
        {
            //echo("Before: "); var_dump($line);
            $line = \ltrim($line);
            if(empty($line))
                continue;
            if($line[0] === '*')
                $line = \ltrim(\substr($line, 1));
            if($line[0] !== '@')
                continue;
            //echo("After: "); var_dump($line);
            $buffer = "";
            $args = array();
            $len = strlen($line);
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
                        else if($char === ',')
                        {
                            $args[] = $argBuffer;
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
                        $args[] = $argBuffer;
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
}