<?php
/**
 * Shopgate GmbH
 *
 * URHEBERRECHTSHINWEIS
 *
 * Dieses Plugin ist urheberrechtlich geschützt. Es darf ausschließlich von Kunden der Shopgate GmbH
 * zum Zwecke der eigenen Kommunikation zwischen dem IT-System des Kunden mit dem IT-System der
 * Shopgate GmbH über www.shopgate.com verwendet werden. Eine darüber hinausgehende Vervielfältigung, Verbreitung,
 * öffentliche Zugänglichmachung, Bearbeitung oder Weitergabe an Dritte ist nur mit unserer vorherigen
 * schriftlichen Zustimmung zulässig. Die Regelungen der §§ 69 d Abs. 2, 3 und 69 e UrhG bleiben hiervon unberührt.
 *
 * COPYRIGHT NOTICE
 *
 * This plugin is the subject of copyright protection. It is only for the use of Shopgate GmbH customers,
 * for the purpose of facilitating communication between the IT system of the customer and the IT system
 * of Shopgate GmbH via www.shopgate.com. Any reproduction, dissemination, public propagation, processing or
 * transfer to third parties is only permitted where we previously consented thereto in writing. The provisions
 * of paragraph 69 d, sub-paragraphs 2, 3 and paragraph 69, sub-paragraph e of the German Copyright Act shall remain unaffected.
 *
 * @author Shopgate GmbH <interfaces@shopgate.com>
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

if (!class_exists('WC_Integration_Shopgate_Integration')) :
	class WC_Integration_Shopgate_Integration extends WC_Integration
	{
		public $api_key;
		public $shop_number;
		public $customer_number;
		public $enable_module;

		/**
		 * Init and hook in the integration.
		 */
		public function __construct()
		{
			$this->id                 = 'integration-shopgate';
			$this->method_title       = __('Shopgate Configuration', 'woocommerce-integration-shopgate');
			$this->method_description = __(
				'Shopgate is a WooCommerce extension to easily create apps for your store. For more information visit <a href="http://www.shopgate.com" target="_blank">www.shopgate.com</a>',
				'woocommerce-integration-shopgate'
			);

			$this->init_form_fields();
			$this->init_settings();
			$this->api_key         = $this->get_option('api_key');
			$this->shop_number     = $this->get_option('shop_number');
			$this->customer_number = $this->get_option('customer_number');
			$this->enable_module   = $this->get_option('enable_module');

			add_action('woocommerce_update_options_integration_' . $this->id, array($this, 'process_admin_options'));
		}

		/**
		 * Initialize integration settings form fields.
		 */
		public function init_form_fields()
		{
			$this->form_fields = array(
				'enable_module'   => array(
					'title'   => __('Enable/Disable', 'woocommerce-integration-shopgate'),
					'type'    => 'checkbox',
					'label'   => __('Enable Shopgate Module', 'woocommerce-integration-shopgate'),
					'default' => 'no'
				),
				'api_key'         => array(
					'title'       => __('API Key', 'woocommerce-integration-shopgate'),
					'type'        => 'text',
					'description' => __(
						'Enter with your Shopgate API Key'
					),
					'desc_tip'    => true,
					'default'     => ''
				),
				'shop_number'     => array(
					'title'       => __('Shop Number', 'woocommerce-integration-shopgate'),
					'type'        => 'text',
					'description' => __(
						'Enter with your Shopgate Shop Number'
					),
					'desc_tip'    => true,
					'default'     => ''
				),
				'customer_number' => array(
					'title'       => __('Customer Number', 'woocommerce-integration-shopgate'),
					'type'        => 'text',
					'description' => __(
						'Enter with your Shopgate Customer Number'
					),
					'desc_tip'    => true,
					'default'     => ''
				),
			);
		}
	}
endif;