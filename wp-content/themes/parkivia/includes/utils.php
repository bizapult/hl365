<?php
/**
 * Theme tags and utilities
 *
 * @package WordPress
 * @subpackage PARKIVIA
 * @since PARKIVIA 1.0
 */

// Disable direct call
if ( ! defined( 'ABSPATH' ) ) { exit; }


/* Arrays manipulations
----------------------------------------------------------------------------------------------------- */

// Return first key (by default) or value from associative array
if (!function_exists('parkivia_array_get_first')) {
	function parkivia_array_get_first(&$arr, $key=true) {
		foreach ($arr as $k=>$v) {
			break;
		}
		return $key ? $k : $v;
	}
}

// Return keys by value from associative string: categories=1|author=0|date=0|counters=1...
if (!function_exists('parkivia_array_get_keys_by_value')) {
	function parkivia_array_get_keys_by_value($arr, $value=1, $as_string=true) {
		if (!is_array($arr)) parse_str(str_replace('|', '&', $arr), $arr);
		return $as_string ? join(',', array_keys($arr, $value)) : array_keys($arr, $value);
	}
}

// Convert list to associative array (use values as keys)
if (!function_exists('parkivia_array_from_list')) {
	function parkivia_array_from_list($arr) {
		$new = array();
		foreach ($arr as $v) $new[$v] = $v;
		return $new;
	}
}

// Merge arrays and lists (preserve number indexes)
if (!function_exists('parkivia_array_merge')) {
	function parkivia_array_merge($a1, $a2) {
		for ($i = 1; $i < func_num_args(); $i++){
			$arg = func_get_arg($i);
			if (is_array($arg) && count($arg)>0) {
				foreach($arg as $k=>$v) {
					$a1[$k] = $v;
				}
			}
		}
		return $a1;
	}
}

// Inserts any number of scalars or arrays at the point
// in the haystack immediately after the search key ($needle) was found,
// or at the end if the needle is not found or not supplied.
// Modifies $haystack in place.
// @param array &$haystack the associative array to search. This will be modified by the function
// @param string $needle the key to search for
// @param mixed $stuff one or more arrays or scalars to be inserted into $haystack
// @return int the index at which $needle was found
if (!function_exists('parkivia_array_insert')) {
	function parkivia_array_insert_after(&$haystack, $needle, $stuff){
		if (! is_array($haystack) ) return -1;

		$new_array = array();
		for ($i = 2; $i < func_num_args(); ++$i){
			$arg = func_get_arg($i);
			if (is_array($arg)) {
				if ($i==2)
					$new_array = $arg;
				else
					$new_array = parkivia_array_merge($new_array, $arg);
			} else 
				$new_array[] = $arg;
		}

		$i = 0;
		if (is_array($haystack) && count($haystack) > 0) {
			foreach($haystack as $key => $value){
				$i++;
				if ($key == $needle) break;
			}
		}

		$haystack = parkivia_array_merge(array_slice($haystack, 0, $i, true), $new_array, array_slice($haystack, $i, null, true));

		return $i;
    }
}

// Inserts any number of scalars or arrays at the point
// in the haystack immediately before the search key ($needle) was found,
// or at the end if the needle is not found or not supplied.
// Modifies $haystack in place.
// @param array &$haystack the associative array to search. This will be modified by the function
// @param string $needle the key to search for
// @param mixed $stuff one or more arrays or scalars to be inserted into $haystack
// @return int the index at which $needle was found
if (!function_exists('parkivia_array_before')) {
	function parkivia_array_insert_before(&$haystack, $needle, $stuff){
		if (! is_array($haystack) ) return -1;

		$new_array = array();
		for ($i = 2; $i < func_num_args(); ++$i){
			$arg = func_get_arg($i);
			if (is_array($arg)) {
				if ($i==2)
					$new_array = $arg;
				else
					$new_array = parkivia_array_merge($new_array, $arg);
			} else 
				$new_array[] = $arg;
		}

		$i = 0;
		if (is_array($haystack) && count($haystack) > 0) {
			foreach($haystack as $key => $value){
				if ($key === $needle) break;
				$i++;
			}
		}

		$haystack = parkivia_array_merge(array_slice($haystack, 0, $i, true), $new_array, array_slice($haystack, $i, null, true));

		return $i;
    }
}





/* HTML & CSS
----------------------------------------------------------------------------------------------------- */

// Return first tag from text
if (!function_exists('parkivia_get_tag')) {
	function parkivia_get_tag($text, $tag_start, $tag_end='') {
		$val = '';
		if (($pos_start = strpos($text, $tag_start))!==false) {
			$pos_end = $tag_end ? strpos($text, $tag_end, $pos_start) : false;
			if ($pos_end===false) {
				$tag_end = substr($tag_start, 0, 1) == '<' ? '>' : ']';
				$pos_end = strpos($text, $tag_end, $pos_start);
			}
			$val = substr($text, $pos_start, $pos_end+strlen($tag_end)-$pos_start);
		}
		return $val;
	}
}

// Return attrib from tag
if (!function_exists('parkivia_get_tag_attrib')) {
	function parkivia_get_tag_attrib($text, $tag, $attr) {
		$val = '';
		if (($pos_start = strpos($text, substr($tag, 0, strlen($tag)-1)))!==false) {
			$pos_end = strpos($text, substr($tag, -1, 1), $pos_start);
			$pos_attr = strpos($text, ' '.($attr).'=', $pos_start);
			if ($pos_attr!==false && $pos_attr<$pos_end) {
				$pos_attr += strlen($attr)+3;
				$pos_quote = strpos($text, substr($text, $pos_attr-1, 1), $pos_attr);
				$val = substr($text, $pos_attr, $pos_quote-$pos_attr);
			}
		}
		return $val;
	}
}

// Return string with position rules for the style attr
if (!function_exists('parkivia_get_css_position_from_values')) {
	function parkivia_get_css_position_from_values($top='',$right='',$bottom='',$left='',$width='',$height='') {
		if (!is_array($top)) {
			$top = compact('top','right','bottom','left','width','height');
		}
		$output = '';
		foreach ($top as $k=>$v) {
			$imp = substr($v, 0, 1);
			if ($imp == '!') $v = substr($v, 1);
			if ($v != '') $output .= ($k=='width' ? 'width' : ($k=='height' ? 'height' : 'margin-'.esc_attr($k))) . ':' . esc_attr(parkivia_prepare_css_value($v)) . ($imp=='!' ? ' !important' : '') . ';';
		}
		return $output;
	}
}

// Return value for the style attr
if (!function_exists('parkivia_prepare_css_value')) {
	function parkivia_prepare_css_value($val) {
		if ($val != '') {
			$ed = substr($val, -1);
			if ('0'<=$ed && $ed<='9') $val .= 'px';
		}
		return $val;
	}
}

// Return array with classes from css-file
if (!function_exists('parkivia_parse_icons_classes')) {
	function parkivia_parse_icons_classes($css) {
		$rez = array();
		if (!file_exists($css)) return $rez;
		$file = parkivia_fga($css);
		if (!is_array($file) || count($file) == 0) return $rez;
		foreach ($file as $row) {
			if (substr($row, 0, 1)!='.') continue;
			$name = '';
			for ($i=1; $i<strlen($row); $i++) {
				$ch = substr($row, $i, 1);
				if (in_array($ch, array(':', '{', '.', ' '))) break;
				$name .= $ch;
			}
			if ($name!='') $rez[] = $name;
		}
		return $rez;
	}
}





/* GET, POST, COOKIE, SESSION manipulations
----------------------------------------------------------------------------------------------------- */

// Strip slashes if Magic Quotes is on
if (!function_exists('parkivia_stripslashes')) {
	function parkivia_stripslashes($val) {
		static $magic = 0;
		if ($magic === 0) {
			$magic = version_compare(phpversion(), '5.4', '>=')
					|| (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()==1) 
					|| (function_exists('get_magic_quotes_runtime') && get_magic_quotes_runtime()==1);
		}
		if (is_array($val)) {
			foreach($val as $k=>$v)
				$val[$k] = parkivia_stripslashes($v);
		} else
			$val = $magic ? stripslashes(trim($val)) : trim($val);
		return $val;
	}
}

// Get GET, POST value
if (!function_exists('parkivia_get_value_gp')) {
	function parkivia_get_value_gp($name, $defa='') {
		if (isset($_GET[$name]))		$rez = wp_unslash($_GET[$name]);
		else if (isset($_POST[$name]))	$rez = wp_unslash($_POST[$name]);
		else							$rez = $defa;
		return $rez;
	}
}

// Get GET, POST, COOKIE value and save it (if need)
if (!function_exists('parkivia_get_value_gpc')) {
	function parkivia_get_value_gpc($name, $defa='', $page='', $exp=0) {
		if (isset($_GET[$name]))		 $rez = wp_unslash($_GET[$name]);
		else if (isset($_POST[$name]))	 $rez = wp_unslash($_POST[$name]);
		else if (isset($_COOKIE[$name])) $rez = wp_unslash($_COOKIE[$name]);
		else							 $rez = $defa;
		return $rez;
	}
}

// Get GET, POST, SESSION value and save it (if need)
if (!function_exists('parkivia_get_value_gps')) {
	function parkivia_get_value_gps($name, $defa='', $page='') {
		global $wp_session;
		if (isset($_GET[$name]))		  	$rez = wp_unslash($_GET[$name]);
		else if (isset($_POST[$name]))	  	$rez = wp_unslash($_POST[$name]);
		else if (isset($wp_session[$name])) $rez = wp_unslash($wp_session[$name]);
		else							  	$rez = $defa;
		return $rez;
	}
}

// Save value into session
if (!function_exists('parkivia_set_session_value')) {
	function parkivia_set_session_value($name, $value, $page='') {
		global $wp_session;
		$wp_session[$name.($page!='' ? sprintf('_%s', $page) : '')] = $value;
	}
}

// Save value into session
if (!function_exists('parkivia_del_session_value')) {
	function parkivia_del_session_value($name, $page='') {
		global $wp_session;
		unset($wp_session[$name.($page!='' ? '_'.($page) : '')]);
	}
}





/* Colors manipulations
----------------------------------------------------------------------------------------------------- */

if (!function_exists('parkivia_hex2rgb')) {
	function parkivia_hex2rgb($hex) {
		$dec = hexdec(substr($hex, 0, 1)== '#' ? substr($hex, 1) : $hex);
		return array('r'=> $dec >> 16, 'g'=> ($dec & 0x00FF00) >> 8, 'b'=> $dec & 0x0000FF);
	}
}

if (!function_exists('parkivia_hex2rgba')) {
	function parkivia_hex2rgba($hex, $alpha) {
		$rgb = parkivia_hex2rgb($hex);
		return 'rgba('.intval($rgb['r']).','.intval($rgb['g']).','.intval($rgb['b']).','.floatval($alpha).')';
	}
}

if (!function_exists('parkivia_hex2hsb')) {
	function parkivia_hex2hsb($hex, $h=0, $s=0, $b=0) {
		$hsb = parkivia_rgb2hsb(parkivia_hex2rgb($hex));
		$hsb['h'] = min(359, $hsb['h'] + $h);
		$hsb['s'] = min(100, $hsb['s'] + $s);
		$hsb['b'] = min(100, $hsb['b'] + $b);
		return $hsb;
	}
}

if (!function_exists('parkivia_rgb2hsb')) {
	function parkivia_rgb2hsb($rgb) {
		$hsb = array();
		$hsb['b'] = max(max($rgb['r'], $rgb['g']), $rgb['b']);
		$hsb['s'] = ($hsb['b'] <= 0) ? 0 : round(100*($hsb['b'] - min(min($rgb['r'], $rgb['g']), $rgb['b'])) / $hsb['b']);
		$hsb['b'] = round(($hsb['b'] /255)*100);
		if (($rgb['r']==$rgb['g']) && ($rgb['g']==$rgb['b'])) $hsb['h'] = 0;
		else if($rgb['r']>=$rgb['g'] && $rgb['g']>=$rgb['b']) $hsb['h'] = 60*($rgb['g']-$rgb['b'])/($rgb['r']-$rgb['b']);
		else if($rgb['g']>=$rgb['r'] && $rgb['r']>=$rgb['b']) $hsb['h'] = 60  + 60*($rgb['g']-$rgb['r'])/($rgb['g']-$rgb['b']);
		else if($rgb['g']>=$rgb['b'] && $rgb['b']>=$rgb['r']) $hsb['h'] = 120 + 60*($rgb['b']-$rgb['r'])/($rgb['g']-$rgb['r']);
		else if($rgb['b']>=$rgb['g'] && $rgb['g']>=$rgb['r']) $hsb['h'] = 180 + 60*($rgb['b']-$rgb['g'])/($rgb['b']-$rgb['r']);
		else if($rgb['b']>=$rgb['r'] && $rgb['r']>=$rgb['g']) $hsb['h'] = 240 + 60*($rgb['r']-$rgb['g'])/($rgb['b']-$rgb['g']);
		else if($rgb['r']>=$rgb['b'] && $rgb['b']>=$rgb['g']) $hsb['h'] = 300 + 60*($rgb['r']-$rgb['b'])/($rgb['r']-$rgb['g']);
		else $hsb['h'] = 0;
		$hsb['h'] = round($hsb['h']);
		return $hsb;
	}
}

if (!function_exists('parkivia_hsb2rgb')) {
	function parkivia_hsb2rgb($hsb) {
		$rgb = array();
		$h = round($hsb['h']);
		$s = round($hsb['s']*255/100);
		$v = round($hsb['b']*255/100);
		if ($s == 0) {
			$rgb['r'] = $rgb['g'] = $rgb['b'] = $v;
		} else {
			$t1 = $v;
			$t2 = (255-$s)*$v/255;
			$t3 = ($t1-$t2)*($h%60)/60;
			if ($h==360) $h = 0;
			if ($h<60) { 		$rgb['r']=$t1; $rgb['b']=$t2; $rgb['g']=$t2+$t3; }
			else if ($h<120) {	$rgb['g']=$t1; $rgb['b']=$t2; $rgb['r']=$t1-$t3; }
			else if ($h<180) {	$rgb['g']=$t1; $rgb['r']=$t2; $rgb['b']=$t2+$t3; }
			else if ($h<240) {	$rgb['b']=$t1; $rgb['r']=$t2; $rgb['g']=$t1-$t3; }
			else if ($h<300) {	$rgb['b']=$t1; $rgb['g']=$t2; $rgb['r']=$t2+$t3; }
			else if ($h<360) {	$rgb['r']=$t1; $rgb['g']=$t2; $rgb['b']=$t1-$t3; }
			else {				$rgb['r']=0;   $rgb['g']=0;   $rgb['b']=0; }
		}
		return array('r'=>round($rgb['r']), 'g'=>round($rgb['g']), 'b'=>round($rgb['b']));
	}
}

if (!function_exists('parkivia_rgb2hex')) {
	function parkivia_rgb2hex($rgb) {
		$hex = array(
			dechex($rgb['r']),
			dechex($rgb['g']),
			dechex($rgb['b'])
		);
		return '#'.(strlen($hex[0])==1 ? '0' : '').($hex[0]).(strlen($hex[1])==1 ? '0' : '').($hex[1]).(strlen($hex[2])==1 ? '0' : '').($hex[2]);
	}
}

if (!function_exists('parkivia_hsb2hex')) {
	function parkivia_hsb2hex($hsb) {
		return parkivia_rgb2hex(parkivia_hsb2rgb($hsb));
	}
}






/* String manipulations
----------------------------------------------------------------------------------------------------- */

// Replace macros in the string
if (!function_exists('parkivia_prepare_macros')) {
	function parkivia_prepare_macros($str) {
		return str_replace(
			array("{{",  "}}",   "((",  "))",   "||"),
			array("<i>", "</i>", "<b>", "</b>", "<br>"),
			$str);
	}
}

// Remove macros from the string
if (!function_exists('parkivia_remove_macros')) {
	function parkivia_remove_macros($str) {
		return str_replace(
			array("{{", "}}", "((", "))", "||"),
			array("",   "",   "",   "",   " "),
			$str);
	}
}

// Check value for "on" | "off" | "inherit" values
if (!function_exists('parkivia_is_on')) {
	function parkivia_is_on($prm) {
		return $prm>0 || in_array(strtolower($prm), array('true', 'on', 'yes', 'show'));
	}
}
if (!function_exists('parkivia_is_off')) {
	function parkivia_is_off($prm) {
		return empty($prm) || $prm===0 || in_array(strtolower($prm), array('false', 'off', 'no', 'none', 'hide'));
	}
}
if (!function_exists('parkivia_is_inherit')) {
	function parkivia_is_inherit($prm) {
		return in_array(strtolower($prm), array('inherit'));
	}
}

// Return truncated string
if (!function_exists('parkivia_strshort')) {
	function parkivia_strshort($str, $maxlength, $add='...') {
		if ($maxlength < 0)
			return '';
		if ($maxlength == 0)
			return '';
		if ($maxlength >= strlen($str)) 
			return strip_tags($str);
		$str = substr(strip_tags($str), 0, $maxlength - strlen($add));
		$ch = substr($str, $maxlength - strlen($add), 1);
		if ($ch != ' ') {
			for ($i = strlen($str) - 1; $i > 0; $i--) {
				if (substr($str, $i, 1) == ' ') break;
			}
			$str = trim(substr($str, 0, $i));
		}
		if (!empty($str) && strpos(',.:;-', substr($str, -1))!==false) $str = substr($str, 0, -1);
		return ($str) . ($add);
	}
}

// Unserialize string (try replace \n with \r\n)
if (!function_exists('parkivia_unserialize')) {
	function parkivia_unserialize($str) {
		if ( is_serialized($str) ) {
			try {
				$data = unserialize($str);
			} catch (Exception $e) {
				dcl($e->getMessage());
				$data = false;
			}
			if ($data===false) {
				try {
					$data = unserialize(str_replace("\n", "\r\n", $str));
				} catch (Exception $e) {
					dcl($e->getMessage());
					$data = false;
				}
			}
			return $data;
		} else
			return $str;
	}
}


// str_replace with arrays and serialize support
function parkivia_str_replace($from, $to, $str) {
	if (is_array($str) && count($str) > 0) {
		foreach ($str as $k=>$v) {
			$str[$k] = parkivia_str_replace($from, $to, $v);
		}
	} else if (is_string($str)) {
		$str = trx_addons_unserialize($str);
		$str = is_array($str)
						? serialize(parkivia_str_replace($from, $to, $str))
						: str_replace($from, $to, $str);
	}
	return $str;
}



/* Media: images, galleries, audio, video
----------------------------------------------------------------------------------------------------- */

// Get image sizes from image url (if image in the uploads folder)
if (!function_exists('parkivia_getimagesize')) {
	function parkivia_getimagesize($url) {
		// Remove scheme from url
		$url = parkivia_remove_protocol_from_url($url);
	
		// Get upload path & dir
		$upload_info = wp_upload_dir();

		// Where check file
		$locations = array(
			'uploads' => array(
				'dir' => $upload_info['basedir'],
				'url' => parkivia_remove_protocol_from_url($upload_info['baseurl'])
				),
			'child' => array(
				'dir' => get_stylesheet_directory(),
				'url' => parkivia_remove_protocol_from_url(get_stylesheet_directory_uri())
				),
			'theme' => array(
				'dir' => get_template_directory(),
				'url' => parkivia_remove_protocol_from_url(get_template_directory_uri())
				)
			);
		
		$img_size = false;

		foreach ($locations as $key=>$loc) {

			// Check if $img_url is local.
			if ( false === strpos($url, $loc['url']) ) continue;
			
			// Get path of image.
			$img_path = str_replace($loc['url'], $loc['dir'], $url);
		
			// Check if img path exists, and is an image indeed.
			if ( !file_exists($img_path)) continue;
	
			// Get image size
			$img_size = getimagesize($img_path);
			break;
		}
		
		return $img_size;
	}
}

// Clear thumb sizes from image name
if (!function_exists('parkivia_clear_thumb_size')) {
	function parkivia_clear_thumb_size($url) {
		$pi = pathinfo($url);
		$pi['dirname'] = parkivia_remove_protocol_from_url($pi['dirname'], false);
		$parts = explode('-', $pi['filename']);
		$suff = explode('x', $parts[count($parts)-1]);
		if (count($suff)==2 && (int) $suff[0] > 0 && (int) $suff[1] > 0) {
			array_pop($parts);
			$url = $pi['dirname'] . '/' . join('-', $parts) . '.' . $pi['extension'];
		}
		return $url;
	}
}

// Add thumb sizes to image name
if (!function_exists('parkivia_add_thumb_size')) {
	function parkivia_add_thumb_size($url, $thumb_size, $check_exists=true) {
		$pi = pathinfo($url);
		$pi['dirname'] = parkivia_remove_protocol_from_url($pi['dirname'], false);
		$parts = explode('-', $pi['filename']);
		// Remove image sizes from filename
		$suff = explode('x', $parts[count($parts)-1]);
		if (count($suff)==2 && (int) $suff[0] > 0 && (int) $suff[1] > 0) {
			array_pop($parts);
		}
		// Add new image sizes
		global $_wp_additional_image_sizes;
		if ( isset( $_wp_additional_image_sizes ) && count( $_wp_additional_image_sizes ) && in_array( $thumb_size, array_keys( $_wp_additional_image_sizes ) ) ) {
			if (intval( $_wp_additional_image_sizes[$thumb_size]['width'] ) > 0 && intval( $_wp_additional_image_sizes[$thumb_size]['height'] ) > 0)
				$parts[] = intval( $_wp_additional_image_sizes[$thumb_size]['width'] ) . 'x' . intval( $_wp_additional_image_sizes[$thumb_size]['height'] );
		}
		$pi['filename'] = join('-', $parts);
		$new_url = $pi['dirname'] . '/' . $pi['filename'] . '.' . $pi['extension'];
		// Check exists
		if ($check_exists) {
			$uploads_info = wp_upload_dir();
			$uploads_url = parkivia_remove_protocol_from_url($uploads_info['baseurl'], false);
			$uploads_dir = $uploads_info['basedir'];
			if (strpos($new_url, $uploads_url)!==false) {
				if (!file_exists(str_replace($uploads_url, $uploads_dir, $new_url)))
					$new_url = $url;
			} else {
				$new_url = $url;
			}
		}
		return $new_url;
	}
}

// Return image size multiplier
if (!function_exists('parkivia_get_thumb_size')) {
	function parkivia_get_thumb_size($ts) {
		$retina = parkivia_get_retina_multiplier() > 1 ? '-@retina' : '';
		return ($ts=='post-thumbnail' ? '' : 'parkivia-thumb-') . $ts . $retina;
	}
}


// Return image url by attachment ID
if (!function_exists('parkivia_get_attachment_url')) {
	function parkivia_get_attachment_url($image_id, $size='full') {
		if ($image_id > 0) {
			$attach = wp_get_attachment_image_src($image_id, $size);
			$image_id = isset($attach[0]) && $attach[0]!='' ? $attach[0] : '';
		} else
			$image_id = parkivia_add_thumb_size($image_id, $size);
		return $image_id;
	}
}


// Return url from first <img> tag inserted in post
if (!function_exists('parkivia_get_post_image')) {
	function parkivia_get_post_image($post_text='', $src=true) {
		global $post;
		$img = '';
		if (empty($post_text)) $post_text = $post->post_content;
		if (preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $post_text, $matches)) {
			$img = $matches[$src ? 1 : 0][0];
		}
		return $img;
	}
}


// Return url from first <audio> tag inserted in post
if (!function_exists('parkivia_get_post_audio')) {
	function parkivia_get_post_audio($post_text='', $src=true) {
		global $post;
		$img = '';
		if (empty($post_text)) $post_text = $post->post_content;
		if ($src) {
			if (preg_match_all('/<audio.+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $post_text, $matches)) {
				$img = $matches[1][0];
			}
		} else {
			$img = parkivia_get_tag($post_text, '<audio', '</audio>');
			if (empty($img))
				$img = do_shortcode(parkivia_get_tag($post_text, '[audio', '[/audio]'));
			if (empty($img)) {
				$img = parkivia_get_tag_attrib($post_text, '[trx_widget_audio]', 'url');
				if (!empty($img))
					$img = '<audio src="'.esc_url($img).'">';
			}
		}
		return $img;
	}
}


// Return url from first <video> tag inserted in post
if (!function_exists('parkivia_get_post_video')) {
	function parkivia_get_post_video($post_text='', $src=true) {
		global $post;
		$img = '';
		if (empty($post_text)) $post_text = do_shortcode($post->post_content);
		if ($src) {
			if (preg_match_all('/<video.+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $post_text, $matches)) {
				$img = $matches[1][0];
			}
		} else {
			$img = parkivia_get_tag($post_text, '<video', '</video>');
			if (empty($img)) {
				$sc = parkivia_get_tag($post_text, '[video', '[/video]');
				if (empty($sc)) $sc = parkivia_get_tag($post_text, '[trx_widget_video', '');
				if (!empty($sc)) $img = do_shortcode($sc);
			}
		}
		return $img;
	}
}


// Return url from first <iframe> tag inserted in post
if (!function_exists('parkivia_get_post_iframe')) {
	function parkivia_get_post_iframe($post_text='', $src=true) {
		global $post;
		$img = '';
		if (empty($post_text)) $post_text = do_shortcode($post->post_content);
		if ($src) {
			if (preg_match_all('/<iframe.+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $post_text, $matches)) {
				$img = $matches[1][0];
			}
		} else
			$img = parkivia_get_tag($post_text, '<iframe', '</iframe>');
		return apply_filters('parkivia_filter_get_post_iframe', $img);
	}
}


// Add 'autoplay' feature in the video
if (!function_exists('parkivia_make_video_autoplay')) {
	function parkivia_make_video_autoplay($video) {
		if (($pos = strpos($video, '<video'))!==false) {
			$video = str_replace('<video', '<video autoplay="autoplay"', $video);
		} else if (($pos = strpos($video, '<iframe'))!==false) {
			if (preg_match('/(<iframe.+src=[\'"])([^\'"]+)([\'"][^>]*>)(.*)/i', $video, $matches)) {
				$video = $matches[1] . $matches[2] . (strpos($matches[2], '?')!==false ? '&' : '?') . 'autoplay=1' . $matches[3] . $matches[4];
			}
		}
		return $video;
	}
}

// Check if image in the uploads folder
if (!function_exists('parkivia_is_from_uploads')) {
	function parkivia_is_from_uploads($url) {
		$local = false;
		$url = parkivia_remove_protocol_from_url($url);
		$uploads_info = wp_upload_dir();
		$uploads_url = parkivia_remove_protocol_from_url($uploads_info['baseurl']);
		$uploads_dir = $uploads_info['basedir'];
		return $local = strpos($url, $uploads_url)!==false && file_exists(str_replace($uploads_url, $uploads_dir, $url));
	}
}

// Check if URL from YouTube
if (!function_exists('parkivia_is_youtube_url')) {
	function parkivia_is_youtube_url($url) {
		return strpos($url, 'youtu.be')!==false || strpos($url, 'youtube.com')!==false;
	}
}

// Check if URL from Vimeo
if (!function_exists('parkivia_is_vimeo_url')) {
	function parkivia_is_vimeo_url($url) {
		return strpos($url, 'vimeo.com')!==false;
	}
}


/* Init WP Filesystem before theme init
------------------------------------------------------------------- */
if (!function_exists('parkivia_init_filesystem')) {
	add_action( 'after_setup_theme', 'parkivia_init_filesystem', 0);
	function parkivia_init_filesystem() {
        if( !function_exists('WP_Filesystem') ) {
            require_once trailingslashit(ABSPATH) .'wp-admin/includes/file.php';
        }
		if (is_admin()) {
			$url = admin_url();
			$creds = false;
			// First attempt to get credentials.
			if ( function_exists('request_filesystem_credentials') && false === ( $creds = request_filesystem_credentials( $url, '', false, false, array() ) ) ) {
				// If we comes here - we don't have credentials
				// so the request for them is displaying no need for further processing
				return false;
			}
	
			// Now we got some credentials - try to use them.
			if ( !WP_Filesystem( $creds ) ) {
				// Incorrect connection data - ask for credentials again, now with error message.
				if ( function_exists('request_filesystem_credentials') ) request_filesystem_credentials( $url, '', true, false );
				return false;
			}
			
			return true; // Filesystem object successfully initiated.
		} else {
            WP_Filesystem();
		}
		return true;
	}
}


// Put data into specified file
if (!function_exists('parkivia_fpc')) {	
	function parkivia_fpc($file, $data, $flag=0) {
		global $wp_filesystem;
		if (!empty($file)) {
			if (isset($wp_filesystem) && is_object($wp_filesystem)) {
				$file = str_replace(ABSPATH, $wp_filesystem->abspath(), $file);
				// Attention! WP_Filesystem can't append the content to the file!
				// That's why we have to read the contents of the file into a string,
				// add new content to this string and re-write it to the file if parameter $flag == FILE_APPEND!
				return $wp_filesystem->put_contents($file, ($flag==FILE_APPEND && $wp_filesystem->exists($file) ? $wp_filesystem->get_contents($file) : '') . $data, false);
			} else {
				if (parkivia_is_on(parkivia_get_theme_option('debug_mode'))) {
					// Translators: Add the file name to the message
					throw new Exception(sprintf(esc_html__('WP Filesystem is not initialized! Put contents to the file "%s" failed', 'parkivia'), $file));
				}
			}
		}
		return false;
	}
}

// Get text from specified file
if (!function_exists('parkivia_fgc')) {	
	function parkivia_fgc($file) {
		global $wp_filesystem;
		if (!empty($file)) {
			if (isset($wp_filesystem) && is_object($wp_filesystem)) {
				$file = str_replace(ABSPATH, $wp_filesystem->abspath(), $file);
				return $wp_filesystem->get_contents($file);
			} else {
				if (parkivia_is_on(parkivia_get_theme_option('debug_mode'))) {
					// Translators: Add the file name to the message
					throw new Exception(sprintf(esc_html__('WP Filesystem is not initialized! Get contents from the file "%s" failed', 'parkivia'), $file));
				}
			}
		}
		return '';
	}
}

// Get array with rows from specified file
if (!function_exists('parkivia_fga')) {	
	function parkivia_fga($file) {
		global $wp_filesystem;
		if (!empty($file)) {
			if (isset($wp_filesystem) && is_object($wp_filesystem)) {
				$file = str_replace(ABSPATH, $wp_filesystem->abspath(), $file);
				return $wp_filesystem->get_contents_array($file);
			} else {
				if (parkivia_is_on(parkivia_get_theme_option('debug_mode'))) {
					// Translators: Add the file name to the message
					throw new Exception(sprintf(esc_html__('WP Filesystem is not initialized! Get rows from the file "%s" failed', 'parkivia'), $file));
				}
			}
		}
		return array();
	}
}

// Remove unsafe characters from file/folder path
if (!function_exists('parkivia_esc')) {	
	function parkivia_esc($file) {
		return str_replace(array('\\', '~', '$', ':', ';', '+', '>', '<', '|', '"', "'", '`', "\xFF", "\x0A", "\x0D", '*', '?', '^'), '/', trim($file));
	}
}


// Return .min version (if exists and filetime .min > filetime original) instead original
if (!function_exists('parkivia_check_min_file')) {	
	function parkivia_check_min_file($file, $dir) {
		if (substr($file, -3)=='.js') {
			if (substr($file, -7)!='.min.js' && parkivia_is_off(parkivia_get_theme_option('debug_mode'))) {
				$dir = trailingslashit($dir);
				$file_min = substr($file, 0, strlen($file)-3).'.min.js';
				if (file_exists($dir . $file_min) && filemtime($dir . $file) <= filemtime($dir . $file_min)) $file = $file_min;
			}
		} else if (substr($file, -4)=='.css') {
			if (substr($file, -8)!='.min.css' && parkivia_is_off(parkivia_get_theme_option('debug_mode'))) {
				$dir = trailingslashit($dir);
				$file_min = substr($file, 0, strlen($file)-4).'.min.css';
				if (file_exists($dir . $file_min) && filemtime($dir . $file) <= filemtime($dir . $file_min)) $file = $file_min;
			}
		}
		return $file;
	}
}


// Check if file/folder present in the child theme and return path (url) to it. 
// Else - path (url) to file in the main theme dir
// If file not exists (component is not included in the theme's light version) - return empty string
if (!function_exists('parkivia_get_file_dir')) {	
	function parkivia_get_file_dir($file, $return_url=false) {
		// Use new WordPress functions (if present)
		if ( function_exists('get_theme_file_path') && !parkivia_get_theme_setting('check_min_version') ) {
			$dir = get_theme_file_path($file);
			$dir = file_exists($dir)
						? ($return_url ? get_theme_file_uri($file) : $dir)
						: '';
		// Otherwise (on WordPress older then 4.7.0) or theme use .min versions of .js and .css
		} else {
			if ($file[0]=='/') $file = substr($file, 1);
			$theme_dir = get_template_directory();
			$theme_url = get_template_directory_uri();
			$child_dir = get_stylesheet_directory();
			$child_url = get_stylesheet_directory_uri();
			$dir = '';
			if (file_exists(($child_dir).'/'.($file)))
				$dir = ($return_url ? $child_url : $child_dir).'/'.parkivia_check_min_file($file, $child_dir);
			else if (file_exists(($theme_dir).'/'.($file)))
				$dir = ($return_url ? $theme_url : $theme_dir).'/'.parkivia_check_min_file($file, $theme_dir);
		}
		return apply_filters( $return_url ? 'parkivia_get_file_url' : 'parkivia_get_file_dir', $dir, $file );
	}
}

if (!function_exists('parkivia_get_file_url')) {	
	function parkivia_get_file_url($file) {
		return parkivia_get_file_dir($file, true);
	}
}

// Return file extension from full name/path
if (!function_exists('parkivia_get_file_ext')) {	
	function parkivia_get_file_ext($file) {
		$ext = pathinfo($file, PATHINFO_EXTENSION);
		return empty($ext) ? '' : $ext;
	}
}

// Return file name from full name/path
if (!function_exists('parkivia_get_file_name')) {	
	function parkivia_get_file_name($file, $without_ext=true) {
		$parts = pathinfo($file);
		return !empty($parts['filename']) && $without_ext ? $parts['filename'] : $parts['basename'];
	}
}

// Detect folder location with same algorithm as file (see above)
if (!function_exists('parkivia_get_folder_dir')) {	
	function parkivia_get_folder_dir($folder, $return_url=false) {
		if ($folder[0]=='/') $folder = substr($folder, 1);
		$theme_dir = get_template_directory();
		$theme_url = get_template_directory_uri();
		$child_dir = get_stylesheet_directory();
		$child_url = get_stylesheet_directory_uri();
		$dir = '';
		if (is_dir(($child_dir).'/'.($folder)))
			$dir = ($return_url ? $child_url : $child_dir).'/'.($folder);
		else if (file_exists(($theme_dir).'/'.($folder)))
			$dir = ($return_url ? $theme_url : $theme_dir).'/'.($folder);
		return $dir;
	}
}

if (!function_exists('parkivia_get_folder_url')) {	
	function parkivia_get_folder_url($folder) {
		return parkivia_get_folder_dir($folder, true);
	}
}


// Merge all separate styles and scripts to the single file to increase page upload speed
if ( !function_exists( 'parkivia_merge_files' ) ) {
	function parkivia_merge_files($to, $list) {
		$s = '';
		foreach ($list as $f) {
			$s .= parkivia_fgc(parkivia_get_file_dir($f));
		}
		if ( $s != '') {
			parkivia_fpc( parkivia_get_file_dir($to), 
				'/* ' 
				. strip_tags( __("ATTENTION! This file was generated automatically! Don't change it!!!", 'parkivia') ) 
				. "\n----------------------------------------------------------------------- */\n"
				. strpos($to, '.js')!==false 
						? apply_filters( 'parkivia_filter_prepare_js', $s, true ) 
						: apply_filters( 'parkivia_filter_prepare_css', $s, true )
				);
		}
	}
}


// Merge styles to the SASS file
if ( !function_exists( 'parkivia_merge_sass' ) ) {
	function parkivia_merge_sass($to, $list, $need_responsive=false, $root='../') {
		if ($need_responsive) $responsive = apply_filters('parkivia_filter_sass_responsive', parkivia_storage_get('responsive'));
		$sass = array(
			'import' => '',
			'sizes'  => array()
			);
		$save = false;
		$sass_special_symbol = '@';
		$sass_required = "{$sass_special_symbol}required";
		$sass_include = "{$sass_special_symbol}include";
		$sass_import = "{$sass_special_symbol}import";
		foreach ($list as $f) {
			$add = false;
			if (($fdir = parkivia_get_file_dir($f)) != '') {
				if ($need_responsive) {
					$css = parkivia_fgc($fdir);
					if (strpos($css, $sass_required) !== false) $add = true;
					foreach ($responsive as $k=>$v) {
						if (preg_match("/([\d\w\-_]+--{$k})\(/", $css, $matches)) {
							$sass['sizes'][$k] = (!empty($sass['sizes'][$k]) ? $sass['sizes'][$k] : '') . "\t{$sass_include} {$matches[1]}();\n";
							$add = true;
						}
					}
				} else
					$add = true;
			}
			if ($add) {
				$sass['import'] .= apply_filters('parkivia_filter_sass_import', "{$sass_import} \"{$root}{$f}\";\n", $f);
				$save = true;
			}
		}
		if ($save) {
			$output = '/* ' 
					. strip_tags( __("ATTENTION! This file was generated automatically! Don't change it!!!", 'parkivia') ) 
					. "\n----------------------------------------------------------------------- */\n"
					. $sass['import'];
			if ($need_responsive) {
				foreach ($responsive as $k => $v) {
					if (!empty($sass['sizes'][$k])) {
						$output .= "\n\n"
								. strip_tags( sprintf( __("/* SASS Suffix: --%s */", 'parkivia'), $k) )
								. "\n"
								. "@media " . (!empty($v['min']) ? "(min-width: {$v['min']}px)" : '')
											. (!empty($v['min']) && !empty($v['max']) ? ' and ' : '')
											. (!empty($v['max']) ? "(max-width: {$v['max']}px)" : '')
											. " {\n"
												. $sass['sizes'][$k]
											. "}\n";
					}
				}
			}
			parkivia_fpc( parkivia_get_file_dir($to), apply_filters('parkivia_filter_sass_output', $output, $to) );
		}
	}
}



// Remove protocol part from URL
// complete - true - remove protocol: and //
//			  false - remove protocol: only
if ( ! function_exists( 'parkivia_remove_protocol_from_url' ) ) {
	function parkivia_remove_protocol_from_url( $url, $complete = true ) {
		return preg_replace( '/(http[s]?:)?' . ( $complete ? '\\/\\/' : '' ) . '/', '', $url );
	}
}

// Add parameters to URL
if (!function_exists('parkivia_add_to_url')) {
	function parkivia_add_to_url($url, $prm) {
		if (is_array($prm) && count($prm) > 0) {
			$separator = strpos($url, '?')===false ? '?' : '&';
			foreach ($prm as $k=>$v) {
				$url .= $separator . urlencode($k) . '=' . urlencode($v);
				$separator = '&';
			}
		}
		return $url;
	}
}
?>