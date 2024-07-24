<?php
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

/**
 * Fired during plugin license activations
 *
 * @link       https://WPBoilerplate.com
 * @since      0.0.1
 *
 * @package    Post_Anonymously
 * @subpackage Post_Anonymously/includes
 */

if ( ! class_exists( 'WPBoilerplate_Updater_Checker_Github' ) ) {

	/**
	 * Fired during plugin licenses.
	 *
	 * This class defines all code necessary to run during the plugin's licenses and update.
	 *
	 * @since      0.0.1
	 * @package    WPBoilerplate_Main_Menu_Licenses
	 * @subpackage WPBoilerplate_Main_Menu_Licenses/includes
	 * @author     WPBoilerplate <contact@WPBoilerplate.com>
	 */
	class WPBoilerplate_Updater_Checker_Github {

		/**
		 * The single instance of the class.
		 *
		 * @var Post_Anonymously_Loader
		 * @since 0.0.1
		 */
		protected static $_instance = null;

		/**
		 * Load the licenses for the plugins
		 *
		 * @since 0.0.1
		 */
		public $packages = array();

		/**
		 * Main Pin_Comment Instance.
		 *
		 * Ensures only one instance of WooCommerce is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @see Pin_Comment()
		 * @return Pin_Comment - Main instance.
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Initialize the collections used to maintain the actions and filters.
		 *
		 * @since    0.0.1
		 */
		public function __construct() {

			/**
			 * Action to do update for the plugins
			 */
			add_action( 'admin_init', array( $this, 'updater' ), 1000 );
		}

		/**
		 * Get the package list
		 */
		public function get_packages() {
			return apply_filters( 'wpboilerplate_updater_checker_github', $this->packages );
		}

		/**
		 * Update plugin if the licenses is valid
		 */
		public function updater() {

			/**
			 * Check if the $this->get_packages() is empty or not
			 */
			if ( is_admin() && ! empty( $this->get_packages() ) ) {

				foreach ( $this->get_packages() as $package ) {

					$github_repo = $package['repo'];
					$file_path = $package['file_path'];
					$name_slug = $package['name_slug'];
					$release_branch = $package['release_branch'];

					$UpdateChecker = PucFactory::buildUpdateChecker(
						$github_repo,
						$file_path,
						$name_slug
					);

					//Set the branch that contains the stable release.
					$UpdateChecker->setBranch( $release_branch );

					if ( ! empty( $package['token'] ) ) {
						// Set the authentication token for private repo access
						$UpdateChecker->setAuthentication( $package['token'] );
					}
				}
			}
		}
	}
}