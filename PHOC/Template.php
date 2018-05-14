<?php
/**
 * Created by PhpStorm.
 * User: Longinus
 * Date: 05/12/2018
 * Time: 05:11 AM
 */
namespace PHOC;

abstract class Template
{
    static private $Transformations = [
        "Pattern" => [
            "/\<phoc:if\s+is=\"(.*)\"\>(.*)\<phoc:else\s*\/?\>(.*)\<\/phoc:if\>/sU",
            "/\<phoc:if\s+is=\"(.*)\"\>(.*)\<\/phoc:if\>/sU",
            "/\<phoc:for\s+each=\"(.*)\"\s+as=\"(.*)\"\>(.*)\<\/phoc:for\>/sU",
            "/\<phoc:include\s+file=\"(.*)\"\s*\/\>/sU",
            "/\<phoc:def\s+([a-zA-Z][a-zA-Z0-9_]*)=\"(.*)\"\s*\/\>/sU",
            "/\<phoc:out\s+var=\"(.*)\"\s*\/\>/sU",
            "/\<(.*)=\"(.*)\{phoc:out\s+var='(.*)'\s*\/?\}(.*)\"(.*)\>/U",
            "/\<(.*)=\"(.*)\{phoc:base-url\s*\/?\}(.*)\"(.*)\>/U"
        ],
        "Replace" => [
            "<?php if($1): ?>$2<?php else: ?>$3<?php endif; ?>",
            "<?php if($1): ?>$2<?php endif; ?>",
            "<?php foreach($1 as $2): ?>$3<?php endforeach; ?>",
            "<?php echo(\\PHOC\\Template::RenderFile(\"$1\")()); ?>",
            "<?php \$$1 = $2; ?>",
            "<?php echo($1); ?>",
            "<$1=\"$2<?php echo($3); ?>$4\"$5>",
            "<$1=\"$2<?php echo(\PHOC\Configuration::BaseUrl()); ?>$3\"$4>"
        ]
    ];
    static public function Compile(string $source)
    {
        $output = \preg_replace(self::$Transformations["Pattern"], self::$Transformations["Replace"], $source);
        return $output;
    }
    static public function RenderFile(string $file)
    {
        $file = \str_replace(["/", "\\"], DIRECTORY_SEPARATOR, $file);
        if($file[0] !== '/')
            $file = '/' . $file;
        /** @noinspection PhpUndefinedMethodInspection */
        $file = Configuration::ResourceDirectory() . $file;
        if(!\file_exists($file))
            throw new IOException("File " . $file . " not found.");
        return function (array $env = []) use ($file) {
            //var_dump(self::Compile(\file_get_contents($file)));
            eval("extract(\$env); ?> " . self::Compile(\file_get_contents($file)) . " <?php ");
        };
    }

    /** @PHOC\UnitTest */
    static public function __UnitTest()
    {
        assert(self::Compile("<phoc:if is=\"\$foo === 42\">Hello</phoc:if>") === "<?php if(\$foo === 42): ?>Hello<?php endif; ?>");
    }
}