Directory for interfaces.

Your file names should start with `interface-{LowercasePrefix}-`,
and names of interfaces should start with `{UppercasePrefix}_`.

---

Don't forget to check `ABSPATH` constant. See below example:
```php
<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
```

---

Enclose with `if ( ! intrface_exists( ... ) )` to prevent name collision.
```php
<?php
if ( ! intrface_exists( 'CPBN_Foo' ) ) {
    interface CPBN_Foo {
    }
}
```
