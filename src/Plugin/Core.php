<?php
/**
 * Extension plugin core base.
 *
 * @package PopupMaker\ExtensionFramework
 */

namespace PopupMaker\ExtensionFramework\Plugin;

use PopupMaker\ExtensionFramework\Controllers\Admin\ProUpsell;
use PopupMaker\ExtensionFramework\Controllers\Assets;
use PopupMaker\ExtensionFramework\Services\License;

defined( 'ABSPATH' ) || exit;

/**
 * Base container for standalone Popup Maker extensions.
 */
abstract class Core extends \PopupMaker\Plugin\Extension {

	/**
	 * Constructor.
	 *
	 * @param array<string, mixed> $config Plugin config.
	 */
	public function __construct( $config ) {
		parent::__construct( $config );

		add_filter( 'pum_enabled_extensions', [ $this, 'register_extension' ] );
		add_action( 'init', [ $this, 'load_textdomain' ] );

		$this->get( 'license' )->init();
	}

	/**
	 * Register shared framework services.
	 *
	 * @return void
	 */
	public function register_services() {
		$this->set(
			'license',
			function () {
				return new License( $this );
			}
		);
	}

	/**
	 * Framework + extension controllers.
	 *
	 * @return array<string, \PopupMaker\Interfaces\Controller>
	 */
	protected function registered_controllers() {
		return array_merge(
			$this->framework_controllers(),
			$this->extension_controllers()
		);
	}

	/**
	 * Shared framework controllers.
	 *
	 * @return array<string, \PopupMaker\Interfaces\Controller>
	 */
	protected function framework_controllers() {
		$controllers = [
			'Assets' => new Assets( $this ),
		];

		if ( $this->should_register_pro_upsell() ) {
			$controllers['Admin\\ProUpsell'] = new ProUpsell( $this );
		}

		return $controllers;
	}

	/**
	 * Whether to register the Pro migration upsell controller.
	 *
	 * @return bool
	 */
	protected function should_register_pro_upsell() {
		if ( ! $this->offsetExists( 'pro_upsell' ) ) {
			return true;
		}

		return false !== $this->get( 'pro_upsell' );
	}

	/**
	 * Extension-specific controllers.
	 *
	 * @return array<string, \PopupMaker\Interfaces\Controller>
	 */
	abstract protected function extension_controllers();

	/**
	 * Register extension with core.
	 *
	 * @param array<string, mixed> $extensions Extensions.
	 * @return array<string, mixed>
	 */
	public function register_extension( $extensions ) {
		$extensions[ $this->get( 'slug' ) ] = true;

		return $extensions;
	}

	/**
	 * Load text domain.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			$this->get( 'text_domain' ),
			false,
			dirname( $this->get( 'basename' ) ) . '/languages'
		);
	}
}
