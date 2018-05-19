# OCP3 - PHP Weblog

This repo contains the source code for my 3rd project at OpenClassrooms.  
Ce repo contient le code source de mon 3ème projet à OpenClassrooms.  
No dependencies are allowed, I therefore wrote PHOC - Longinus' **PH**P **O**pen**C**lassrooms framework.  
Aucune dépendance n'est autorisée, j'ai ainsi écrit PHOC - Longinus' **PH**P **O**pen**C**lassrooms framework.  
It is a simple annotation-based IoC framework supporting unit tests.  
C'est un simple framework d'inversion de control (IoC) basé sur des annotations.  
The website is based on it, and uses a standard MVC structure.  
Le site web est basé dessus, et utilise une structure MVC standard.  
It has been tested with Apache2, MySQL5 and PHP7 only.  
Le projet a été testé avec Apache2, MySQL5 et PHP7 seulement.  

## Control Flow

Programs using PHOC, such as this blog, follow a conventional control flow revolving around metadata, namely, annotations.  
Les programmes utilisant PHOC, tel que ce blog, suivent un flux de contrôle standard tournant autours de metadata, plus exactement, des annotations.  
PHOC projects configure their HTTP server to redirect all requests to a single `index.php` file, which starts the PHOC's runtime with the `\PHOC\Runtime::Start` function.  
Les projets PHOC configurent leurs serveurs HTTP afin de rediriger toutes les requêtes sur un seul fichier `index.php`, qui démarre le runtime de PHOC avec la fonction `\PHOC\Runtime::Start`.  
From here, the runtime first declares a standard autoloader, then autoload the so-called "entry class," as defined in the `configuration.xml` file.  
Le runtime déclare d'abords un autoloader standard, puis autoload la dénommé "entry class" (ou "class d'entrée") tel que définie dans le fichier `configuration.xml`.  
Notably, the PHOC's autoloader has the additional role of automatically querying the loaded class' annotations.  
Notablement, l'autoloader de PHOC a le rôle additionel d'automatiquement récupérer les annotations des classes chargées.  

Annotations' constructors will, by themselves, run code, but it is also possible for users to annotate static functions `@PHOC\ClassInit`, in which case such functions will be called to initialize their class.  
Les constructeurs d'annotations, par eux-mêmes, êxecute du code, mais il est également possible pour les utilisateurs d'annoter des fonctions statiques `@PHOC\ClassInit`, auquel cas, de tels fonctions seront appelées pour initialiser leurs classes.  
As annotation deduction is triggered by the autoloader, it is a non-deterministic process.  
Car la déduction d'annotation est enclenchée par l'autoloader, il s'agit d'un processus non-déterministe.  
Inside the entry class must be defined a static function annotated `@PHOC\Entry`. Only one entry function is allowed per program.  
Dans la classe d'entrée doit être définie une fonction statique annotée `@PHOC\Entry`. Seulement un point d'entré est autorisé par programme.  
This function will then be called by the runtime.  
La fonction est appelé par le runtime, stoppant ainsi son rôle d'initialisation.  
The runtime's initialization role stops there, and it is then up to the user to interact with PHOC.  
Il est ensuite à l'utilisateur d'intéragir avec PHOC.  
One natural pattern in web development is the definition of "web interfaces" - classes containing "route handlers."  
Un modèle naturel en développement web est la définition de "web interfaces" - des classes containant des "route handlers."  
It is possible for the user to define such interfaces by annotating static methods `@PHOC\Route` and dispatching a request over an interface with the `\PHOC\WebInterface` class.
Il est possible à l'utilisateur de définir de telles interfaces en annotant des fonctions statiques `@PHOC\Route` et en dispatchant une requête sur une interface avec la classe `\PHOC\WebInterface`.

## Annotations

Annotations are pieces of metadata about program's entities, more precisely classes, functions and fields.  
Les annotations sont des morceaux de metadata à propos des entités d'un programme, plus précisement, des classes, fonctions et champs.  
A PHOC annotation is written inside a PHP "doc comment" and with the forms `@T` or `@T(Args)` where `T` is a class name and `Args` a list of comma-separated PHP expressions.  
Une annotation de PHOC est écrite dans un "doc comment" de PHP et sous les formes `@T` ou `@T(Args)` où `T` est le nom d'une classe et `Args` une liste d'expression PHP séparé par des virgules.  
Instead of PHP expressions, an argument can be one of `@Class`, `@Field`, `@Method` or `@Function`, and it will be replaced by the appropriate `\PHOC\Annotations::T_*` constant.  
Au lieu d'une expression, un argument peut être un parmis `@Class`, `@Field`, `@Method` et `@Function`, et sera remplacé par la constante `\PHOC\Annotations::T_*` appropriée.  
To be recognized as such by the parser, an annotation must see its `@` character as the first on the current line, ignoring whitespaces, the first `*` character if there is one, and the first three chars `/**`.  
Afin d'être reconnue par le parseur, une annotation doit voir son charactère `@` être le premier sur la ligne, en ignorant les whitespaces, le première char `*` s'il y en a un, et les trois premiers charactères du commentaire `/**`.  
Hence, these three examples are seen as annotations:  
Ainsi, ces trois exemples sont reconnues:  
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
Seulement une annotation est autorisée sur chaque ligne, et toute autre information est ignorée.  
In the case another annotation parser is used, such as PHPDoc, it is possible to register key annotations to ignore with the `\PHOC\Annotations::RegisterAnnotationToIgnore(string): void` and `::RegisterAnnotationsToIgnore(string...): void` functions.  
Dans le cas où un autre processeur d'annotation serait utilisé, tel que PHPDoc, il est possible d'enregistrer des annotations clés avec les fonctions `\PHOC\Annotations::RegisterAnnotationToIgnore(string): void` et `::RegisterAnnotationsToIgnore(string...): void`.  
By default, the `@noinspection`, `@return`, `@param`, `@var` and `@throws` annotations are ignored.  
Par défault, les annotations `@noinspection`, `@return`, `@param`, `@var` et `@throws` sont ignorées.  
When an annotation is recognized, an object of the associated class name will be created with a map containing the entity's identity, and the passed arguments, passed to the constructor.  
Quand une annotation est reconnue, une instance de la classe associé est créé et une map contenant l'identité de l'entitée et les arguments passés, sont passés au constructeur.  
If the class name is not absolute, the engine will first search for the class inside the entity's namespace, then in the global namespace. PHP `use` statements are not considered.  
Si le nom de la classe n'est pas absolu, le processeur cherchera d'abord la classe dans le namespace de l'entité, puis dans le namespace global. Les phrases `use` de PHP ne sont pas considérées.  
The map is of the form `["Type" => string, "Symbol" => string]` where `Type` is one of `\PHOC\Annotations::T_CLASS`, `::T_FIELD`, `::T_METHOD` or `::T_FUNCTION`.  
La map est sous la forme `["Type" => string, "Symbol" => string]` où `Type` est l'un de `\PHOC\Annotations::T_CLASS`, `::T_FIELD`, `::T_METHOD` et `::T_FUNCTION`. 
In the case the indicated class does not exist or is not a valid annotation class, a `\PHOC\AnnotationException` is raised. Such exceptions are not meant to be caught.  
Dans le cas où la classe indiquée n'existerais pas ou n'est pas une classe d'annotation valide, une `\PHOC\AnnotationException` est lancée. De telles exceptions ne sont pas prévues pour être capturées.  
Again, as annotation deduction is triggered by the autoloader, it is a non-deterministic process.  
Encore une fois, car la déduction d'annotation est enclenchée par l'autoloader, il s'agit d'un processus non-déterministe.  

By default, PHOC comes with the following pre-defined annotation classes:  
Par défault, PHOC viens avec les classes d'annotations pré-définies suivantes:  
- **`\PHOC\Annotation(string...)`** - classes annotated this, are resolvable annotations.  
- **`\PHOC\Annotation(string...)`** - les classes annotées ceci sont des classes d'annotation résolvables.  
- **`\PHOC\ClassInit`** - static methods annotated this way are called during class initialization, and are intended to initialize static fields.  
- **`\PHOC\ClassInit`** - les méthodes statiques annotées de cette façon sont appelées durant l'initialisation de classe, et sont prévues pour initialiser les champs statiques.  
- **`\PHOC\Entry`** - defines the program's entry point.  
- **`\PHOC\Entry`** - définis le point d'entrée du programme.    
- **`\PHOC\Route(string)`** - defines a route inside a web interface.  
- **`\PHOC\Route(string)`** - définis une route dans une interface web.  
- **`\PHOC\SessionVar([string])`** - defines a session handle - an object used to access a `$_SESSION` member. The member can be specified through the argument, or is the field name by default.  
- **`\PHOC\SessionVar([string])`** - définis un handle de session - un object utilisé affin de manipuler un membre de `$_SESSION`. Le membre peut être spécifié à travers l'argument, ou est le nom du champ par défault.  
- **`\PHOC\UnitTest`** - static methods annotated this are called, but only in debug mode, and are intended to perform tests.  
- **`\PHOC\UnitTest`** - les méthodes statiques annotées ceci sont appelées, mais uniquement en mode de debug, et sont prévues pour éffectuer des tests.  

To be a valid annotation class, a class must be annotated `@PHOC\Annotation` with the desired entity types as arguments.  
Pour être une classe d'annotation valide, une classe doit être annotée `@PHOC\Annotation` avec les types d'entité désirés comme arguments.  
Ex (note the use of both the long PHP expression and the short `@Method` syntax):  
Ex (notez l'utilisation d'à la fois la longue expression PHP, et la courte syntaxe `@Method`):  
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
L'annotation au-dessus peut maintenant être utilisée de cette manière:
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


## Interfaces Web

PHOC's web interfaces are simple classes containing route handlers, or controllers.  
Les interfaces web de PHOC sont de simples classes contenant des processeurs de route.  
The classes by themselves are not recognized in any special ways, but the controllers are to be annotated `@PHOC\Route`.  
Les classes ne sont pas elles-mêmes reconnues d'une manière spéciale, mais les controlleurs doivent être annotés `@PHOC\Route`.  
The `\PHOC\Route` constructor accepts a single string argument: a URI pattern.  
Le constructeur de `\PHOC\Route` accepte un seul string argument: un pattern d'URI.  
URI patterns can have multiple placeholders, that are then passed to the controller with the gathered value when a request is dispatched.  
Les patterns d'URI peuvent contenir plusieurs placeholders, qui sont passés à leurs controlleurs avec les valeurs acquises quand une requête est dispatched.  
The available placeholders are:  
Les placeholders disponibles sont:  
- **`{i}`** - decimal integer number.
- **`{i}`** - nombre entier décimal.
- **`{a}`** - alphanumeric string (RegExp `[a-zA-Z0-9_]+`).
- **`{a}`** - string alphanumerique (RegExp `[a-zA-Z0-9_]+`).
- **`{*}`** - wildcard.
- **`{*}`** - wildcard.

Ex:  
Ex:  
`/user/{i}` will match the URI `/user/42` and will pass 42 to the controller.  
`/user/{i}` va match l'URI `/user/42` et passera 42 au controlleur.  
`/node/{a}/static/{*}` will match the URI `/node/october/static/surprised-seal.gif` and pass 42 and `surprised-seal.gif` to the controller.
`/node/{a}/static/{*}` va match l'URI `/node/october/static/surprised-seal.gif` et passera 42 et `surprised-seal.gif` au controlleur.

Such routes can be implemented this way:
De telles routes peuvent être implémentées de cette façon:
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
Quand une URI variable est désirée sans d'actuel paramètre, il est possible d'ajouter un charactère `?` après le symbole du placeholder.  
Ex:
Ex:
```php
class MyOtherWebInterface
{
    /** @PHOC\Route("/article/{*?}.{i}") */
    static public function Article(int $id)
    {
        //le premier placeholder pourrait être utilisé pour faire des pretty URLs
        echo("Requesting article id " . $id);
    }
}
```

Dispatching a request over an interface is done with `\PHOC\WebInterface::Dispatch(string, [string]): void`.  
Dispatch une requête sur une interface est atteint avec `\PHOC\WebInterface::Dispatch(string, [string]): void`.  
The first parameter is the name of the class to dispatch over. It is an error to dispatch over a class that doesn't have any route handlers.  
Le premier paramètre est le nom de la classe sur laquelle dispatch. Il s'agit d'une erreur de dispatch sur une classe n'ayant pas de processeurs de route.  
The second parameter is the URI to match against. It is by default the value of `$_SERVER["REQUEST_URI"]`.  
Le second paramètre est l'URI contre laquelle match les routes. Sa valeur par défault est la valeur de `$_SERVER["REQUEST_URI"]`.  

The route handlers are matched against the URI in their order of declaration, and deduction is therefore not based on specialization.  
Les processeurs de routes sont matched contre l'URI dans leur ordre de déclaration. La déduction n'est ainsi pas basé sur leur spécialisation.  
It means that if two handlers are declared in this order: `/{*}`, `/foo`, then a URI `/foo` will match the first route.  
Cela signifie que si deux processeurs sont déclarés dans cet ordre: `/{*}`, `/foo`, alors une URI `/foo` va match la première route.  
Lastly, instead of a URI pattern, the number `404` can be passed as annotation argument, in which case the function will be called if no other is selected during dispatch.  
Enfin, au lieu d'un pattern d'URI, le nombre `404` peut être passé comme argument à l'annotation, auquel cas la fonction sera appelée si aucune autre n'est sélectionné durant le dispatch.  


## XML Templating

PHOC supports a form of pseudo-XML templating including multiple XML tags.  
PHOC supportes une forme de templating pseudo-XML incluant plusieurs tags XML.  
It is "pseudo-XML" as no actual XML declaration is needed and the processor is happy manipulating HTML documents.  
Il s'agit de "pseudo-XML" par les faits qu'aucune déclaration XML est nécessaire et que le processeur est content quand manipulant des documents HTML.  
It is possible to compile a file to HTML/PHP with the `\PHOC\Template::RenderFile(string): callable([array])` function.  
Il est possible de compiler un fichier en HTML/PHP avec la fonction `\PHOC\Template::RenderFile(string): callable([array])`.  
The returned delegate can then be called to execute the generated PHP code. If an array is passed to it, its indices will be extracted into the local scope for use by the template.  
Le delegate retourné peut alors être appelé afin d'éxecuter le code PHP généré. Si un array lui est passé, ses indices seront extraits dans le scope local pour être utilisé par le template.  

These special tags reside inside the `phoc` XML namespace.  
Ses tags spéciaux vivent dans le namespace XML `phoc`.  
Among them are two tags dedicated to outputing content. They are also accepted inside strings with curly brackets instead of angled brackets.  
Parmis eux sont deux tags dédiés à output. Ils sont également acceptés à l'intérieur de strings, avec des accolades (curly brackets) au lieu d'équerres (angle brackets).  
In the case there are inside a string, then double quotes must be changed to single quotes, to match regular XML code.  
Dans le cas où ils sont à l'intérieur d'un string, alors les doubles guillemets doivent être changé pour des appostrophes.  
Ex: `<phoc:out var="$foo" />` and `<span class="{phoc:out var='$foo'}">...</span>`
Ex: `<phoc:out var="$foo" />` et `<span class="{phoc:out var='$foo'}">...</span>`

Any PHP expression can be passed to PHOC tags, as long as it is of the requested type.  
Toute expression PHP peut être passé aux tags de PHOC, tant qu'elles respectent le type attendu.  
The dialect also supports comments with a weird syntax: `<phoc:!-- comment here --/>`.  
Le dialecte supporte également des commentaires avec une syntaxe non-orthodoxe: `<phoc:!-- commentaire ici --/>`.  
They can be expressed as the RegExp `/\<phoc:!--(.*)--\/?\>/sU` _(note the last `/` is optional)_ and are removed before emission of the resulting XML or HTML code.
Ils peuvent être exprimés avec la RegExp `/\<phoc:!--(.*)--\/?\>/sU` _(notez que le dernier `/` est optionel)_ et sont enlevés avant l'émission du code XML ou HTML produit.  

```cpp
namespace phoc
{
    //if the expression passed to `is` is true, then the body is evaluated.
    //si l'expression passée à `is` est vraie, alors le corps est évalué.
    //otherwise, in the case a `phoc:else` tag is present in the body, the code following it is evaluated.
    //sinon, dans le cas où un tag `phoc:else` est présent dans le corps, le code le suivant est évalué.
    node `if`
    {
        bool is;
        xml _body;
    };
    node `else` {};
    
    //for each value in the array passed to `each`, the variable passed to `as` is populated with the current value and the body evaluated.
    //pour chaque valeur dans l'array passé à `each`, la variable passé à `as` est peuplée avec la valeur actuelle et le corps évalué.
    node `for`
    {
        array each;
        any& as;
        xml _body;
    };
    
    //includes the indicated file's content in the current compilation unit.
    //inclus le fichier indiqué dans l'unité de compilation actuelle.
    node include
    {
        string file;
    };
    
    //accepts an unknown attribute, and declares a variable with its name, populating it with the result of the passed expression.
    //accepte un attribut inconnus, et déclare une variable avec son nom, la peuplant avec le résultat de l'expression passée.
    node def
    {
        any __any;
    };
    
    //outputs the result of the expression passed to `var`.
    //outputs le résultat de l'expression passée à `var`.
    node out
    {
        any var;
    };
    
    //outputs the project's base url.
    //outputs l'URL de base du projet.
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