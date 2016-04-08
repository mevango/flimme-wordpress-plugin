<?php
/*
Plugin Name: Flimme Frame
Plugin URI: http://flimme.com/wordpress/
Description: [flimme element=<- element-identifier -> type=<- flim|event|auth -> width=<- w -> height=<- h -> className=<- class ->] shortcode
Version: 1.0
Author: sspielvogel
License: GPLv3
*/

define('FLIMME_VERSION', '1.0');
global $_frameNo;


// Filter callbacks.

function _flimme_getHTML($params) {
    global $_frameNo;
    $output = "";
    $_frameNo++;
    $defaults = array (
        "element" => "",
        'width' => "100%",
        "height" => "100%",
        "scrolling" => "no",
        "type" => "flim",
        "frameborder" => "0",
        "id" => "iframe$_frameNo",
        "offset" => "0",
        "allowfullscreen" => "yes"
    );
    //merge default
    foreach ( $defaults as $default => $value ) {
        if ( ! @array_key_exists( $default, $params ) ) { 
            $params[$default] = $value;
        }
    }

    if ($params["element"] == "") {return "Element-Identifier not set";}
    $params["url"] = $params["element"];
    foreach ($params as $tag => $value) {
        $output = $output . flimme_render_tag($tag, $value, $params) . " ";
    }
    $output =  "<iframe $output></iframe>";
    if ( $params["height"] == "100%" ) {
        $output .= '
			<script>
			function fixHeight(obj, offset) {
                var helpFrame = jQuery("#" + obj.name);
                jQuery(window).resize(function(ev){
                    var innerDocWidth = helpFrame.width();
                    helpFrame.height((innerDocWidth/(16/9)) + offset);
                });
                var innerDocWidth = helpFrame.width();
                helpFrame.height((innerDocWidth/(16/9)) + offset);
            }
			</script>
		';
    }
    $code = $output;
    return "$code";
}


/* Rendering functions */

function flimme_render_tag($tag, $value, $params = NULL) {
    $output = $tag . '="' . $value . '"';

    if (flimme_is_themed($tag)) {
        $output = call_user_func("theme_flimme_render_" . $tag,array("value" => $value,"params" => $params));
    }
    else {
        if (function_exists("flimme_render_" . $tag)) {
            $function = "flimme_render_" . $tag;
            $output = $function($value,$params);
        }
    }
    return $output;
}

function flimme_is_themed($tag) {
    return function_exists("theme_flimme_render_" . strtolower($tag));
}


function theme_flimme_render_url($vars) {
    $id = $vars['value'];
    switch ($vars["params"]["type"]){
        case "auth":
            return 'src="https://flimme.com/events_auth/' . $id . '/"';
            break;
        case "event":
            return 'src="https://flimme.com/#/embed/events/' . $id . '"';
            break;
        default:
            return 'src="https://flimme.com/#/embed/play/' . $id . '"';
            break;
    }
}

function theme_flimme_render_id($vars) {
    return 'id="' . $vars["value"] . '" name="' . $vars["value"] .'"' ;
}


function theme_flimme_render_classname($vars) {
    return 'class="' . $vars["value"] .'"';
}


function theme_flimme_render_height($vars) {
    $output = 'height="' . $vars["value"] . '"';
    if ($vars["value"] == '100%') {
        $output .= " onload='fixHeight(this," . $vars["params"]["offset"] . ")'";
    }
    return $output;
}


add_shortcode( 'flimme', '_flimme_getHTML' );


function flimme_meta_cb( $links, $file ) {
    if ( $file == plugin_basename( __FILE__ ) ) {
        $row_meta = array(
            'support' => '<a href="mailto:support@flimme.com">Flimme Support</a>'
        );
        $links = array_merge( $links, $row_meta );
    }
    return (array) $links;
}
add_filter( 'plugin_row_meta', 'flimme_meta_cb', 10, 2 );