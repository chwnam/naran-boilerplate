Directory for traits
--------------------
Naran boilerplace code (hereafter, CPBN. 'CPBN' is a term used call the project itself, because every 'N-B-P-C' terms is
going to be replaced.) defines some useful traits.

## Trait for actions and filters in modules
Do like this in your module class:
```php
<?php
class CPBN_Foo implements CPBN_Module {
    use CPBN_Hook_Impl; // This one.
    
    public function __construct() {
        $this
            // This trait can do:
            ->add_action( 'init', 'init' ) // method chaining.
            ->add_action( 'wp', 'wp' )     // Callback methods without arrays.
                                           // Default priority may not be 10. See CPBN_PRIORITY.
        ;
    }
    
    /**
     * @callback
     * @action    init
     */
    public function init() {
        // Your callback codes.
    }
    
    /**
     * @callback
     * @action    wp
     */
    public function wp() {
        // Your callback codes.
    }
    
}
```

* You can call class method version of add_action(), and add_filter().
* Those methods support method chaining.
* `array( $this, 'callback' )` is too long. You may provide callback method names only. 
* Your callbacks have different default priority. The new priority is defined by `CPBN_PRIORITY` constant.




## Trait for submodules
Managing submodules is easy.

* Use this trait.
* Call `assign_modules()` method in your class constructor.
* To make IDE understand what's going on in your code, doc-comment your submodules. 

```php
<?php
/**
 * Class CPBN_Foo
 * 
 * @property-read CPBN_Bar $bar 
 * @property-read CPBN_Bar $baz 
 */
class CPBN_Foo implements CPBN_Module {
    use CPBN_Submodule_Impl; // This one.
    
    public function __construct() {
        $this->assign_modules(
            [
                'bar' => CPBN_Bar::class,
                'baz' => CPBN_Baz::class,
            ]       
        );
    }
}

// Accessing:
// cpbn()->foo->bar
// cpbn()->foo->baz
```



## Trait for templating
CPBN allows simple php based templating with context variables.

Please look at this sample code:
```php
<?php
class CPBN_Foo implements CPBN_Moudle {
    use CPBN_Template_Impl; // This one.
    
    public output_something() {
        $this->render( 
            'template',
            [
                'foo' => 'foo-value',
                'bar' => 'bar-value',
            ],
            'variant'
        );
    }
}
?>

includes/templates/template-variant.php:
<?php
/**
 * Context variable:
 * 
 * @var string $foo
 * @var string $bar                
 */
 
/* ABSPATH check */ 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
```

* Your template files are stored in `includes/templates`
* Your context variables are defined as an array form.
* Template files can have variants.
* Do not forget to comment context variables in your template files.