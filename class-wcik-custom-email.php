<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Custom Email class used to send out custom emails to customers purchasing a course
 *
 * @extends \WC_Email
 */

class WC_Custom_Email extends WC_Email {
	
	/**
	 * Set email defaults
	 */

public function __construct() {
			$this->id             = 'custom_email';
			$this->customer_email = true;
			$this->title          = __( 'Custom order', 'woocommerce' );
			$this->description    = __( 'Order complete emails are sent to customers when their orders are marked completed and usually indicate that their orders have been shipped.', 'woocommerce' );
			
			
			$this->template_base  = plugin_dir_path( __FILE__ )."templates/";	// Fix the template base lookup for use on admin screen template path display
	
    
			$this->template_html  = 'emails/wcik-custom-email.php';
			$this->template_plain = 'emails/plain/wcik-custom-email.php';
			
			
			$this->placeholders   = array(
				'{site_title}'   => $this->get_blogname(),
				'{order_date}'   => '',
				'{order_number}' => '',
			);

			// Triggers for this email.
		 add_action( 'woocommerce_order_status_custom_notification', array( $this, 'trigger' ) );

			// Call parent constructor.
			parent::__construct();
			
			// Other settings.
	//	$this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
		
		

		}


/**
		 * Trigger the sending of this email.
		 *
		 * @param int            $order_id The order ID.
		 * @param WC_Order|false $order Order object.
		 */
			public function trigger( $order_id, $order = false ) {
			$this->setup_locale();

			if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
				$order = wc_get_order( $order_id );
			}

			if ( is_a( $order, 'WC_Order' ) ) {
				$this->object                         = $order;
				
				/*echo $this->recipient                      = $this->object->get_billing_email();*/
				
				$this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );
				
				$this->placeholders['{order_number}'] = $this->object->get_order_number();
			}



$admin_email = get_option('admin_email');
$customer_email = $this->object->get_billing_email();

$this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
$receipent_email =  $this->get_recipient();

$admin_customer_receipent_email = $admin_email. ", ".$customer_email.", ". $receipent_email;

			if ( $this->is_enabled()) {
			   	$this->send( $admin_customer_receipent_email, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
			}

			$this->restore_locale();
		}
	
	/**
	 * get_content_html function.
	 *
	 * @return string
	 */
		public function get_content_html() {
			return wc_get_template_html(
				$this->template_html, array(
					'order'         => $this->object,
					'email_heading' => $this->get_heading(),
					'sent_to_admin' => true,
					'plain_text'    => false,
					'email'         => $this,
				)
			);
		}

		/**
		 * Get content plain.
		 *
		 * @return string
		 */
		public function get_content_plain() {
			return wc_get_template_html(
				$this->template_plain, array(
					'order'         => $this->object,
					'email_heading' => $this->get_heading(),
					'sent_to_admin' => true,
					'plain_text'    => true,
					'email'         => $this,
				)
			);
		}

	/**
	 * Initialize settings form fields
	 */
	public function init_form_fields() {

		$this->form_fields = array(
			'enabled'    => array(
				'title'   => __( 'Enable/Disable', 'woocommerce' ),
				'type'    => 'checkbox',
				'label'   => 'Enable this email notification',
				'default' => 'yes'
			),
            'recipient'  => array(
				'title'       => 'Recipient(s)',
				'type'        => 'text',
				'description' => sprintf( 'Enter recipients (comma separated) for this email. Defaults to <code>%s</code>.', esc_attr( get_option( 'admin_email' ) ) ),
				'placeholder' => '',
				'default'     => ''
			),
			'subject'    => array(
				'title'       => __( 'Subject', 'woocommerce' ),
				'type'        => 'text',
				'description' => sprintf( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', $this->subject ),
				'placeholder' => '',
				'default'     => ''
			),
			'heading'    => array(
				'title'       => __( 'Email Heading', 'woocommerce' ),
				'type'        => 'text',
				'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.' ), $this->heading ),
				'placeholder' => '',
				'default'     => ''
			),
			'email_type' => array(
				'title'       => __( 'Email type', 'woocommerce' ),
				'type'        => 'select',
				'description' => __( 'Choose which format of email to send.', 'woocommerce' ),
				'default'       => 'html',
				'class'         => 'email_type wc-enhanced-select',
				'options'     => array(
					'plain'	    => __( 'Plain text', 'woocommerce' ),
					'html' 	    => __( 'HTML', 'woocommerce' ),
					'multipart' => __( 'Multipart', 'woocommerce' ),
				)
			)
		);
	}
		
}