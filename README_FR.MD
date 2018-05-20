# OCP3 - PHP Weblog

Ce repo contient le code source de mon 3ème projet à OpenClassrooms.  
Aucune dépendance n'est autorisée, j'ai ainsi écrit PHOC - Longinus' **PH**P **O**pen**C**lassrooms framework.  
C'est un simple framework d'inversion de control (IoC) basé sur des annotations.  
Le site web est basé dessus, et utilise une structure MVC standard.  
Le projet a été testé avec Apache2, MySQL5 et PHP7 seulement.  

## Control Flow

Les programmes utilisant PHOC, tel que ce blog, suivent un flux de contrôle standard tournant autours de metadata, plus exactement, des annotations.  
Les projets PHOC configurent leurs serveurs HTTP afin de rediriger toutes les requêtes sur un seul fichier `index.php`, qui démarre le runtime de PHOC avec la fonction `\PHOC\Runtime::Start(void)`.  
Le runtime déclare d'abords un autoloader standard, puis autoload la dénommée "entry class" (ou "class d'entrée") tel que définie dans le fichier `configuration.xml`.  
Notablement, l'autoloader de PHOC a le rôle additionel d'automatiquement récupérer les annotations des classes chargées.  

Les constructeurs d'annotations, par eux-mêmes, êxecutent du code, mais il est également possible pour les utilisateurs d'annoter des fonctions statiques `@PHOC\ClassInit`, auquel cas, de telles fonctions seront appelées pour initialiser leurs classes.  
Car la déduction d'annotation est enclenchée par l'autoloader, il s'agit d'un processus non-déterministe.  
Dans la classe d'entrée doit être définie une fonction statique annotée `@PHOC\Entry`. Seulement un point d'entré est autorisé par programme.  
La fonction est appelé par le runtime, stoppant ainsi son rôle d'initialisation.  
Il est ensuite à l'utilisateur d'intéragir avec PHOC.  
Un modèle naturel en développement web est la définition de "web interfaces" - des classes containant des "route handlers."  
Il est possible pour l'utilisateur de définir de telles interfaces en annotant des fonctions statiques `@PHOC\Route` et en dispatchant une requête sur une interface avec la classe `\PHOC\WebInterface`.

## Annotations

Les annotations sont des morceaux de metadata décrivant des entités d'un programme, plus précisement, des classes, fonctions et champs.  
Une annotation de PHOC est écrite dans un "doc comment" de PHP et sous les formes `@T` ou `@T(Args)` où `T` est le nom d'une classe et `Args` une liste d'expressions PHP séparées par des virgules.  
Au lieu d'une expression, un argument peut être `@Class`, `@Field`, `@Method` ou `@Function`, et sera remplacé par la constante `\PHOC\Annotations::T_*` appropriée.  
Afin d'être reconnue par le parseur, une annotation doit voir son charactère `@` être le premier sur la ligne, en ignorant les whitespaces, le première char `*` s'il y en a un, et les trois premiers charactères du commentaire `/**`.  
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
Seulement une annotation est autorisée sur chaque ligne, et toute autre information est ignorée.  
Dans le cas où un autre processeur d'annotation serait utilisé, tel que PHPDoc, il est possible d'enregistrer des annotations clées avec les fonctions `\PHOC\Annotations::RegisterAnnotationToIgnore(string): void` et `::RegisterAnnotationsToIgnore(string...): void`.  
Par défault, les annotations `@noinspection`, `@return`, `@param`, `@var` et `@throws` sont ignorées.  
Quand une annotation est reconnue, une instance de la classe associée est créée et une map contenant l'identité de l'entitée et les arguments passés, sont passés au constructeur.  
Si le nom de la classe n'est pas absolu, le processeur cherchera d'abord la classe dans le namespace de l'entité, puis dans le namespace global. Les phrases `use` de PHP ne sont pas considérées.  
La map est sous la forme `["Type" => string, "Symbol" => string]` où `Type` est l'un de `\PHOC\Annotations::T_CLASS`, `::T_FIELD`, `::T_METHOD` et `::T_FUNCTION`.  
Dans le cas où la classe indiquée n'existerais pas ou n'est pas une classe d'annotation valide, une `\PHOC\AnnotationException` est lancée. De telles exceptions ne sont pas prévues pour être capturées.  
Encore une fois, car la déduction d'annotation est enclenchée par l'autoloader, il s'agit d'un processus non-déterministe.  

Par défault, PHOC viens avec les classes d'annotations pré-définies suivantes:  
- **`\PHOC\Annotation(string...)`** - les classes annotées ceci sont des classes d'annotation résolvables.  
- **`\PHOC\ClassInit`** - les méthodes statiques annotées de cette façon sont appelées durant l'initialisation de classe, et sont prévues pour initialiser les champs statiques.  
- **`\PHOC\Entry`** - définis le point d'entrée du programme.    
- **`\PHOC\Route(string)`** - définis une route dans une interface web.  
- **`\PHOC\SessionVar([string])`** - initialise un champ avec un handle de session - un objet utilisé afin de manipuler un membre de `$_SESSION`. Le membre peut être spécifié à travers l'argument, ou est le nom du champ par défault.  
- **`\PHOC\UnitTest`** - les méthodes statiques annotées ceci sont appelées, mais uniquement en mode de debug, et sont prévues pour éffectuer des tests.  

Pour être une classe d'annotation valide, une classe doit être annotée `@PHOC\Annotation` avec les types d'entité désirés comme arguments.  
Ex (notez l'utilisation à la fois de la longue expression PHP, et de la courte syntaxe `@Method`):  
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

Les interfaces web de PHOC sont de simples classes contenant des processeurs de route.  
Les classes ne sont pas elles-mêmes reconnues d'une manière spéciale, mais les controlleurs doivent être annotés `@PHOC\Route`.  
Le constructeur de `\PHOC\Route` accepte un seul string argument: un pattern d'URI.  
Les patterns d'URI peuvent contenir plusieurs placeholders, qui sont passés à leurs controlleurs avec les valeurs acquises quand une requête est dispatched.  
Les placeholders disponibles sont:  
- **`{i}`** - nombre entier décimal.
- **`{a}`** - string alphanumerique (RegExp `[a-zA-Z0-9_]+`).
- **`{*}`** - wildcard.

Ex:  
`/user/{i}` va match l'URI `/user/42` et passera 42 au controlleur.  
`/node/{a}/static/{*}` va match l'URI `/node/october/static/surprised-seal.gif` et passera 42 et `surprised-seal.gif` au controlleur.

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

Quand une URI variable est désirée sans d'actuel paramètre, il est possible d'ajouter un charactère `?` après le symbole du placeholder.  
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

Dispatch une requête sur une interface est atteint avec `\PHOC\WebInterface::Dispatch(string, [string]): void`.  
Le premier paramètre est le nom de la classe sur laquelle dispatch. Il s'agit d'une erreur de dispatch sur une classe n'ayant pas de processeurs de route.  
Le second paramètre est l'URI contre laquelle match les routes. Sa valeur par défault est la valeur de `$_SERVER["REQUEST_URI"]`.  

Les processeurs de route sont matched contre l'URI dans leur ordre de déclaration. La déduction n'est ainsi pas basée sur leur spécialisation.  
Cela signifie que si deux processeurs sont déclarés dans cet ordre: `/{*}`, `/foo`, alors une URI `/foo` va match la première route.  
Enfin, au lieu d'un pattern d'URI, le nombre `404` peut être passé comme argument à l'annotation, auquel cas la fonction sera appelée si aucune autre n'est sélectionnée durant le dispatch.  


## XML Templating

PHOC supporte une forme de templating pseudo-XML incluant plusieurs tags XML.  
Il s'agit de "pseudo-XML" par les faits qu'aucune déclaration XML ne soit nécessaire et que le processeur soit content quand manipulant des documents HTML.  
Il est possible de compiler un fichier en HTML/PHP avec la fonction `\PHOC\Template::RenderFile(string): callable([array])`.  
Le delegate retourné peut alors être appelé afin d'éxecuter le code PHP généré. Si un array lui est passé, ses indices seront extraits dans le scope local pour être utilisés par le template.  

Ses tags spéciaux vivent dans le namespace XML `phoc`.  
Parmis eux sont deux tags dédiés à output. Ils sont également acceptés à l'intérieur de strings, avec des accolades (curly brackets) au lieu d'équerres (angle brackets).  
Dans le cas où ils sont à l'intérieur d'un string, alors les doubles guillemets doivent être changés pour des apostrophes.  
Ex: `<phoc:out var="$foo" />` et `<span class="{phoc:out var='$foo'}">...</span>`

Toute expression PHP peut être passé aux tags de PHOC, tant qu'elle respecte le type attendu.  
Le dialecte supporte également des commentaires avec une syntaxe non-orthodoxe: `<phoc:!-- commentaire ici --/>`.  
Ils peuvent être exprimés avec la RegExp `/\<phoc:!--(.*)--\/?\>/sU` _(notez que le dernier `/` est optionel)_ et sont enlevés avant l'émission du code XML ou HTML produit.

```cpp
namespace phoc
{
    //si l'expression passée à `is` est vraie, alors le corps est évalué.
    //sinon, dans le cas où un tag `phoc:else` est présent dans le corps, le code le suivant est évalué.
    node `if`
    {
        bool is;
        xml _body;
    };
    node `else` {};
    
    //pour chaque valeur dans l'array passé à `each`, la variable passée à `as` est peuplée avec la valeur actuelle et le corps évalué.
    node `for`
    {
        array each;
        any& as;
        xml _body;
    };
    
    //inclus le fichier indiqué dans l'unité de compilation actuelle.
    node include
    {
        string file;
    };
    
    //accepte un attribut inconnus, et déclare une variable avec son nom, la peuplant avec le résultat de l'expression passée.
    node def
    {
        any __any;
    };
    
    //outputs le résultat de l'expression passée à `var`.
    node out
    {
        any var;
    };
    
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