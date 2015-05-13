<?php
/*
Plugin Name: IM-InstaLivit
Plugin URI: https://github.com/imkrisna/instalivit
Description: Instagram Masonry Display Feeds for Wordpress by <a href="http://www.imkrisna.com" target="_blank">imkrisna</a>.
Author: I Made Krisna Widhiastra
Version: 1.0
Author URI: http://www.imkrisna.com/
*/

$IM_INSTALIVIT_USERS 	= array();
$IM_INSTALIVIT_TAGS		= array();

function im_instalivit_install(){
	global $wpdb;

	$table_name = $wpdb->prefix . "instalivit";
	$charset = $wpdb->get_charset_collate();
	
	$sql = "CREATE TABLE $table_name (
		id text NOT NULL,
		rate mediumint(9) DEFAULT -1,
		comment text DEFAULT '',
		timestamp text NOT NULL
	) $charset;";
	
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta($sql);
	
	
    $the_page_title = 'InstaLivit';
    $the_page_name = 'instalivit';

    delete_option("instalivit_page_title");
    add_option("instalivit_page_title", $the_page_title, '', 'yes');
    delete_option("instalivit_page_name");
    add_option("instalivit_page_name", $the_page_name, '', 'yes');
    delete_option("instalivit_page_id");
    add_option("instalivit_page_id", '0', '', 'yes');

    $the_page = get_page_by_title( $the_page_title );

    if ( ! $the_page ) {
        $_p = array();
        $_p['post_title'] = $the_page_title;
        $_p['post_content'] = "[instalivitdetail]";
        $_p['post_status'] = 'publish';
        $_p['post_type'] = 'page';
        $_p['comment_status'] = 'closed';
        $_p['ping_status'] = 'closed';
        $_p['post_category'] = array(1);

        $the_page_id = wp_insert_post( $_p );
    }
    else {
        $the_page_id = $the_page->ID;
        $the_page->post_status = 'publish';
        $the_page_id = wp_update_post( $the_page );
    }

    delete_option( 'instalivit_page_id' );
    add_option( 'instalivit_page_id', $the_page_id );
}
function im_instalivit_remove() {

    global $wpdb;

    $the_page_title = get_option( "instalivit_page_title" );
    $the_page_name = get_option( "instalivit_page_name" );

    $the_page_id = get_option( 'instalivit_page_id' );
    if( $the_page_id ) {
        wp_delete_post( $the_page_id );
    }

    delete_option("instalivit_page_title");
    delete_option("instalivit_page_name");
    delete_option("instalivit_page_id");
}

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
			'hashtag'	=> ''
		),
		$atts,
		'instalivit'
	);
	
	$IM_INSTALIVIT_USERS = array();
	if ($atts['user']){
		$IM_INSTALIVIT_USERS = explode(",", $atts['user']);
	}
	
	$IM_INSTALIVIT_TAGS = array();
	if ($atts['hashtag']){
		$IM_INSTALIVIT_TAGS = explode(",", $atts['hashtag']);
	}
	
	include('instalivit-view.php');
}

function im_instalivit_detail($atts = null){
	include('instalivit-detail.php');
}

function im_instalivit_detail_ajax($atts = null){
	if ( !empty($_REQUEST['ajax']) ) {
		$full_shortcode = sprintf('[%s]', include('instalivit-detail.php'));
		echo do_shortcode( $full_shortcode );
		exit;
	}	
}

register_activation_hook(__FILE__,'im_instalivit_install'); 
register_deactivation_hook( __FILE__, 'im_instalivit_remove' );

add_action('init', 'im_instalivit_detail_ajax');	
add_action('wp_enqueue_scripts', 'im_instalivit_add_dependency');
add_action('admin_menu', 'im_instalivit_admin_menu');
add_shortcode('instalivit', 'im_instalivit_view');
add_shortcode('instalivitdetail', 'im_instalivit_detail');

?>