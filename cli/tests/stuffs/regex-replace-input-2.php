<?php
namespace Naran\NBPC\CLI;

use Naran\NBPC\Core as NBPC_Core;
use function NBPC_Foo\Bar\func as nbpc_func;

function nbpc_foo_x( int|string|array $foo ): int|string|array|NBPC_Foo {
	return $foo;
}
