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

Annotations are pieces of metadata about program's entities, more precisely classes, functions and fields.  
A PHOC annotation is written inside a PHP "doc comment" and with the forms `@T` or `@T(Args)` where `T` is a class name and `Args` a list of comma-separated PHP expressions.  
To be recognized as such by the parser, an annotation must see its '@' character as the first on the current line, ignoring whitespaces, the first `*` character if there is one, and the first three chars `/**`.  
Hence, these three examples are seen as annotations:  
```php
/** @Foo */
```
```
/**
@Bar
*/
```
```
/**
 * @Baz
 */
```


## XML Templating

PHOC supports a form of pseudo-XML templating including multiple XML tags.  
It is "pseudo-XML" as no actual XML declaration is needed and the processor is happy manipulating HTML documents.  
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
    //Otherwise, in the case a `phoc:else` tag is present in the body, the code following it is evaluated.
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
        any as;
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