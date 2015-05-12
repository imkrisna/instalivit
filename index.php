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

function im_instalivit_install(){
	global $wpdb;

    $the_page_title = 'InstaLivit';
    $the_page_name = 'instalivit';

    // the menu entry...
    delete_option("instalivit_page_title");
    add_option("instalivit_page_title", $the_page_title, '', 'yes');
    // the slug...
    delete_option("instalivit_page_name");
    add_option("instalivit_page_name", $the_page_name, '', 'yes');
    // the id...
    delete_option("instalivit_page_id");
    add_option("instalivit_page_id", '0', '', 'yes');

    $the_page = get_page_by_title( $the_page_title );

    if ( ! $the_page ) {

        // Create post object
        $_p = array();
        $_p['post_title'] = $the_page_title;
        $_p['post_content'] = "This text may be overridden by the plugin. You shouldn't edit it.";
        $_p['post_status'] = 'publish';
        $_p['post_type'] = 'page';
        $_p['comment_status'] = 'closed';
        $_p['ping_status'] = 'closed';
        $_p['post_category'] = array(1); // the default 'Uncatrgorised'

        // Insert the post into the database
        $the_page_id = wp_insert_post( $_p );

    }
    else {
        // the plugin may have been previously active and the page may just be trashed...

        $the_page_id = $the_page->ID;

        //make sure the page is not trashed...
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

    //  the id of our page...
    $the_page_id = get_option( 'instalivit_page_id' );
    if( $the_page_id ) {

        wp_delete_post( $the_page_id ); // this will trash, not delete

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
			'tags'	=> ''
		),
		$atts,
		'instalivit'
	);
	
	$IM_INSTALIVIT_USERS = array();
	if ($atts['user']){
		$IM_INSTALIVIT_USERS = explode(",", $atts['user']);
	}
	
	$IM_INSTALIVIT_TAGS = array();
	if ($atts['tags']){
		$IM_INSTALIVIT_TAGS = explode(",", $atts['tags']);
	}
	
	include('instalivit-view.php');
}

register_activation_hook(__FILE__,'im_instalivit_install'); 
register_deactivation_hook( __FILE__, 'im_instalivit_remove' );

add_action('wp_enqueue_scripts', 'im_instalivit_add_dependency');
add_action('admin_menu', 'im_instalivit_admin_menu');
add_shortcode('instalivit', 'im_instalivit_view');

?>