<?php
/**
 * WHPDF Invoice Class
 *
 * @author  WPHobby
 * @package WooCommerce PDF Invoice Maker
 * @version 1.0.0
 */
if( ! class_exists( 'WHPDF_Invoice' ) ) {
    class WHPDF_Invoice extends WHPDF_Document {

        /**
         * document type
         *
         * @var string
         */
        public $document_type = 'invoice';


        /**
         * Constructor
         * @since  1.0.0
         * @access public
         * @return void
         */
        public function __construct() {
            add_action( 'init', array( $this, 'init_plugin_actions' ) );

            /**
             * Add invoice metabox on order
             */
            add_action( 'add_meta_boxes', array( $this, 'add_invoice_metabox' ) );
        }

        /**
         * add the right action based on GET var current used
         *
         */
        public function init_plugin_actions() {
            if ( isset( $_GET[ 'order_ids' ] ) ) {
                $this->create_document( $_GET[ 'order_ids' ], 'invoice' );
            }

            if ( isset( $_GET[ 'create_shipping_list' ] ) ) {
                $this->create_document( $_GET[ 'create_shipping_list' ], 'shipping_list' );
            }
        }

        /**
         *  Add invoice metabox on order page
         *
         * @since  1.0.0
         * @access public
         * @return void
         */
        public function add_invoice_metabox() {

            add_meta_box( 'wphobby-pdf-invoice-box', __( 'WPHobby PDF Invoice', 'wphobby-woo-pdf-invoice' ), array(
                $this,
                'pdf_invoice_metabox_html',
            ), 'shop_order', 'side', 'high' );
        }

        /**
         * Show invoice metabox html content
         *
         * @param WP_Post $post the order object that is currently shown
         *
         * @since  1.0.0
         * @access public
         * @return void
         */
        function pdf_invoice_metabox_html( $post ) {
            $order   = wc_get_order( $post->ID );
            $options = get_option( 'whpdf_advanced_data' );

            $invoice_data = array(
                'url'		=> wp_nonce_url( add_query_arg( 'order_ids', $post->ID ) ),
                'alt'		=> esc_attr( "PDF"),
                'title'		=> "PDF",
            );
            $shipping_data = array(
                'url'		=> wp_nonce_url( add_query_arg( 'create_shipping_list', $post->ID ) ),
                'alt'		=> esc_attr( "Shipping List"),
                'title'		=> "Shipping List",
            );

            ?>
            <div class="whpdf-invoice-wrapper">
                <ul class="whpdf-invoice-actions">
                    <?php
                        printf('<li><a href="%1$s" class="button" target="_blank" alt="%2$s">%3$s</a></li>', $invoice_data['url'], $invoice_data['alt'], $invoice_data['title']);
                        if ( isset($options['whpdf_field_packing_slip']) && $options['whpdf_field_packing_slip']  ) {
                            printf('<li><a href="%1$s" class="button" target="_blank" alt="%2$s">%3$s</a></li>', $shipping_data['url'], $shipping_data['alt'], $shipping_data['title']);

                        } 
                    ?>
                </ul>
            </div>
            <?php
        }

        /**
         * Create a new document of the type requested, for a specific order
         *
         * @param        $order_id      the order id for which the document is created
         * @param string $document_type the document type to be generated
         */
        public function create_document( $order_id, $document_type = '' ) {

            $document = new WHPDF_Document( $order_id , $document_type);
            
            $this->save_document( $document );
            
        }

        /*
			 * Save a PDF file starting from a previously created document
			 */
        public function save_document( $document ) {
            global $whpdf_document;
            $whpdf_document = $document;
            $whpdf_document->save();
        }


    }

    new WHPDF_Invoice;
}