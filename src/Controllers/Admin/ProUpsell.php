<?php
/**
 * Pro migration upsell controller.
 *
 * @package PopupMaker\ExtensionFramework
 */

namespace PopupMaker\ExtensionFramework\Controllers\Admin;

use PopupMaker\ExtensionFramework\Plugin\Controller;

defined( 'ABSPATH' ) || exit;

/**
 * Encourage migration to Popup Maker Pro when Pro is not active.
 */
class ProUpsell extends Controller {

	/**
	 * Init.
	 *
	 * @return void
	 */
	public function init() {
		if ( defined( 'POPUP_MAKER_DISABLE_UPSELLS' ) && POPUP_MAKER_DISABLE_UPSELLS ) {
			return;
		}

		if ( $this->is_pro_active() ) {
			return;
		}

		add_action( 'admin_notices', [ $this, 'admin_notice' ] );
		add_filter( 'plugin_row_meta', [ $this, 'plugin_row_meta' ], 10, 2 );
	}

	/**
	 * Whether Popup Maker Pro is active.
	 *
	 * @return bool
	 */
	protected function is_pro_active() {
		if ( function_exists( '\PopupMaker\plugin' ) ) {
			return \PopupMaker\plugin()->is_pro_active();
		}

		return class_exists( '\PopupMaker\Pro\Plugin\Core' );
	}

	/**
	 * Upsell config with defaults.
	 *
	 * @return array<string, string>
	 */
	protected function get_upsell_config() {
		$config = (array) $this->container->get( 'pro_upsell' );

		return wp_parse_args(
			$config,
			[
				'feature_name' => $this->container->get( 'name' ),
				'utm_medium'   => 'extension-' . $this->container->get( 'slug' ),
			]
		);
	}

	/**
	 * Dismiss transient key.
	 *
	 * @return string
	 */
	protected function get_dismiss_transient_key() {
		return $this->container->get( 'option_prefix' ) . '_pro_upsell_dismissed';
	}

	/**
	 * Admin notice on Popup Maker screens.
	 *
	 * @return void
	 */
	public function admin_notice() {
		if ( ! current_user_can( 'manage_options' ) || ! function_exists( 'pum_is_admin_page' ) || ! pum_is_admin_page() ) {
			return;
		}

		if ( get_transient( $this->get_dismiss_transient_key() ) ) {
			return;
		}

		if ( ! function_exists( '\PopupMaker\generate_upgrade_url' ) ) {
			return;
		}

		$upsell = $this->get_upsell_config();
		$url    = \PopupMaker\generate_upgrade_url(
			$upsell['utm_medium'],
			'migrate-to-pro',
			'admin-notice'
		);

		printf(
			'<div class="notice notice-info is-dismissible" data-pum-upsell="%s"><p>%s</p></div>',
			esc_attr( $this->container->get( 'slug' ) ),
			wp_kses_post(
				sprintf(
					/* translators: %1$s: feature name, %2$s: opening anchor, %3$s: closing anchor */
					__( '%1$s is included in %2$sPopup Maker Pro%3$s along with Scheduling, Analytics, Advanced Targeting, and more.', $this->container->get( 'text_domain' ) ),
					esc_html( $upsell['feature_name'] ),
					'<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">',
					'</a>'
				)
			)
		);
	}

	/**
	 * Plugin row meta link.
	 *
	 * @param array<int, string> $links       Links.
	 * @param string             $plugin_file Plugin file.
	 * @return array<int, string>
	 */
	public function plugin_row_meta( $links, $plugin_file ) {
		if ( $this->container->get( 'basename' ) !== $plugin_file || ! function_exists( '\PopupMaker\generate_upgrade_url' ) ) {
			return $links;
		}

		$upsell = $this->get_upsell_config();

		$links[] = sprintf(
			'<a href="%s" target="_blank" rel="noopener noreferrer"><strong>%s</strong></a>',
			esc_url(
				\PopupMaker\generate_upgrade_url(
					$upsell['utm_medium'],
					'migrate-to-pro',
					'plugins-list'
				)
			),
			esc_html__( 'Upgrade to Pro', $this->container->get( 'text_domain' ) )
		);

		return $links;
	}
}
