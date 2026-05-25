<?php
/**
 * Extension assets controller.
 *
 * @package PopupMaker\ExtensionFramework
 */

namespace PopupMaker\ExtensionFramework\Controllers;

use PopupMaker\ExtensionFramework\Plugin\Controller;

defined( 'ABSPATH' ) || exit;

/**
 * Register webpack/DEWP package scripts from extension config.
 */
class Assets extends Controller {

	/**
	 * Init.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'wp_enqueue_scripts', [ $this, 'register_scripts' ], 3 );
		add_action( 'admin_enqueue_scripts', [ $this, 'register_scripts' ], 3 );
		add_action( 'wp_print_scripts', [ $this, 'autoload_styles_for_scripts' ], 3 );
		add_action( 'admin_print_scripts', [ $this, 'autoload_styles_for_scripts' ], 3 );
	}

	/**
	 * Package definitions from extension config.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public function get_packages() {
		return (array) $this->container->get( 'asset_packages' );
	}

	/**
	 * Asset meta from webpack output.
	 *
	 * @param string              $group Package group.
	 * @param array<string,mixed> $default_args Defaults.
	 * @return array<string, array<string, mixed>>
	 */
	public function get_asset_group_meta( $group, $default_args = [] ) {
		$file = $this->container->get_path( "dist/$group-assets.php" );

		$meta = (array) ( file_exists( $file ) ? require $file : [] );

		foreach ( $meta as $key => $value ) {
			$meta[ $key ] = wp_parse_args( $value, $default_args );
		}

		return $meta;
	}

	/**
	 * Register package scripts.
	 *
	 * @return void
	 */
	public function register_scripts() {
		static $registered;

		if ( $registered ) {
			return;
		}

		$registered = true;

		$packages_meta = $this->get_asset_group_meta(
			'package',
			[
				'version' => $this->container->get( 'version' ),
			]
		);

		foreach ( $this->get_packages() as $package => $package_data ) {
			if ( empty( $package_data['handle'] ) || empty( $packages_meta[ "$package.js" ] ) ) {
				continue;
			}

			$handle  = $package_data['handle'];
			$meta    = $packages_meta[ "$package.js" ];
			$js_deps = array_merge(
				$meta['dependencies'],
				isset( $package_data['deps'] ) ? (array) $package_data['deps'] : []
			);

			pum_register_script(
				$handle,
				$this->container->get_url( "dist/$package.js" ),
				$js_deps,
				$meta['version'],
				true
			);

			wp_set_script_translations( $handle, $this->container->get( 'text_domain' ) );
		}
	}

	/**
	 * Enqueue styles when scripts enqueue.
	 *
	 * @return void
	 */
	public function autoload_styles_for_scripts() {
		foreach ( $this->get_packages() as $package_data ) {
			if ( empty( $package_data['handle'] ) ) {
				continue;
			}

			$handle = $package_data['handle'];

			if ( pum_script_is( $handle, 'enqueued' ) && ! empty( $package_data['styles'] ) ) {
				pum_enqueue_style( $handle );
			}
		}
	}
}
