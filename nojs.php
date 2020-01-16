<?php

/**
  * ----------------------------------------------------------------------------------
  * voegt extra class toe aan body en een scriptje om deze class te 
  * switchen op het moment dat JS blijkbaar aan staat
  * ----------------------------------------------------------------------------------
*/

class Genesis_Js_No_Js {
	/**
	 * Add action and filter.
	 *
	 * @since 1.0.0
	 */
	public function run() {
		add_filter( 'body_class', array( $this, 'body_class' ) );
		add_action( 'genesis_after', array( $this, 'script' ), 1 );
	}
	/**
	 * Add 'nojs' class to the body class values.
	 *
	 * @since 1.0.0
	 *
	 * @param array $classes Existing classes
	 * @return array
	 */
	public function body_class( $classes ) {
		$classes[] = 'nojs';
		return $classes;
	}
	/**
	 * Echo out the script that changes 'nojs' class to 'js'.
	 *
	 * @since 1.0.0
	 */
	public function script() {
		?>
<script type="text/javascript">
//<![CDATA[
(function(){
var c = document.body.className;
c = c.replace(/nojs/, 'dojs');
document.body.className = c;
})();
//]]>
</script>
		<?php
	}
}

