<?php
/**
 * Plugin Name: WooCommerce Custom Order Email
 * Plugin URI: http://www.imran1.com/
 * Description: Demo plugin for adding a custom WooCommerce email that sends admins an email when an order is received with Custom shipping
 * Author: Imran Khan
 * Author URI: http://www.imran1.com
 * Version: 0.1
 */
 
 
 
 function myplugin_get_plugin_path() {
	// Gets the absolute path to this plugin directory.
	return untrailingslashit( plugin_dir_path( __FILE__ ) );
}

add_filter( 'woocommerce_locate_template', 'myplugin_woocommerce_locate_template', 10, 3 );

/**
 * Custom `/templates/` directory inside a plugin.
 * 
 * @param  string $template            Full template path
 * @param  string $template_name       Template name
 * @param  string $templates_directory Templates directory.
 * @return string
 */
function myplugin_woocommerce_locate_template( $template, $template_name, $templates_directory ) {
	$original_template = $template;

	if ( ! $templates_directory ) {
		$templates_directory = WC()->template_url;
	}

    // Plugin's custom templates/ directory
	$plugin_path = myplugin_get_plugin_path() . '/templates/';

	// Look within passed path within the theme - this is priority.
	$template = locate_template(
		array(
			$templates_directory . $template_name,
			$template_name,
		)
	);

	// Get the template from this plugin under /templates/ directory, if it exists.
	if ( ! $template && file_exists( $plugin_path . $template_name ) ) {
		$template = $plugin_path . $template_name;
	}

	// Use default template if not found a suitable template under plugin's /templates/ directory.
	if ( ! $template ) {
		$template = $original_template;
	}

	// Return what we found.
	return $template;
}
    
    

 
 
 
add_filter( 'woocommerce_email_classes', 'wcik_custom_woocommerce_emails' );
function wcik_custom_woocommerce_emails( $email_classes ) {
	
    
   	require_once( plugin_dir_path( __FILE__ ) . 'class-wcik-custom-email.php' );
    
	$email_classes['WC_Custom_Email'] = new WC_Custom_Email(); // add to the list of email classes that WooCommerce loads
	return $email_classes;
	
}


// Add new Email Action
function wcik_woocommerce_email_actions( $actions ){
    $actions[] = 'woocommerce_order_status_custom';
    //var_dump($actions);
    return $actions;
}
add_filter( 'woocommerce_email_actions', 'wcik_woocommerce_email_actions' );




function wcik_register_post_statuses() {
	register_post_status( 'wc-custom', array(
		'label'						=> _x( 'Custom', 'WooCommerce Order status', 'text_domain' ),
		'public'					=> true,
		'exclude_from_search'		=> false,
		'show_in_admin_all_list'	=> true,
		'show_in_admin_status_list'	=> true,
		'label_count'				=> _n_noop( 'Approved (%s)', 'Approved (%s)', 'text_domain' )
	) );
}
add_filter( 'init', 'wcik_register_post_statuses' );


add_action('wc_order_statuses', 'wcik_custom_order_status' );

function wcik_custom_order_status($order_statuses)
{

$order_statuses['wc-custom']= "My Custom";
//var_dump($order_statuses);

return $order_statuses;

}