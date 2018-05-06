<?php
$input = ["Hello World!", "main", "\$hello", "52az", "thisptr_t", "_plt_factory2", "c2bd8980d327b47351b09f01fd554ee083a070aebbf931e896b792701e35ddf1", "c2bd8980d327b47351b09f01fd554ee083a070aebb\$f931e896b792701e35ddf1"];
function _Regex_c2bd8980d327b47351b09f01fd554ee083a070aebbf931e896b792701e35ddf1($input)
{
    $i0 = ord($input[0]);
    if(($i0 > 64 && $i0 < 91) || ($i0 > 96 && $i0 < 123) || $i0 === 95)
    {
        $len0 = strlen($input);
        for($ii0 = 1; $ii0 < $len0; ++$ii0)
        {
            $ij = ord($input[$ii0]);
            if(($ij < 48) || ($ij > 57 && $ij < 65) || ($ij > 90 && $ij < 97 && $ij !== 95) || ($ij > 122))
                return false;
        }
    }
    else
        return false;
    return true;
}
function _Regex1_c2bd8980d327b47351b09f01fd554ee083a070aebbf931e896b792701e35ddf1($input)
{
    $i0 = ord($input[0]);
    if(($i0 > 64 && $i0 < 91) || ($i0 > 96 && $i0 < 123) || $i0 === 95)
    {
        $len0 = strlen($input);
        for($ii0 = 1; $ii0 < $len0; ++$ii0)
        {
            $ij = ord($input[$ii0]);
            if(($ij > 64 && $ij < 91) || ($ij > 96 && $ij < 123) || ($ij > 47 && $ij < 59) || $ij === 95)
                continue;
            else
                return false;
        }
    }
    else
        return false;
    return true;
}

echo("String Operations 0:" . PHP_EOL . PHP_EOL);
foreach($input as $entry)
{
    echo('"' . $entry . '"' . PHP_EOL . "Result: ");
    var_dump(_Regex_c2bd8980d327b47351b09f01fd554ee083a070aebbf931e896b792701e35ddf1($entry));
    $start = microtime(true);
    for($i = 0; $i < 100000; ++$i)
        _Regex_c2bd8980d327b47351b09f01fd554ee083a070aebbf931e896b792701e35ddf1($entry);
    $end = microtime(true);
    echo("Time: " . ($end - $start) . PHP_EOL);
}
echo(PHP_EOL . "----" . PHP_EOL);

echo("String Operations 1:" . PHP_EOL . PHP_EOL);
foreach($input as $entry)
{
    echo('"' . $entry . '"' . PHP_EOL . "Result: ");
    var_dump(_Regex_c2bd8980d327b47351b09f01fd554ee083a070aebbf931e896b792701e35ddf1($entry));
    $start = microtime(true);
    for($i = 0; $i < 100000; ++$i)
        _Regex1_c2bd8980d327b47351b09f01fd554ee083a070aebbf931e896b792701e35ddf1($entry);
    $end = microtime(true);
    echo("Time: " . ($end - $start) . PHP_EOL);
}
echo(PHP_EOL . "----" . PHP_EOL);

echo("RegEx Operations:" . PHP_EOL . PHP_EOL);
foreach($input as $entry)
{
    echo('"' . $entry . '"' . PHP_EOL . "Result: ");
    var_dump(preg_match("/^[a-zA-Z_]\w+$/", $entry));
    $start = microtime(true);
    for($i = 0; $i < 100000; ++$i)
        preg_match("/^[a-zA-Z_]\w+$/", $entry);
    $end = microtime(true);
    echo("Time: " . ($end - $start) . PHP_EOL);
}
