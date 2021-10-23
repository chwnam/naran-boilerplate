Directory for abstract classes.

Your file names should start with `abstract-{LowercasePrefix}-`,
and names of classes should start with `{UppercasePrefix}_`.

---

Don't forget to check `ABSPATH` constant. See below example:
```php
<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
```

---

Enclose with `if ( ! class_exists( ... ) )` to prevent class name collision,
and give your users a final chance to customize their codes.
```php
<?php
if ( ! class_exists( 'CPBN_Foo' ) ) {
    abstract class  CPBN_Foo {
    }
}
```
