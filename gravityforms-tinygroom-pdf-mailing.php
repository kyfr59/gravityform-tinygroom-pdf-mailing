<?php

/*
 * @wordpress-plugin
 * Plugin Name:       Gravity Forms Tinygroom PDF mailing
 * Description:       Generate PDF exports of Tinygroom forms for papers mailing
 * Version:           1.0.0
 * Author:            Franck Dupont
 * Author URI:        http://franck-dupont.me
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       gravityforms-tinygroom-pdf-mailing
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

if (!class_exists('GravityForms_Tinygroom_PDF_Mailing_Plugin')) {

	class GravityForms_Tinygroom_PDF_Mailing_Plugin {

		function __construct() {
			$this->run();
		}

		function install() { }

		function uninstall() { }

		function run() {
			require plugin_dir_path(__FILE__) . 'includes/enable-mailing-pdf-link.php';
			require plugin_dir_path(__FILE__) . 'includes/manage-mailing-pdf-page.php';
		}
	}

}

new GravityForms_Tinygroom_PDF_Mailing_Plugin();

