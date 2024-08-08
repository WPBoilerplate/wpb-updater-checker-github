<?php
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

/**
 * Fired during plugin license activations
 *
 * @link       https://github.com/WPBoilerplate/
 * @since      0.0.1
 *
 * @package    WPBoilerplate_Updater_Checker_Github
 * @subpackage WPBoilerplate_Updater_Checker_Github/includes
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
	 */
	class WPBoilerplate_Updater_Checker_Github {

		/**
		 * Load the licenses for the plugins
		 *
		 * @since 0.0.1
		 */
		public $packages = array();

		/**
		 * Initialize the collections used to maintain the actions and filters.
		 *
		 * @since    0.0.1
		 */
		public function __construct( $package = array() ) {

			if ( ! empty( $package ) ) {
				$this->packages[] = $package;
			}

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

					$update_checker = PucFactory::buildUpdateChecker(
						$package['repo'],
						$package['file_path'],
						$package['name_slug']
					);

					$package['release_branch'] = empty( $package['release_branch'] ) ? 'main' : $package['release_branch'];
					//Set the branch that contains the stable release.
					$update_checker->setBranch( $package['release_branch'] );

					if ( ! empty( $package['token'] ) ) {
						// Set the authentication token for private repo access
						$update_checker->setAuthentication( $package['token'] );
					}

					if ( ! empty( $package['release-assets'] ) ) {
						$update_checker->getVcsApi()->enableReleaseAssets();
					}
				}
			}
		}
	}
}