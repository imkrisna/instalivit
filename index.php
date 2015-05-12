<?php
/*
Plugin Name: IM-InstaLivit
Plugin URI: https://github.com/imkrisna/instalivit
Description: Instagram Masonry Display Feeds for Wordpress by <a href="http://www.imkrisna.com" target="_blank">imkrisna</a>.
Author: I Made Krisna Widhiastra
Version: 0.1
Author URI: http://www.imkrisna.com/
*/

$IM_INSTALIVIT_USERS 	= array();
$IM_INSTALIVIT_TAGS		= array();

function im_instalivit_add_dependency(){
	wp_enqueue_script('jquery');
	wp_enqueue_script('instalivit_js', plugins_url('instalivit.js', __FILE__));
	wp_enqueue_script('isotope', plugins_url('include/isotope.pkgd.min.js', __FILE__));
	wp_enqueue_style('instalivit_style', plugins_url('instalivit.css', __FILE__));
}

function im_instalivit_admin_menu(){
    add_options_page('InstaLivit', 'InstaLivit', 1, 'im_instalivit', 'im_instalivit_admin_page');
}

function im_instalivit_admin_page(){
	include('admin-page.php');
}

function im_instalivit_view($atts = null){
	$atts = shortcode_atts(
		array(
			'user'	=> '',
			'tags'	=> ''
		),
		$atts,
		'instalivit'
	);
	
	$IM_INSTALIVIT_USERS = array();
	if ($atts['user']){
		$IM_INSTALIVIT_USERS = explode(",", $atts['user']);
	}
	
	include('instalivit-view.php');
}

add_action('wp_enqueue_scripts', 'im_instalivit_add_dependency');
add_action('admin_menu', 'im_instalivit_admin_menu');
add_shortcode('instalivit', 'im_instalivit_view');

?>