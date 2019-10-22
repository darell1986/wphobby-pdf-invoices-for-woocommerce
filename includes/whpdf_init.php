<?php
/**
 * WHPDF class
 *
 * @author  WPHobby
 * @package WooCommerce PDF Invoice Maker
 * @version 1.0.0
 */
class WHPDF {

    public $options;

    /**
     * @var bool Check WooCommerce Version
     * @since 1.0.0
     */
    public $current_wc_version  = false;
    public $is_wc_older_2_1     = false;
    public $is_wc_older_2_6     = false;

    public function __construct() {
        $this->options = get_option(WHPDF_OPTIONS);

        /**
         * WooCommerce Version Check
         */
        $this->current_wc_version = WC()->version;
        $this->is_wc_older_2_1    = version_compare( $this->current_wc_version, '2.1', '<' );
        $this->is_wc_older_2_6    = version_compare( $this->current_wc_version, '2.6', '<' );

        /* Enqueue Style and Scripts */
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ) );

    }

    public function activate(){
        //plugin default opts
        $init_opts = array(
            'version' => WHPDF_VERSION
        );

        if(!empty($this->options)){
            // update existed options
            update_option(WHPDF_OPTIONS, $init_opts);
        }else{
            // add the init options
            add_option(WHPDF_OPTIONS, $init_opts);
        }
    }

    public function initialize(){
    }

    public function deactivate(){
    }

    /**
     * Enqueue Styles and Scripts
     */
    public function enqueue_styles_scripts() {
        wp_enqueue_style('font-awesome', WHPDF_URL . 'assets/css/font-awesome.min.css', false, WHPDF_VERSION );
        wp_enqueue_style('flaticon', WHPDF_URL . 'assets/css/flaticon.css', false, WHPDF_VERSION );
        wp_enqueue_style( 'whpdf-frontend-style', WHPDF_URL . 'assets/css/frontend.css', false, WHPDF_VERSION );
        wp_enqueue_script( 'whpdf-frontend-script', WHPDF_URL . 'assets/js/frontend.js', array( 'jquery' ), WHPDF_VERSION, true );

        $advanced_options = get_option( 'whpdf_advanced_data' );

        $whpdf_js_options = array(
            'enable_message'   => $advanced_options['whpdf_field_cart_message'],
            'message_text'     => $advanced_options['whpdf_field_prompt_message'],
        );
        wp_localize_script( 'whpdf-frontend-script', 'options', $whpdf_js_options );
    }
}
?>