<?php
/*
Plugin Name: KL Role Restrict Category Shortcode
Plugin URI: https://github.com/educate-sysadmin/kl-role-restrict-categories-shortcode
Description: Wordpress plugin for shortcode access controls by category and roles
Version: 0.1
Author: b.cunningham@ucl.ac.uk
Author URI: https://educate.london
License: GPL2
*/

$klrrcc_config = array(
    'divup' => true, // surround content with divs with role classes
    'match' => 'any', // for matching multiple specified categories: any || all
);

/* Looks up if user roles has access to specified post category/s and shows content accordingly */
/* [kl_category_role_restrict category="{category}[,..]"] */ 
function klrrcc_shortcode( $atts, $content = null ) {
    global $klrrcc_config;

	$output = '';
	$class = ' klrrc '; // to populate in case needed for divup
    // parse options
	$options = shortcode_atts( array( 'category' => '' ), $atts );
	// check permissions
	$show_content = false;    
	if ( $content !== null) {
        if (!isset($options['category']) || $options['category'] === null || $options['category'] === "") {
            $show_content = true;            
        } else {
            $show_content = false;
            $categories = explode(",",$options['category']);
            foreach ($categories as $category) {
                $allowed = KLUtils::user_has_category_permissions($category, get_current_user_id());
                if ($allowed && $klrrcc_config['match'] !== 'all') { $show_content = true; }
                if (!$allowed && $klrrcc_config['match'] == 'all') { $show_content = false; }
                // also get roles allowed for divup class
                $roles = KLUtils::get_category_roles($category);
                foreach ($roles as $role) {            
                    $class .= 'klrrc-'.$role.' ';
                }
            }
        }

		if ( $show_content ) {
			remove_shortcode( 'kl_category_role_restrict' );
			$content = do_shortcode( $content );
			add_shortcode( 'kl_category_role_restrict', 'klrrcc_shortcode' );
            if ($klrrcc_config['divup']) { $output .= '<div class = "'.$class.'">'; }
			$output .= $content;
            if ($klrrcc_config['divup']) { $output .= '</div>'; }
		}
	}
	return $output;
}

add_shortcode( 'kl_category_role_restrict', 'klrrcc_shortcode' );
