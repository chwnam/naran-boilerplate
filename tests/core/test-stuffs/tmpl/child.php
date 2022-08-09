<?php
/**
 * @var NBPC_Template_Impl $this
 */

$this->extend( 'parent' );
$this->assign( 'name', 'Expo' );

?>This is child. <?php echo esc_html( $this->fetch( 'name' ) ); ?>.