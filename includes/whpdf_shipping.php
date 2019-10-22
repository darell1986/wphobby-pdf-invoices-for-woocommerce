<?php
if ( ! defined ( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists ( 'WHPDF_Shipping' ) ) {
	
	/**
	 * Implements features related to a PDF document
	 *
	 * @class   WHPDF_Shipping
	 * @package Yithemes
	 * @since   1.0.0
	 * @author  Your Inspiration Themes
	 */
	class WHPDF_Shipping extends WHPDF_Document {
		
		public $document_type = 'shipping_list';
		
		public $save_path;
		
		/**
		 * Constructor
		 *
		 * Initialize plugin and registers actions and filters to be used
		 *
		 * @since  1.0
		 * @author Lorenzo Giuffrida
		 * @access public
		 * @return void
		 */
		public function __construct( $order_id, $document_type ) {
			
			/**
			 * Call base class constructor
			 */
			parent::__construct ( $order_id, $document_type );
			
			/**
			 * if this document is not related to a valid WooCommerce order, exit
			 */
			if ( ! $this->is_valid ) {
				return;
			}
			
			/**
			 *  Fill invoice information from a previous invoice is exists or from general plugin options plus order related data
			 * */
			$this->init_document ();
		}
		
		/*
		 * Check if a document exist for current order and load related data
		 */
		private function init_document() {
			$this->exists = whpdf_get_prop ( $this->order, '_whpdf_has_shipping_list', true );

			if ( $this->exists ) {
				$this->save_path = whpdf_get_prop ( $this->order, '_ywpi_shipping_list_path', true );
			}
		}

		/**
		 *  Cancel shipping list document for the current order
		 */
		public function reset() {
			yit_delete_prop ( $this->order, '_whpdf_has_shipping_list' );
			yit_delete_prop ( $this->order, '_whpdf_shipping_list_path' );
		}

		/**
		 * Set invoice data for current order, picking the invoice number from the related general option
		 */
		public function save() {
			//  Avoid generating a new document if a previous one still exists
			if ( $this->exists ) {
				return;
			}

			$this->save_path = $this->document_type . "_" . $this->order->get_order_number () . ".pdf";
			$this->exists    = true;

			whpdf_save_prop ( $this->order,
				array(
					'_whpdf_has_shipping_list'  => $this->exists,
					'_whpdf_shipping_list_path' => $this->save_path
				) );

			$pdf_path = WHPDF_INVOICE_TEMPLATE_DIR . $this->save_path;
			$this->init_template_generation_actions ();
			$this->save_file ( $pdf_path );
		}

		/**
		 * Reset actions and add new ones related to current document being generated
		 */
		public function init_template_generation_actions() {
			add_action ( 'whpdf_invoice_template_head', array( $this, 'add_invoice_style' ) );
			add_action ( 'whpdf_invoice_template_content', array( $this, 'add_invoice_content' ) );

		}
	}
}