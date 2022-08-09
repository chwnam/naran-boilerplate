<?php

class NBPC_Textdomain_Test {
	public function test(): string {
		return __( 'NBPC Textdomain Test', 'nbpc' );
	}
}

$nbpc_textdomain_test = new NBPC_Textdomain_Test();
?>

<div title="<?php esc_attr_e( 'Div title', 'nbpc' ); ?>">
	<?php _e( 'String', 'nbpc' ); ?>
	<?php
	$string1 = _n( 'single', 'plural', 2, 'nbpc' );
	$string2 = _nx( 'single', 'plural', 2, 'context', 'nbpc' );
	$string3 = _x( 'string', 'context', 'nbpc' );
	$string4 = __( 'nbpc' ); // This is textdomain test. This one should not be replaced by the test.
	?>
</div>
