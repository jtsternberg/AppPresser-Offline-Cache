<?php
/**
 * Plugin Name: AppPresser Offline Cache
 * Plugin URI:  http://dsgnwrks.pro
 * Description: Adds functionality to allow AppPresser to download and use curated offline content.
 * Version:     0.0.0
 * Author:      jtsternberg
 * Author URI:  http://dsgnwrks.pro
 * Donate link: http://dsgnwrks.pro
 * License:     GPLv2
 * Text Domain: apppresser-offline-cache
 * Domain Path: /languages
 */

/**
 * Copyright (c) 2016 jtsternberg (email : justin@dsgnwrks.pro)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Built using generator-plugin-wp
 */


/**
 * Autoloads files with classes when needed
 *
 * @since  NEXT
 * @param  string $class_name Name of the class being requested.
 * @return void
 */
function appp_oc_autoload_classes( $class_name ) {
	if ( 0 !== strpos( $class_name, 'APOC_' ) ) {
		return;
	}

	$filename = strtolower( str_replace(
		'_', '-',
		substr( $class_name, strlen( 'APOC_' ) )
	) );

	AppPresser_Offline_Cache::include_file( $filename );
}
spl_autoload_register( 'appp_oc_autoload_classes' );


/**
 * Main initiation class
 *
 * @since  NEXT
 * @var  string $version  Plugin version
 * @var  string $basename Plugin basename
 * @var  string $url      Plugin URL
 * @var  string $path     Plugin Path
 */
class AppPresser_Offline_Cache {

	/**
	 * Current version
	 *
	 * @var  string
	 * @since  NEXT
	 */
	const VERSION = '0.0.0';

	/**
	 * URL of plugin directory
	 *
	 * @var string
	 * @since  NEXT
	 */
	protected $url = '';

	/**
	 * Path of plugin directory
	 *
	 * @var string
	 * @since  NEXT
	 */
	protected $path = '';

	/**
	 * Plugin basename
	 *
	 * @var string
	 * @since  NEXT
	 */
	protected $basename = '';

	/**
	 * Singleton instance of plugin
	 *
	 * @var AppPresser_Offline_Cache
	 * @since  NEXT
	 */
	protected static $single_instance = null;

	/**
	 * Instance of APOC_REST_Controllers
	 *
	 * @since NEXT
	 * @var APOC_REST_Controllers
	 */
	protected $controllers;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since  NEXT
	 * @return AppPresser_Offline_Cache A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Sets up our plugin
	 *
	 * @since  NEXT
	 */
	protected function __construct() {
		$this->basename = plugin_basename( __FILE__ );
		$this->url      = plugin_dir_url( __FILE__ );
		$this->path     = plugin_dir_path( __FILE__ );

		$this->plugin_classes();
	}

	/**
	 * Attach other plugin classes to the base plugin class.
	 *
	 * @since  NEXT
	 * @return void
	 */
	public function plugin_classes() {
		// Attach other plugin classes to the base plugin class.
		$this->controllers = new APOC_REST_Controllers( $this );
	} // END OF PLUGIN CLASSES FUNCTION

	/**
	 * Add hooks and filters
	 *
	 * @since  NEXT
	 * @return void
	 */
	public function hooks() {
		if ( $this->check_requirements() ) {
			add_action( 'init', array( $this, 'init' ) );
			$this->controllers->hooks();
		}
	}

	/**
	 * Activate the plugin
	 *
	 * @since  NEXT
	 * @return void
	 */
	public function _activate() {
		// Make sure any rewrite functionality has been loaded.
		flush_rewrite_rules();
	}

	/**
	 * Deactivate the plugin
	 * Uninstall routines should be in uninstall.php
	 *
	 * @since  NEXT
	 * @return void
	 */
	public function _deactivate() {}

	/**
	 * Init hooks
	 *
	 * @since  NEXT
	 * @return void
	 */
	public function init() {
		load_plugin_textdomain( 'apppresser-offline-cache', false, dirname( $this->basename ) . '/languages/' );
	}

	/**
	 * Check if the plugin meets requirements and
	 * disable it if they are not present.
	 *
	 * @since  NEXT
	 * @return boolean result of meets_requirements
	 */
	public function check_requirements() {
		if ( ! $this->meets_requirements() ) {

			// Add a dashboard notice.
			add_action( 'all_admin_notices', array( $this, 'requirements_not_met_notice' ) );

			// Deactivate our plugin.
			add_action( 'admin_init', array( $this, 'deactivate_me' ) );

			return false;
		}

		return true;
	}

	/**
	 * Deactivates this plugin, hook this function on admin_init.
	 *
	 * @since  NEXT
	 * @return void
	 */
	public function deactivate_me() {
		deactivate_plugins( $this->basename );
	}

	/**
	 * Check that all plugin requirements are met
	 *
	 * @since  NEXT
	 * @return boolean True if requirements are met.
	 */
	public static function meets_requirements() {
		// Do checks for required classes / functions
		// function_exists('') & class_exists('').
		// We have met all requirements.
		return function_exists( 'rest_api_init' ) && class_exists( 'WP_REST_Posts_Controller' );
	}

	/**
	 * Adds a notice to the dashboard if the plugin requirements are not met
	 *
	 * @since  NEXT
	 * @return void
	 */
	public function requirements_not_met_notice() {
		// Output our error.
		echo '<div id="message" class="error">';
		echo '<p>' . sprintf( __( 'AppPresser Offline Cache is missing requirements and has been <a href="%s">deactivated</a>. Please make sure all requirements are available. <em>Requirements include: At least WordPress 4.4, and the WordPress REST API plugin.</em>', 'apppresser-offline-cache' ), admin_url( 'plugins.php' ) ) . '</p>';
		echo '</div>';
	}

	public function get_option( $key = false, $fallback = false ) {
		if ( function_exists( 'appp_get_setting' ) ) {
			return appp_get_setting( $key, $fallback );
		}

		// Otherwise, duplicate appp_get_setting functionality.
		$settings = get_option( 'appp_settings' );
		$value = $settings;

		if ( $key ) {
			$setting = isset( $settings[ $key ] ) ? $settings[ $key ] : false;
			// Override value or supply fallback
			$value = apply_filters( 'apppresser_setting_default', $setting, $key, $settings, $fallback );
			if ( ! $value ) {
				$value = $fallback;
			}
		}

		return $value;
	}

	/**
	 * Magic getter for our object.
	 *
	 * @since  NEXT
	 * @param string $field Field to get.
	 * @throws Exception Throws an exception if the field is invalid.
	 * @return mixed
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'version':
				return self::VERSION;
			case 'basename':
			case 'url':
			case 'path':
			case 'controllers':
				return $this->$field;
			default:
				throw new Exception( 'Invalid '. __CLASS__ .' property: ' . $field );
		}
	}

	/**
	 * Include a file from the includes directory
	 *
	 * @since  NEXT
	 * @param  string $filename Name of the file to be included.
	 * @return bool   Result of include call.
	 */
	public static function include_file( $filename ) {
		$file = self::dir( 'includes/class-'. $filename .'.php' );
		if ( file_exists( $file ) ) {
			return include_once( $file );
		}
		return false;
	}

	/**
	 * This plugin's directory
	 *
	 * @since  NEXT
	 * @param  string $path (optional) appended path.
	 * @return string       Directory and path
	 */
	public static function dir( $path = '' ) {
		static $dir;
		$dir = $dir ? $dir : trailingslashit( dirname( __FILE__ ) );
		return $dir . $path;
	}

	/**
	 * This plugin's url
	 *
	 * @since  NEXT
	 * @param  string $path (optional) appended path.
	 * @return string       URL and path
	 */
	public static function url( $path = '' ) {
		static $url;
		$url = $url ? $url : trailingslashit( plugin_dir_url( __FILE__ ) );
		return $url . $path;
	}
}

/**
 * Grab the AppPresser_Offline_Cache object and return it.
 * Wrapper for AppPresser_Offline_Cache::get_instance()
 *
 * @since  NEXT
 * @return AppPresser_Offline_Cache  Singleton instance of plugin class.
 */
function appp_oc() {
	return AppPresser_Offline_Cache::get_instance();
}

// Kick it off.
add_action( 'plugins_loaded', array( appp_oc(), 'hooks' ) );

register_activation_hook( __FILE__, array( appp_oc(), '_activate' ) );
register_deactivation_hook( __FILE__, array( appp_oc(), '_deactivate' ) );
