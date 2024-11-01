<?php
/**
 * Plugin Name: Shopgate Connector
 * Plugin URI: http://woothemes.com/products/woocommerce-extension/shopgate-connector
 * Description: Connect your WooCommerce store with Shopgate to create your own mobile shopping apps
 * Version: 2.9.0
 * Author: Shopgate
 * Author URI: http://www.shopgate.com/
 * Text Domain: woocommerce-extension
 * Domain Path: /languages
 *
 * Copyright: © 2009-2015 WooThemes.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
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

define("SHOPGATE_PLUGIN_VERSION", "2.9.0");

if (!class_exists('WC_Integration_Shopgate')) :
	final class WC_Integration_Shopgate
	{
		public function __construct()
		{
			$this->define_constants();
			$this->init_hooks();
		}

		private function init_hooks()
		{
			add_action('plugins_loaded', array($this, 'init_plugin'));
			add_action('wp_head', array($this, 'mobile_redirect')); //woocommerce_before_main_content
			add_action('woocommerce_order_status_changed', array($this, 'order_status_update'));
			// run installer
			//register_activation_hook(__FILE__, array('WC_Install', 'install'));
		}

		public function init_plugin()
		{
			if (class_exists('WC_Integration')) {
				include_once('includes/class-sg-integration.php');
				include_once('vendor/shopgate/shopgate.php');
				add_filter('woocommerce_integrations', array($this, 'add_integration'));
			}
		}

		/**
		 * @param $orderId
		 */
		public function order_status_update($orderId)
		{
			$order = WC()->order_factory->get_order($orderId);
			$post  = $order->post;

			if (strstr($post->post_excerpt, "Shopgate") == false) {
				return;
			}
			$parts           = explode(" ", $post->post_excerpt);
			$shopgateOrderId = array_pop($parts);
			$merchantApi     = $this->get_merchant_api();
			switch ($order->post_status) {
				case "wc-cancelled" :
					try {
						//$merchantApi->cancelOrder($shopgateOrderId);
						apply_filters('woocommerce_add_message', "Order cancelled at Shopgate");
					} catch (Exception $e) {
						apply_filters('woocommerce_add_error', "Order was not cancelled at Shopgate");
						return;
					}
					break;
				case "wc-completed" :
					try {
						//$merchantApi->setOrderShippingCompleted($shopgateOrderId);
						apply_filters('woocommerce_add_message', "Order was marked as shipped at Shopgate");
					} catch (Exception $e) {
						apply_filters('woocommerce_add_error', "Order was not marked as shipped at Shopgate");
						return;
					}
					break;
				default :
					break;
			}
		}

		public function mobile_redirect()
		{
			$wooConfig = $this->get_woo_config();
			if ($wooConfig->enable_module == "no") {
				return;
			}
			$mobileRedirect = $this->get_mobile_redirect();
			$script         = "";
			if (is_product_category()) {
				global $wp_query;
				$category = $wp_query->get_queried_object();
				$script   = $mobileRedirect->buildScriptCategory($category->term_id);
			} elseif (is_product()) {
				$productId = get_the_ID();
				$script    = $mobileRedirect->buildScriptItem($productId);
			} elseif (is_shop()) {
				$script = $mobileRedirect->buildScriptShop();
			}

			echo $script;
		}

		/**
		 * @return WC_Integration_Shopgate_Integration
		 */
		protected function get_woo_config()
		{
			return new WC_Integration_Shopgate_Integration();
		}

		/**
		 * @return ShopgateConfig
		 */
		protected function get_config()
		{
			$wooConfiguration      = $this->get_woo_config();
			$shopgateConfiguration = new ShopgateConfig();
			$shopgateConfiguration->setApikey($wooConfiguration->api_key);
			$shopgateConfiguration->setCustomerNumber($wooConfiguration->customer_number);
			$shopgateConfiguration->setShopNumber($wooConfiguration->shop_number);

			return $shopgateConfiguration;
		}

		/**
		 * @return ShopgateBuilder
		 */
		protected function get_builder()
		{
			$config = $this->get_config();

			return new ShopgateBuilder($config);
		}

		/**
		 * @return ShopgateMerchantApi
		 */
		public function get_merchant_api()
		{
			$builder = $this->get_builder();

			return $builder->buildMerchantApi();
		}

		/**
		 * @return Shopgate_Helper_Redirect_MobileRedirect
		 */
		public function get_mobile_redirect()
		{
			$builder = $this->get_builder();

			return $builder->buildMobileRedirect($_SERVER['HTTP_USER_AGENT'], $_GET, $_COOKIE);
		}

		/**
		 * @param $integrations
		 *
		 * @return array
		 */
		public function add_integration($integrations)
		{
			$integrations[] = 'WC_Integration_Shopgate_Integration';
			return $integrations;
		}

		/**
		 * Define WC Constants.
		 */
		private function define_constants()
		{
			$this->define('WC_VERSION', SHOPGATE_PLUGIN_VERSION);
		}

		/**
		 * @param $name
		 * @param $value
		 */
		private function define($name, $value)
		{
			if (!defined($name)) {
				define($name, $value);
			}
		}
	}

	$WC_Integration_Shopgate = new WC_Integration_Shopgate(__FILE__);
endif;