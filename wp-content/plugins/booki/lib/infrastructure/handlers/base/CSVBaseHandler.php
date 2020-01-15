<?php 
	class Booki_CSVBaseHandler
	{
		public function __construct(){
			if(WP_DEBUG){
				remove_action( 'shutdown', 'wp_ob_end_flush_all', 1);
			}
		}
		function encode($value) {
			if(strpos($value, '"') !== false || 
				strpos($value, "\n") !== false) 
			{
				$value = str_replace('"', '""', $value);
				$value = str_replace("\n", '', $value);
			}
			return '"' . $value . '"';
		}
	}
?>
