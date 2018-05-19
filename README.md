# OCP3 - PHP Weblog

This repo contains the source code for my 3rd project at OpenClassrooms.  
No dependencies are allowed, I therefore wrote PHOC - Longinus' **PH**P **O**pen**C**lassrooms framework.  
It is a simple annotation-based IoC framework supporting unit tests.  
The website is based on it, and uses a standard MVC structure.  
It has been tested with Apache2, MySQL5 and PHP7 only.  

## Control Flow

Programs using PHOC, such as this blog, follow a conventional control flow revolving around metadata, namely, annotations.  
PHOC projects configure their HTTP server to redirect all requests to a single `index.php` file, which starts the PHOC's runtime with the `\PHOC\Runtime::Start` function.  
From here, the runtime first declares a standard autoloader, then autoload the so-called "entry class", as defined in the `configuration.xml` file.  
Notably, the PHOC's autoloader has the additional role of automatically querying the loaded class' annotations.  

Annotations' constructors will, by themselves, run code, but it is also possible for users to annotate static functions `@PHOC\ClassInit`, in which case such functions will be called to initialize their class.  
As annotation deduction is triggered by the autoloader, it is a non-deterministic process.  
Inside the entry class must be defined a static function annotated `@PHOC\Entry`. Only one entry function is allowed per program.  
This function will then be called by the runtime.  
The runtime's initialization role stops there, and it is then up to the user to interact with PHOC.  
One natural pattern in web development is the definition of "web interfaces" - classes containing "route handlers."  
It is possible for the user to define such interfaces by annotating static methods `@PHOC\Route` and dispatching a request over an interface with the `\PHOC\WebInterface` class.

## Annotations

Annotations are pieces of metadata about program's entities, more precisely classes, functions and fields.  
A PHOC annotation is written inside a PHP "doc comment" and with the forms `@T` or `@T(Args)` where `T` is a class name and `Args` a list of comma-separated PHP expressions.  
Instead of PHP expressions, an argument can be one of `@Class`, `@Field`, `@Method` or `@Function`, and it will be replaced by the appropriate `\PHOC\Annotations::T_*` constant.  
To be recognized as such by the parser, an annotation must see its `@` character as the first on the current line, ignoring whitespaces, the first `*` character if there is one, and the first three chars `/**`.  
Hence, these three examples are seen as annotations:  
```php
/** @Foo */

/**
@Bar
*/

/**
 * @Baz
 */
```
Only one annotation is allowed per line inside one doc comment, and all other information is ignored.  
In the case another annotation parser is used, such as PHPDoc, it is possible to register key annotations to ignore with the `\PHOC\Annotations::RegisterAnnotationToIgnore(string): void` and `::RegisterAnnotationsToIgnore(string...): void` functions.  
By default, the `@noinspection`, `@return`, `@param`, `@var` and `@throws` annotations are ignored.  
When an annotation is recognized, an object of the associated class name will be created with a map containing the entity's identity, and the passed arguments, passed to the constructor.  
If the class name is not absolute, the engine will first search for the class inside the entity's namespace, then in the global namespace. PHP `use` statements are not considered.  
The map is of the form `["Type" => string, "Symbol" => string]` where `Type` is one of `\PHOC\Annotations::T_CLASS`, `::T_FIELD`, `::T_METHOD` or `::T_FUNCTION`.  
In the case the indicated class does not exist or is not a valid annotation class, a `\PHOC\AnnotationException` is raised. Such exceptions are not meant to be caught.  
Again, as annotation deduction is triggered by the autoloader, it is a non-deterministic process.  

By default, PHOC comes with the following pre-defined annotation classes:  
- **`\PHOC\Annotation(string...)`** - classes annotated this, are resolvable annotations.  
- **`\PHOC\ClassInit`** - static methods annotated this way are called during class initialization, and are intended to initialize static fields.  
- **`\PHOC\Entry`** - defines the program's entry point.  
- **`\PHOC\Route(string)`** - defines a route inside a web interface.  
- **`\PHOC\SessionVar([string])`** - defines a session handle - an object used to access a `$_SESSION` member. The member can be specified through the argument, or is the field name by default.  
- **`\PHOC\UnitTest`** - static methods annotated this are called, but only in debug mode, and are intended to perform tests.  

To be a valid annotation class, a class must be annotated `@PHOC\Annotation` with the desired entity types as arguments.  
Ex (note the use of both the long PHP expression and the short `@Method` syntax):  
```php
<?php
namespace MyApp;

/** @PHOC\Annotation(\PHOC\Annotations::T_FUNCTION, @Method) */
final class MyAnnotation
{
    private $message;
    
    public function __construct(array $entity, string $message)
    {
        $this->message = $message;
    }
    public function GetMessage(): string
    {
        return $this->message;
    }
}
```
The above annotation can now be used this way:
```php
<?php
namespace MyApp;
use \PHOC\Annotations;

abstract class MyProgram
{
    /** @MyAnnotation("Hello World!") */
    public function Foo() {}
    
    /** @PHOC\Entry */
    static public function Main()
    {
        echo(Annotations::GetAnnotations("\MyApp\MyProgram::Foo")[0]->GetMessage());
        //Expected output: Hello World!
    }
}
```


## Web Interfaces

PHOC's web interfaces are simple classes containing route handlers, or controllers.  
The classes by themselves are not recognized in any special ways, but the controllers are to be annotated `@PHOC\Route`.  
The `\PHOC\Route` constructor accepts a single string argument: a URI pattern.  
URI patterns can have multiple placeholders, that are then passed to the controller with the gathered value when a request is dispatched.  
The available placeholders are:  
- **`{i}`** - decimal integer number.
- **`{a}`** - alphanumeric string (RegExp `[a-zA-Z0-9_]+`).
- **`{*}`** - wildcard.

Ex:  
`/user/{i}` will match the URI `/user/42` and will pass 42 to the controller.  
`/node/{a}/static/{*}` will match the URI `/node/october/static/surprised-seal.gif` and pass 42 and `surprised-seal.gif` to the controller.

Such routes can be implemented this way:
```php
class MyWebInterface
{
    /** @PHOC\Route("/user/{i}") */
    static public function User(int $i)
    {
        echo("requesting user page for user " . $i);
    }
    
    /** @PHOC\Route("/node/{a}/static/{*}") */
    static public function NodeStatic(string $node, string $file)
    {
        echo("requesting file " . $file . " from node " . $node);
    }
}
```

When a variable URI is desired without an actual parameter, it is possible to add a `?` character after the placeholder symbol.  
Ex:
```php
class MyOtherWebInterface
{
    /** @PHOC\Route("/article/{*?}.{i}") */
    static public function Article(int $id)
    {
        //the first placeholder may be used for making pretty URLs
        echo("Requesting article id " . $id);
    }
}
```

Dispatching a request over an interface is done with `\PHOC\WebInterface::Dispatch(string, [string]): void`.  
The first parameter is the name of the class to dispatch over. It is an error to dispatch over a class that doesn't have any route handlers.  
The second parameter is the URI to match against. It is by default the value of `$_SERVER["REQUEST_URI"]`.  

The route handlers are matched against the URI in their order of declaration, and deduction is therefore not based on specialization.  
It means that if two handlers are declared in this order: `/{*}`, `/foo`, then a URI `/foo` will match the first route.  
Lastly, instead of a URI pattern, the number `404` can be passed as annotation argument, in which case the function will be called if no other is selected during dispatch.  


## XML Templating

PHOC supports a form of pseudo-XML templating including multiple XML tags.  
It is "pseudo-XML" as no actual XML declaration is needed and the processor is happy manipulating HTML documents.  
It is possible to compile a file to HTML/PHP with the `\PHOC\Template::RenderFile(string): callable([array])` function.  
The returned delegate can then be called to execute the generated PHP code. If an array is passed to it, its indices will be extracted into the local scope for use by the template.  

These special tags reside inside the `phoc` XML namespace.  
Among them are two tags dedicated to outputing content. They are also accepted inside strings with curly brackets instead of angled brackets.  
In the case there are inside a string, then double quotes must be changed to single quotes, to match regular XML code.  
Ex: `<phoc:out var="$foo" />` and `<span class="{phoc:out var='$foo'}">...</span>`

Any PHP expression can be passed to PHOC tags, as long as it is of the requested type.  
The dialect also supports comments with a weird syntax: `<phoc:!-- comment here --/>`.  
They can be expressed as the RegExp `/\<phoc:!--(.*)--\/?\>/sU` _(note the last `/` is optional)_ and are removed before emission of the resulting XML or HTML code.

```cpp
namespace phoc
{
    //if the expression passed to `is` is true, then the body is evaluated.
    //otherwise, in the case a `phoc:else` tag is present in the body, the code following it is evaluated.
    node `if`
    {
        bool is;
        xml _body;
    };
    node `else` {};
    
    //for each value in the array passed to `each`, the variable passed to `as` is populated with the current value and the body evaluated.
    node `for`
    {
        array each;
        any& as;
        xml _body;
    };
    
    //includes the indicated file's content in the current compilation unit.
    node include
    {
        string file;
    };
    
    //accepts an unknown attribute, and declares a variable with its name, populating it with the result of the passed expression.
    node def
    {
        any __any;
    };
    
    //outputs the result of the expression passed to `var`.
    node out
    {
        any var;
    };
    
    //outputs the project's base url.
    node `base-url` {};
}
```

Ex:
```xml
<phoc:include file="header.html" />

<phoc:for each="$Articles" as="$article">
    <div>
        <h4>
            <phoc:def url="\preg_replace('/\s+/', '-', $article->Title) . '.' . $article->Id" />
            <a href="{phoc:base-url}/article/{phoc:out var='$url'}">
                <phoc:out var="$article->Title" />
            </a>
        </h4>
        <small><phoc:out var="$article->Date" /></small>
        <p>
            <phoc:out var="$article->Abstract" />... 
            <a href="{phoc:base-url}/article/{phoc:out var='$url'}">Continue Reading</a>
        </p>
    </div>
</phoc:for>

<phoc:if is="$PageId !== 0">
    <a href="{phoc:base-url}/archives/{phoc:out var='$PageId-1'}">
        Previous
    </a>
</phoc:if>
<phoc:if is="\count($Articles) === 5 && $Articles[4]->Id !== $FirstId">
    <a href="{phoc:base-url}/archives/{phoc:out var='$PageId+1'}">
        Next
    </a>
</phoc:if>

<phoc:include file="footer.html" />
```