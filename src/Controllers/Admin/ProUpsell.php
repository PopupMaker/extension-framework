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
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_dismiss_script' ] );
		add_filter( 'plugin_row_meta', [ $this, 'plugin_row_meta' ], 10, 2 );
		add_filter( 'pum_alert_list', [ $this, 'register_panel_notification' ] );
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
	 * Alert code for the admin notice banner.
	 *
	 * @return string
	 */
	protected function get_admin_notice_code() {
		return 'pm_extension_pro_upsell_' . sanitize_key( $this->container->get( 'slug' ) ) . '_admin';
	}

	/**
	 * Alert code for the notifications panel.
	 *
	 * @return string
	 */
	protected function get_panel_alert_code() {
		return 'pm_extension_pro_upsell_' . sanitize_key( $this->container->get( 'slug' ) ) . '_panel';
	}

	/**
	 * Whether an alert code has been dismissed.
	 *
	 * @param string $code Alert code.
	 * @return bool
	 */
	protected function is_alert_dismissed( $code ) {
		return class_exists( 'PUM_Utils_Alerts' ) && \PUM_Utils_Alerts::has_dismissed_alert( $code );
	}

	/**
	 * Whether upsell surfaces can render for the current user.
	 *
	 * @return bool
	 */
	protected function can_show() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		if ( ! function_exists( '\PopupMaker\generate_upgrade_url' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Whether the admin notice should render.
	 *
	 * @return bool
	 */
	protected function should_show_admin_notice() {
		return $this->can_show() && ! $this->is_alert_dismissed( $this->get_admin_notice_code() );
	}

	/**
	 * Whether the panel notification should render.
	 *
	 * @return bool
	 */
	protected function should_show_panel_notification() {
		return $this->can_show() && ! $this->is_alert_dismissed( $this->get_panel_alert_code() );
	}

	/**
	 * Upgrade URL for upsell CTAs.
	 *
	 * @param string $utm_content UTM content slug.
	 * @return string
	 */
	protected function get_upgrade_url( $utm_content ) {
		$upsell = $this->get_upsell_config();

		return \PopupMaker\generate_upgrade_url(
			$upsell['utm_medium'],
			'migrate-to-pro',
			$utm_content
		);
	}

	/**
	 * Admin notice on Popup Maker screens.
	 *
	 * @return void
	 */
	public function admin_notice() {
		if ( ! $this->should_show_admin_notice() || ! function_exists( 'pum_is_admin_page' ) || ! pum_is_admin_page() ) {
			return;
		}

		$upsell = $this->get_upsell_config();
		$url    = $this->get_upgrade_url( 'admin-notice' );

		printf(
			'<div class="notice notice-info is-dismissible" data-pum-upsell="%1$s" data-alert-code="%2$s"><p>%3$s</p></div>',
			esc_attr( $this->container->get( 'slug' ) ),
			esc_attr( $this->get_admin_notice_code() ),
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
	 * Persist admin notice dismissal via core alert storage.
	 *
	 * @return void
	 */
	public function enqueue_dismiss_script() {
		if ( ! $this->should_show_admin_notice() || ! function_exists( 'pum_is_admin_page' ) || ! pum_is_admin_page() ) {
			return;
		}

		wp_enqueue_script( 'jquery' );

		$code  = esc_js( $this->get_admin_notice_code() );
		$nonce = esc_js( wp_create_nonce( 'pum_alerts_action' ) );

		wp_add_inline_script(
			'jquery',
			"jQuery( function ( $ ) {
				$( document ).on( 'click', '.notice[data-pum-upsell] .notice-dismiss', function () {
					$.post( ajaxurl, {
						action: 'pum_alerts_action',
						nonce: '{$nonce}',
						code: '{$code}',
						pum_dismiss_alert: 'dismiss'
					} );
				} );
			} );",
			'after'
		);
	}

	/**
	 * Register a Pro upsell in the core notifications panel.
	 *
	 * @param array<int, array<string, mixed>> $alerts Registered alerts.
	 * @return array<int, array<string, mixed>>
	 */
	public function register_panel_notification( $alerts ) {
		if ( ! is_array( $alerts ) || ! $this->should_show_panel_notification() ) {
			return $alerts;
		}

		$upsell      = $this->get_upsell_config();
		$text_domain = $this->container->get( 'text_domain' );

		$alerts[] = [
			'code'        => $this->get_panel_alert_code(),
			'category'    => 'offer',
			'priority'    => 65,
			'dismissible' => true,
			'type'        => 'info',
			'title'       => __( 'More features. Less money.', $text_domain ),
			'message'     => $this->get_panel_message( $upsell ),
			'subtitle'    => __( 'under $100/yr', $text_domain ),
			'icon'        => 'awards',
			'actions'     => [
				[
					'text'     => __( 'See what\'s in Pro', $text_domain ),
					'type'     => 'link',
					'action'   => '',
					'href'     => $this->get_upgrade_url( 'notifications-panel' ),
					'primary'  => true,
					'external' => true,
				],
				[
					'text'    => __( 'Not now', $text_domain ),
					'type'    => 'action',
					'action'  => 'dismiss',
					'expires' => '30 days',
				],
			],
		];

		return $alerts;
	}

	/**
	 * Richer Pro value message for the notifications panel.
	 *
	 * @param array<string, string> $upsell Upsell config.
	 * @return string
	 */
	protected function get_panel_message( $upsell ) {
		return sprintf(
			/* translators: %s: extension feature name */
			__(
				'%s + Scheduling, Analytics, Advanced Targeting, Theme Builder, and 10+ more pro features — bundled in <strong>Popup Maker Pro</strong> for less than buying extensions à la carte.',
				$this->container->get( 'text_domain' )
			),
			esc_html( $upsell['feature_name'] )
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

		$links[] = sprintf(
			'<a href="%s" target="_blank" rel="noopener noreferrer"><strong>%s</strong></a>',
			esc_url( $this->get_upgrade_url( 'plugins-list' ) ),
			esc_html__( 'Upgrade to Pro', $this->container->get( 'text_domain' ) )
		);

		return $links;
	}
}
