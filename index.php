<?php
/*
Plugin Name: IM-InstaLivit
Plugin URI: https://github.com/imkrisna/instalivit
Description: Instagram Masonry Display Feeds for Wordpress by <a href="http://www.imkrisna.com" target="_blank">imkrisna</a>.
Author: I Made Krisna Widhiastra
Version: 0.1
Author URI: http://www.imkrisna.com/
*/

function im_instalivit_admin_menu(){
    add_options_page('InstaLivit', 'InstaLivit', 1, 'im_instalivit', 'im_instalivit_admin_page');
}

function im_instalivit_admin_page(){
	include('admin-page.php');
}

add_action('admin_menu', 'im_instalivit_admin_menu');

?>