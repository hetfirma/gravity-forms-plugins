<?php
/*
Plugin Name: GF NL Address Autocomplete
Plugin URI:  https://webventiv.nl
Description: Voeg NL-adres-autocomplete toe aan het standaard Gravity Forms Adres-veld via PDOK Locatieserver. Geef je Address-veld de CSS-klasse: nl-address-autocomplete
Version: 1.0.0
Author: Webventiv
Author URI: https://webventiv.com
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wbv-gfnla
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WBV_GF_NL_Address_Autocomplete' ) ) :

final class WBV_GF_NL_Address_Autocomplete {

    /** @var string */
    private $version = '1.0.0';

    /** @var self|null */
    private static $instance = null;

    /** @return self */
    public static function instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Alleen init hooks; laten draaien ongeacht GF, de callbacks worden alleen door GF aangeroepen.
        add_action( 'gform_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10, 2 );
        add_action( 'gform_register_init_scripts', array( $this, 'register_init_scripts' ), 10, 1 );

        // Optioneel: waarschuwing tonen als GF ontbreekt.
        add_action( 'admin_notices', array( $this, 'maybe_admin_notice_gravityforms_missing' ) );
    }

    /** @return void */
    public function enqueue_scripts( $form, $is_ajax ) {
        // Laad ons script altijd wanneer GF assets voor dit form enqueued,
        // het init-script bepaalt uiteindelijk of er iets gebeurt.
        $handle = 'wbv-nl-addr';
        $src    = plugin_dir_url( __FILE__ ) . 'assets/wbv-nl-addr.js';
        wp_register_script( $handle, $src, array(), $this->version, true );
        wp_enqueue_script( $handle );
    }

    /** @return void */
    public function register_init_scripts( $form ) {
        if ( empty( $form['id'] ) || empty( $form['fields'] ) || ! is_array( $form['fields'] ) ) {
            return;
        }

        foreach ( $form['fields'] as $field ) {
            if ( ! is_object( $field ) ) {
                continue;
            }

            // We targetten alléén standaard Address-velden met onze marker CSS class.
            if ( $field->type === 'address' && $this->field_has_css_class( $field, 'nl-address-autocomplete' ) ) {
                $form_id  = absint( $form['id'] );
                $field_id = absint( $field->id );

                // Optionele settings die je in de toekomst kunt uitbreiden.
                $settings = array(
                    'minChars' => 3,
                    'country'  => 'Nederland', // default land invullen (optioneel)
                );

                $script = sprintf(
                    'window.wbvInitNlAddress && window.wbvInitNlAddress(%d, %d, %s);',
                    $form_id,
                    $field_id,
                    wp_json_encode( $settings )
                );

                if ( class_exists( 'GFFormDisplay' ) ) {
                    GFFormDisplay::add_init_script( $form_id, "wbv_nl_addr_{$field_id}", GFFormDisplay::ON_PAGE_RENDER, $script );
                }
            }
        }
    }

    /** @return bool */
    private function field_has_css_class( $field, $class ) {
        $css = isset( $field->cssClass ) ? (string) $field->cssClass : '';
        if ( $css === '' ) {
            return false;
        }
        // simpele contains-check, GF bewaart spatiescheidende class-namen in cssClass
        return ( strpos( ' ' . $css . ' ', ' ' . $class . ' ' ) !== false );
    }

    /** @return void */
    public function maybe_admin_notice_gravityforms_missing() {
        if ( class_exists( 'GFForms' ) ) {
            return;
        }
        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }
        echo '<div class="notice notice-warning"><p>';
        esc_html_e( 'GF NL Address Autocomplete vereist Gravity Forms. Activeer Gravity Forms om deze plugin te gebruiken.', 'wbv-gfnla' );
        echo '</p></div>';
    }
}

// Bootstrap plugin.
WBV_GF_NL_Address_Autocomplete::instance();

endif;
