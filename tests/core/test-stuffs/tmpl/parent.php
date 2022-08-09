<?php
/**
 * @var NBPC_Template_Impl $this
 */
$this->extend( 'grandparent' );
?>
This is a parent. <?php echo esc_html( $this->fetch( 'name' ) ); ?>.