<?php
class Booki_BasketWidget extends WP_Widget {
	function __construct() {
		parent::__construct( 
			false
			, __('Booki - Basket/Cart', 'booki')
			, array('description'=>__('Booki basket  --Allows you to embed the mini cart via a widget', 'booki'))
			, array('id_base'=>'booki_basketwidget')
		);
	}
	public static function init(){
		register_widget( 'Booki_BasketWidget' );
	}
	function widget( $args, $instance ) {
		extract( $args );
		/* Before widget (defined by themes). */
		echo $before_widget;
		$templatePath = Booki_ThemeHelper::getTemplateFilePath('minicart.php');
		echo Booki_ThemeHelper::getTemplateRender($templatePath);
		/* After widget (defined by themes). */
		echo $after_widget;
	}

	function form( $instance ) {
		?>
		<p>
			A cart containing your bookings will be displayed by this widget.
		</p>
	<?php
	}
}