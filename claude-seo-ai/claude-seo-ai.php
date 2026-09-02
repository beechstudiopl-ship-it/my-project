<?php
/**
 * Plugin Name:       Claude SEO — AI Visibility
 * Plugin URI:        https://northtc.pl
 * Description:        Automatyczne SEO pod AI (AEO): wpuszcza boty AI w robots.txt oraz wstrzykuje dane strukturalne (firma, FAQ, kursy) do <head>. Konfiguracja w jednym miejscu.
 * Version:           1.1.0
 * Requires at least: 5.6
 * Requires PHP:      7.2
 * Author:            NorthTC
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       claude-seo-ai
 *
 * @package ClaudeSeoAi
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Nie pozwalamy na bezposredni dostep.
}

define( 'CSA_VERSION', '1.1.0' );
define( 'CSA_PLUGIN_FILE', __FILE__ );
define( 'CSA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CSA_OPTION_KEY', 'csa_settings' );

require_once CSA_PLUGIN_DIR . 'includes/class-csa-settings.php';
require_once CSA_PLUGIN_DIR . 'includes/class-csa-robots.php';
require_once CSA_PLUGIN_DIR . 'includes/class-csa-schema.php';
require_once CSA_PLUGIN_DIR . 'includes/class-csa-faq.php';
require_once CSA_PLUGIN_DIR . 'includes/class-csa-course.php';
require_once CSA_PLUGIN_DIR . 'includes/class-csa-breadcrumbs.php';

/**
 * Zwraca zapisane ustawienia wtyczki, uzupelnione o wartosci domyslne.
 *
 * @return array
 */
function csa_get_settings() {
	$defaults = array(
		'enable_ai_bots'     => 1,
		'enable_org'         => 1,
		'enable_faq'         => 1,
		'enable_breadcrumbs' => 1,
		'org_type'           => 'EducationalOrganization',
		'name'               => get_bloginfo( 'name' ),
		'alternate_name'     => '',
		'logo'               => '',
		'description'        => get_bloginfo( 'description' ),
		'email'              => '',
		'telephone'          => '',
		'street'             => '',
		'postal_code'        => '',
		'city'               => '',
		'region'             => '',
		'country'            => 'PL',
		'area_served'        => '',
		'same_as'            => '',
		'faq_scope'          => 'front', // front | all
	);

	$saved = get_option( CSA_OPTION_KEY, array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}

	return wp_parse_args( $saved, $defaults );
}

/**
 * Inicjalizacja modulow wtyczki.
 */
function csa_init() {
	new CSA_Settings();
	new CSA_Robots();
	new CSA_Schema();
	new CSA_Faq();
	new CSA_Course();
	new CSA_Breadcrumbs();
}
add_action( 'plugins_loaded', 'csa_init' );

/**
 * Przy aktywacji: rejestrujemy CPT i odswiezamy przepisywanie adresow.
 */
function csa_activate() {
	CSA_Faq::register_post_type();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'csa_activate' );

/**
 * Przy deaktywacji: czyscimy przepisywanie adresow.
 */
function csa_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'csa_deactivate' );
