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
            "/\<phoc:!--(.*)--\/?\>/sU",
            "/\<phoc:if\s+is=\"(.*)\"\>(.*)\<phoc:else\s*\/?\>(.*)\<\/phoc:if\>/sU",
            "/\<phoc:if\s+isset=\"(.*)\"\>(.*)\<phoc:else\s*\/?\>(.*)\<\/phoc:if\>/sU",
            "/\<phoc:if\s+is=\"(.*)\"\>(.*)\<\/phoc:if\>/sU",
            "/\<phoc:if\s+isset=\"(.*)\"\>(.*)\<\/phoc:if\>/sU",
            "/\<phoc:for\s+each=\"(.*)\"\s+as=\"(.*)\"\>(.*)\<\/phoc:for\>/sU",
            "/\<phoc:include\s+file=\"(.*)\"\s*\/\>/sU",
            "/\<phoc:def\s+([a-zA-Z_][a-zA-Z0-9_]*)=\"(.*)\"\s*\/\>/sU",
            "/\<phoc:out\s+var=\"(.*)\"\s*\/\>/sU",
            "/\<phoc:base-url\s*\/\>/sU",
            "/\<phoc:param\s+name=\"([a-zA-Z_][a-zA-Z0-9_]*)\"\s*\/\>/sU",
            "/\<(.*)=\"(.*)\{phoc:out\s+var='(.*)'\s*\/?\}(.*)\"(.*)\>/U",
            "/\<(.*)=\"(.*)\{phoc:base-url\s*\/?\}(.*)\"(.*)\>/U"
        ],
        "Replace" => [
            "",
            "<?php if($1): ?>$2<?php else: ?>$3<?php endif; ?>",
            "<?php if(isset($1)): ?>$2<?php else: ?>$3<?php endif; ?>",
            "<?php if($1): ?>$2<?php endif; ?>",
            "<?php if(isset($1)): ?>$2<?php endif; ?>",
            "<?php foreach($1 as $2): ?>$3<?php endforeach; ?>",
            "<?php echo(\\PHOC\\Template::RenderFile(\"$1\")(\$__env)); ?>",
            "<?php \$$1 = $2; ?>",
            "<?php echo($1); ?>",
            "<?php echo(\PHOC\Configuration::BaseUrl()); ?>",
            "<?php if(!isset(\$$1)) throw new \\BadFunctionCallException(\"Template \$__template is missing argument $1\"); ?>",
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
        $file = Configuration::TemplateDirectory() . $file;
        if(!\file_exists($file))
            throw new IOException("File " . $file . " not found.");
        $__template = \realpath($file);
        $contents = self::Compile(\file_get_contents($__template));
        return function (array $__env = []) use ($__template, $contents) {
            if(\ob_get_level() <= 1)
            {
                \ob_start();
                $ob_flag = 1;
            }
            eval("unset(\$contents); extract(\$__env); ?> " . $contents . " <?php ");
            if(isset($ob_flag))
                \ob_end_flush();
        };
    }
    static public function ResetBuffer()
    {
        while(\ob_get_level() > 0)
            \ob_end_clean();
    }

    /** @UnitTest */
    static public function __UnitTest()
    {
        assert(self::Compile("<phoc:if is=\"\$foo === 42\">Hello</phoc:if>") === "<?php if(\$foo === 42): ?>Hello<?php endif; ?>");
    }
}