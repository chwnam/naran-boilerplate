<?php
namespace Naran\SMPL\CLI;

use Naran\SMPL\Core as SMPL_Core;
use function SMPL_Foo\Bar\func as smpl_func;

function smpl_foo_x( int|string|array $foo ): int|string|array|SMPL_Foo {
	return $foo;
}
