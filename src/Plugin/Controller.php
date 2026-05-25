<?php
/**
 * Extension controller base.
 *
 * @package PopupMaker\ExtensionFramework
 */

namespace PopupMaker\ExtensionFramework\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Controller base for standalone extensions.
 *
 * @extends \PopupMaker\Base\Controller<Core>
 */
abstract class Controller extends \PopupMaker\Base\Controller {

	/**
	 * Plugin container.
	 *
	 * @var Core
	 */
	public $container;
}
