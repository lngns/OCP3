# OCP3 - PHP Weblog

This repo contains the source code for my 3rd project at OpenClassrooms.  
No dependencies are allowed, I therefore wrote PHOC - Longinus' **PH**P **O**pen**C**lassrooms framework.  
It is a simple annotation-based IoC framework supporting unit tests.  
The website is based on it, and uses a standard MVC structure.  
It has been tested with Apache2, MySQL5 and PHP7 only.  

## XML Templating

PHOC supports a form of pseudo-XML templating including multiple XML tags.  
It is "pseudo-XML" as no actual XML declaration is needed and the processor is happy manipulating HTML documents.  
These special tags reside inside the `phoc` XML namespace.  
Among them are two tags dedicated to outputing content. There are also accepted inside strings with curly brackets instead of angled brackets.  
In the case there are inside a string, then double quotes must be changed to single quotes, to match regular XML code.  
Ex: `<phoc:out var="$foo" />` and `<span class="{phoc:out var='$foo'}">...</span>`

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