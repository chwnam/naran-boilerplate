<?php

class NBPC_Textdomain_Test {
	public function test(): string {
		return __( 'NBPC Textdomain Test', 'smpl' );
	}
}

$nbpc_textdomain_test = new NBPC_Textdomain_Test();
?>

<div title="<?php esc_attr_e( 'Div title', 'smpl' ); ?>">
	<?php _e( 'String', 'smpl' ); ?>
	<?php
	$string1 = _n( 'single', 'plural', 2, 'smpl' );
	$string2 = _nx( 'single', 'plural', 2, 'context', 'smpl' );
	$string3 = _x( 'string', 'context', 'smpl' );
	$string4 = __( 'nbpc' ); // This is textdomain test. This one should not be replaced by the test.
	?>
</div>
