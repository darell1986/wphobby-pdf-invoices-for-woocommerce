<?php
/**
 * WHPDF Document Class
 *
 * @author  WPHobby
 * @package WooCommerce PDF Invoice Maker
 * @version 1.0.0
 */
if( ! class_exists( 'WHPDF_Document' ) ) {
    class WHPDF_Document {

        /**
         * @var string Document type
         */
        public $document_type = '';

        /**
         * @var WC_Order
         */
        public $order;

        public $save_path;

        public $exists = false;

        /**
         * Constructor
         * @since  1.0.0
         * @access public
         * @return void
         */
        public function __construct($order_id, $document_type) {
            $this->initialize();

            $this->document_type = $document_type;
            /**
             * Get WooCommerce order for this order id
             */
            $this->order = wc_get_order( $order_id );
        }

        public function initialize() {
            $date = getdate( time() );
            $year = $date['year'];

            if ( ! file_exists( WHPDF_DOCUMENT_SAVE_DIR ) ) {
                wp_mkdir_p( WHPDF_DOCUMENT_SAVE_DIR );
            }

            if ( ! file_exists( WHPDF_DOCUMENT_SAVE_DIR . $year ) ) {
                wp_mkdir_p( WHPDF_DOCUMENT_SAVE_DIR . $year );
            }
        }

        public function save_file( $file_path ) {
            $pdf_content = $this->generate_template();
            file_put_contents( $file_path, $pdf_content );
        }

        /**
         * Generate the template
         */
        private function generate_template() {

            $this->init_template();
            do_action( 'whpdf_before_template_generation' );

            ob_start();
            wc_get_template( 'template.php', null, WHPDF_INVOICE_TEMPLATE_DIR, WHPDF_INVOICE_TEMPLATE_DIR );

            $html = ob_get_contents();

            var_dump($html);

            ob_end_clean();

            require_once( WHPDF_DIR . "lib/dompdf/autoload.inc.php" );
            require_once( WHPDF_DIR . "lib/dompdf/src/Options.php" );


            $options = new Dompdf\Options();
            $options->set('isRemoteEnabled', true);

            $dompdf = new Dompdf\Dompdf($options);

            $dompdf->load_html( $html );

            $dompdf->render();

            // The next call will store the entire PDF as a string in $pdf
            $pdf = $dompdf->output();
            $this->flush_template();

            return $pdf;
        }

        public function init_template() {
            add_action( 'whpdf_invoice_template_head', array( $this, 'add_invoice_style' ) );
            add_action( 'whpdf_invoice_template_content', array( $this, 'add_invoice_content' ) );
        }

        public function flush_template() {

        }

        public function add_invoice_style() {
            $document_url = WHPDF_INVOICE_TEMPLATE_DIR . $this->document_type . '.css';

            if ( file_exists( $document_url ) ) {
                echo '<link rel="stylesheet" type="text/css" href="' . $document_url . '">';
            }
        }

        /**
         *
         * save invoice content
         * @since  1.0.0
         * @access public
         * @return void
         */
        public function add_invoice_content() {

            $template_filename = $this->document_type . '.php';
            $template_path     = WHPDF_INVOICE_TEMPLATE_DIR . $template_filename;

            if ( file_exists( $template_path ) ) {
                wc_get_template( $template_filename, null, WHPDF_INVOICE_TEMPLATE_DIR, WHPDF_INVOICE_TEMPLATE_DIR );
            }
        }

        /**
         * Set invoice data for current order, picking the invoice number from the related general option
         */
        public function save() {
            //  Avoid generating a new invoice from a previous one
            if ( $this->exists ) {
                return;
            }

            $this->date = time ();
            $date       = getdate ( $this->date );
            $year       = $date['year'];

            $invoice_number = apply_filters ( 'whpdf_new_invoice_number', null, $this->order );

            $this->number = $invoice_number ? $invoice_number : $this->get_new_invoice_number();

            $this->prefix    = 'whpdf_invoice_prefix';
            $this->suffix    = 'whpdf_invoice_suffix';
            $this->save_path = $year . "/invoice_" . $this->number . ".pdf";
            $this->exists    = true;

            $pdf_path = WHPDF_DOCUMENT_SAVE_DIR . $this->save_path;
            add_action ( 'whpdf_before_template_generation', array( $this, 'init_template_generation_actions' ) );
            $this->save_file ( $pdf_path );

            // update invoice data in db.
            $order_id = $this->order->get_id();
            update_post_meta( $order_id, '_whpdf_invoice_pdf_path', $pdf_path );


            $general_options = get_option( 'whpdf_general_data' );
            $this->view_document($general_options['whpdf_field_view_pdf']);

            //  Auto increment the invoice number for next invoice
            update_option ( 'whpdf_invoice_number', $this->number + 1 );

        }

        /**
         * Get Formatted Invoice Number
         */
        public function get_formatted_invoice_number() {
            $formatted_invoice_number = get_option ( 'whpdf_invoice_number_format' );

            $formatted_invoice_number = str_replace (
                array( '[prefix]', '[suffix]', '[number]' ),
                array( $this->prefix, $this->suffix, $this->number ),
                $formatted_invoice_number );


            return apply_filters ( 'whpdf_invoice_get_formatted_invoice_number', $formatted_invoice_number, $this->order );
        }

        /**
         * Get Formatted Date
         */
        public function get_formatted_date() {

            if ( $this->order ) {
                $format = apply_filters('whpdf_invoice_date_format',get_option ( 'whpdf_invoice_date_format' ));
                $order_id = whpdf_get_prop( $this->order,'id' );
                $date   = get_post_meta( $order_id, '_completed_date', true ) ? date($format,strtotime(get_post_meta( $order_id, '_completed_date', true ))) : date ( $format, $this->order->get_date_created()->getTimestamp() );
            }

            return $date;

        }

        /**
         * Reset actions and add new ones related to current document being generated
         */
        public function init_template_generation_actions() {
            add_action ( 'whpdf_invoice_template_customer_data', array(
                $this,
                'show_invoice_template_customer_data',
            ) );
            add_action ( 'whpdf_invoice_template_invoice_data', array(
                $this,
                'show_invoice_template_invoice_data',
            ) );
            add_action ( 'whpdf_invoice_template_products_list', array(
                $this,
                'show_invoice_template_products_list',
            ) );
            add_action ( 'whpdf_invoice_template_footer', array(
                $this, 'show_invoice_template_footer',
            ) );
            add_action ( 'whpdf_invoice_template_sender', array(
                $this, 'show_invoice_template_sender',
            ) );
            add_action ( 'whpdf_invoice_template_company_logo', array(
                $this, 'show_invoice_template_company_logo',
            ) );
        }

        /**
         * Show data of customer on invoice template
         */
        public function show_invoice_template_customer_data() {
            global $whpdf_document;

            $display_text = '';
            if($this->document_type == 'invoice'){
                $display_text = 'Invoice To:';
            }else if($this->document_type == 'shipping_list'){
                $display_text = 'Shipping To:';
            }

            echo '<div class="invoice-to-section" > ';

            $order = $whpdf_document->order;
            /** WC_Order $order*/

            if ( $order->get_formatted_billing_address () ) {
                echo '<span class="invoice-from-to" > ' . $display_text . '</span > ' . wp_kses ( $order->get_formatted_billing_address (), array( "br" => array() ) );
            }

            echo '</div> ';


        }

        /**
         * Show data of customer on invoice template
         */
        public function show_invoice_template_invoice_data() {
            global $whpdf_document;


            if ( ! isset( $whpdf_document ) || ! $whpdf_document->exists ) {
                return;
            }
            ?>
            <table>
                <tr class="invoice-number">
                    <td><?php esc_html_e( "Invoice", 'whpdfh-woocommerce-pdf-invoice' ); ?></td>
                    <td class="right"><?php echo $whpdf_document->get_formatted_invoice_number (); ?></td>
                </tr>

                <tr class="invoice-order-number">
                    <td><?php esc_html_e( "Order", 'whpdfh-woocommerce-pdf-invoice' ); ?></td>
                    <td class="right"><?php echo $whpdf_document->order->get_order_number (); ?></td>
                </tr>

                <tr class="invoice-date">
                    <td><?php esc_html_e( "Invoice date", 'whpdfh-woocommerce-pdf-invoice' ); ?></td>
                    <td class="right"><?php echo $whpdf_document->get_formatted_date (); ?></td>
                </tr>
                <tr class="invoice-amount">
                    <td><?php esc_html_e( "Order Amount", 'whpdfh-woocommerce-pdf-invoice' ); ?></td>
                    <td class="right"><?php echo wc_price ( $whpdf_document->order->get_total () ); ?></td>
                </tr>
            </table>
            <?php
        }

        /**
         * Show product list for current order on invoice template
         */
        public function show_invoice_template_products_list() {
            include ( WHPDF_INVOICE_TEMPLATE_DIR . 'invoice-details.php' );
        }

        /**
         * Show footer information on invoice template
         */
        public function show_invoice_template_footer() {
            include ( WHPDF_INVOICE_TEMPLATE_DIR . 'invoice-footer.php' );
        }

        /**
         * Render and show data to "sender section" on invoice template
         */
        public function show_invoice_template_sender() {
            $general_options = get_option( 'whpdf_general_data' );

            $company_name    = $general_options['whpdf_field_shop_name'];
            $company_address = $general_options['whpdf_field_shop_address'];

            if ( ! isset( $company_name ) && ! isset( $show_logo ) ) {
                return;
            }

            echo '<span class="invoice-from-to">' . __ ( "Invoice From:", 'wphobby-woo-pdf-invoice' ) . ' </span>';
            if ( isset( $company_name ) ) {
                echo '<span class="company-name">' . $company_name . '</span>';
            }
            if ( isset ( $company_address ) ) {
                echo '<span class="company-details" > ' . $company_address . '</span > ';
            }
        }

        /**
         * Show company logo on invoice template
         */
        public function show_invoice_template_company_logo() {
            $general_options = get_option( 'whpdf_general_data' );
            $image_attributes = wp_get_attachment_image_src( $general_options['whpdf_field_shop_logo'] );

            $company_logo = $image_attributes[0];

            if ( ! isset( $company_logo ) ) {
                return;
            }

            if ( isset( $company_logo ) ) {
                echo '<div class="company-logo">
					<img src="' . $company_logo . '"/>
				</div>';
            }
        }

        /*
        * Generate the PDF when you click on view invoice
        */
        public function view_document( $view_type ) {
            $full_path = WHPDF_DOCUMENT_SAVE_DIR . $this->save_path;
            //  Check if show pdf invoice on browser or asking to download it
            if ( 'browser' == $view_type ) {
                header( 'Content-type: application/pdf' );
                header( 'Content-Disposition: inline; filename = "' . basename( $full_path ) . '"' );
                header( 'Content-Transfer-Encoding: binary' );
                header( 'Content-Length: ' . filesize( $full_path ) );
                header( 'Accept-Ranges: bytes' );
                @readfile( $full_path );
                exit();
            } else {
                header( "Content-type: application/pdf" );
                header( 'Content-Disposition: attachment; filename = "' . basename( $full_path ) . '"' );
                @readfile( $full_path );
            }
        }

        /*
		 * Return the next available invoice number
		 */
        private function get_new_invoice_number() {

            $current_invoice_number = get_option ( 'whpdf_invoice_number' );
            if ( ! isset( $current_invoice_number ) || ! is_numeric ( $current_invoice_number ) ) {
                $current_invoice_number = 1;
            }

            return $current_invoice_number;
        }

        /**
         * Full path to pdf invoice.
         *
         * @return string full path to pdf invoice.
         */
        public function get_full_path() {
            $order_id = $this->order->get_id();
            // pdf data exists in database?
            $pdf_path = get_post_meta( $order_id, '_whpdf_invoice_pdf_path', true );
            if ( ! $pdf_path ) {
                return false;
            }else{
                return $pdf_path;
            }
        }

    }

}