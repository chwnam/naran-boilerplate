Directory for modules
---------------------
Naran boilerplace code (hereafter, CPBN. 'CPBN' is a term used call the project
itself, because every 'N-B-P-C' term is going to be replaced.) supports loose-coupled modules.

All modules are stored in this directory.
To find files quickly and easily, please group your files into a sub-directory.

CPBN modules follow WordPress coding style.

---

Don't forget to check `ABSPATH` constant. See below example:
```php
<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
```

---

Enclose with `if ( ! class_exists( ... ) )` to prevent name collision,
and give your users a final chance to customize their codes.
```php
<?php
if ( ! class_exists( 'CPBN_Foo' ) ) {
    interface CPBN_Foo {
    }
}
```

---

Example:
```php
<?php
/**
 * CPBN: Module sample
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CPBN_Foo' ) ) {
    class CPBN_Foo implements CPBN_Module {
    }
}
```
