<?php
/**
 * Extension license service.
 *
 * @package PopupMaker\ExtensionFramework
 */

namespace PopupMaker\ExtensionFramework\Services;

use PopupMaker\Base\Service;
use PopupMaker\ExtensionFramework\Plugin\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Registers extension EDD licensing with Popup Maker core.
 *
 * @extends Service<Core>
 */
class License extends Service {

	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init', [ $this, 'register_extension_license' ], 5 );
	}

	/**
	 * Register with core extension licensing.
	 *
	 * @return void
	 */
	public function register_extension_license() {
		if ( ! class_exists( 'PUM_Extension_License' ) ) {
			return;
		}

		$edd_id = (int) $this->container->get( 'edd_id' );

		if ( $edd_id <= 0 ) {
			return;
		}

		new \PUM_Extension_License(
			$this->container->get( 'file' ),
			$this->container->get( 'name' ),
			$this->container->get( 'version' ),
			'Popup Maker',
			null,
			null,
			$edd_id
		);
	}
}
