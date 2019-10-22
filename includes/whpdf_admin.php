<?php
/**
 * WHPDF Admin class
 *
 * @author  WPHobby
 * @package WooCommerce PDF Invoice Maker
 * @version 1.0.0
 */
if( ! class_exists( 'WHPDF_Admin' ) ) {
    class WHPDF_Admin {
        // =============================================================================
        // Construct
        // =============================================================================
        public function __construct() {
            add_action( 'admin_init', array( $this, 'whpdf_register_settings' ) );
            add_action( 'admin_menu', array( $this, 'whpdf_register_menu' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'whpdf_admin_styles_scripts' ) );
            add_action( 'admin_init', array( $this, 'whpdf_admin_notice_ignore' ));

            // Attach invoice on woocommerce email
            add_filter( 'woocommerce_email_attachments', array( $this, 'whpdf_attach_to_email' ), 99, 3 );

        }

        /**
         * Load welcome admin css and js
         * @return void
         * @since  1.0.0
         */
        public function whpdf_admin_styles_scripts() {
            if ( is_admin() ) {
                wp_enqueue_style('font-awesome', WHPDF_URL . 'assets/css/font-awesome.min.css', false, WHPDF_VERSION );
                wp_enqueue_style( 'whpdf-admin-style', WHPDF_URL . 'assets/css/admin.css', false, WHPDF_VERSION );

                if( isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'custom-style' ){
                    wp_enqueue_script( 'whpdf-admin-script', WHPDF_URL . 'assets/js/admin.js', array( 'jquery' ), WHPDF_VERSION, true );

                    /* Add CodeMirror */
                    $cm_settings['codeEditor'] = wp_enqueue_code_editor(array('type' => 'text/css'));
                    wp_localize_script('jquery', 'cm_settings', $cm_settings);

                    wp_enqueue_script('wp-theme-plugin-editor');
                    wp_enqueue_style('wp-codemirror');
                }

                // upload image scripts
                wp_enqueue_media();
                wp_enqueue_script( 'whpdf-upload-image-script', WHPDF_URL . 'assets/js/upload_image.js', array( 'jquery' ), WHPDF_VERSION, true );


            }
        }

        /*
         * Display admin messages
         */
        public function whpdf_display_message(){
            if ( isset( $_GET['settings-updated'] ) ) {
                echo "<div class='updated alert success'><p>".__( 'Settings updated successfully.', 'wphobby-woo-pdf-invoice' )."</p></div>";
            }
        }

        /*
        * Ignore admin notice message
        */
        public function whpdf_admin_notice_ignore() {

            global $current_user;

            $user_id = $current_user->ID;

            if (isset($_GET['whpdf-ignore-notice'])) {

                add_user_meta($user_id, 'whpdf_admin_notice_ignore', 'true', true);
                $plugin_url = admin_url('??page=whpdf-panel');
                wp_redirect( $plugin_url );

            }

        }

        /**
         * Register admin menus
         * @return void
         * @since  1.0.0
         */
        public function whpdf_register_menu(){
            add_menu_page( 'WooCommerce PDF Invoice Maker', 'Invoices', 'manage_options', 'whpdf-panel', array( $this, 'whpdf_panel_general' ), WHPDF_URL . '/assets/images/icon.svg', '2');
            add_submenu_page('whpdf-panel', 'Help & Guide', 'Help & Guide', 'manage_options', 'whpdf-help', array( $this, 'whpdf_panel_help' ) );
        }

        /**
         * The admin panel content
         * @since 1.0.0
         */
        public function whpdf_panel_general() {
            $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general';
            ?>
            <div class="whpdf-panel">
                <div class="wrap">
                    <?php require_once( WHPDF_DIR . '/includes/admin/sections/general/top.php' ); ?>
                    <?php $this->whpdf_display_message(); ?>
                    <?php
                    if( $active_tab == 'general' ){
                        require_once( WHPDF_DIR . '/includes/admin/sections/general/tab-general.php' );
                    }
                    else if($active_tab == 'advanced'){
                        require_once( WHPDF_DIR . '/includes/admin/sections/general/tab-advanced.php' );
                    }else if($active_tab == 'server'){
                        require_once( WHPDF_DIR . '/includes/admin/sections/general/tab-server.php' );
                    }
                    ?>
                </div>
            </div>
            <?php
        }

        /**
         * The admin panel help
         * @since 1.0.0
         */
        public function whpdf_panel_help() {
            $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'help';
            ?>
            <div class="whpdf-panel">
                <div class="wrap">
                    <?php require_once( WHPDF_DIR . '/includes/admin/sections/help/top.php' ); ?>
                    <?php $this->whpdf_display_message(); ?>
                    <?php
                    if( $active_tab == 'help' ){
                        require_once( WHPDF_DIR . '/includes/admin/sections/help/tab-help.php' );
                    }else if($active_tab == 'change-log'){
                        require_once( WHPDF_DIR . '/includes/admin/sections/help/tab-change-log.php' );
                    }
                    ?>
                </div>
            </div>
            <?php
        }

        /**
         * Register Settings
         * @since 1.0.0
         */
        public function whpdf_register_settings() {
            register_setting(
                'whpdf_advanced', // A settings group name. Must exist prior to the register_setting call. This must match the group name in settings_fields()
                'whpdf_advanced_data' //The name of an option to sanitize and save.
            );

            register_setting(
                'whpdf_general', // A settings group name. Must exist prior to the register_setting call. This must match the group name in settings_fields()
                'whpdf_general_data'
            );

            register_setting(
                'whpdf_custom', // A settings group name. Must exist prior to the register_setting call. This must match the group name in settings_fields()
                'whpdf_custom_data'
            );

            add_settings_section( 'whpdf_section_general', '', array( $this, 'whpdf_section_general_output' ), 'whpdf_panel_general' );
            add_settings_field( 'whpdf_field_view_pdf', esc_html__("Vew PDF Behaviour", "wphobby-woo-pdf-invoice"), array( $this, 'whpdf_view_pdf_output' ), 'whpdf_panel_general', 'whpdf_section_general' );
            add_settings_field( 'whpdf_field_shop_name', esc_html__("Shop Name", "wphobby-woo-pdf-invoice"), array( $this, 'whpdf_shop_name_output' ), 'whpdf_panel_general', 'whpdf_section_general' );
            add_settings_field( 'whpdf_field_shop_address', esc_html__("Shop Address", "wphobby-woo-pdf-invoice"), array( $this, 'whpdf_shop_address_output' ), 'whpdf_panel_general', 'whpdf_section_general' );
            add_settings_field( 'whpdf_field_shop_logo', esc_html__("Shop Logo", "wphobby-woo-pdf-invoice"), array( $this, 'whpdf_shop_logo_output' ), 'whpdf_panel_general', 'whpdf_section_general' );

            add_settings_section( 'whpdf_section_advanced', '', array( $this, 'whpdf_section_advanced_output' ), 'whpdf_panel_advanced' );
            add_settings_field( 'whpdf_field_packing_slip', esc_html__("Enable Packing Slip", "wphobby-woo-pdf-invoice"), array( $this, 'whpdf_packing_list_output' ), 'whpdf_panel_advanced', 'whpdf_section_advanced' );
            add_settings_field( 'whpdf_field_currency_code', esc_html__("Display Currency Code", "wphobby-woo-pdf-invoice"), array( $this, 'whpdf_currency_code_output' ), 'whpdf_panel_advanced', 'whpdf_section_advanced' );
            add_settings_field( 'whpdf_field_attach_email', esc_html__("Attach to Emails", "wphobby-woo-pdf-invoice"), array( $this, 'whpdf_attach_email_output' ), 'whpdf_panel_advanced', 'whpdf_section_advanced' );

        }

        public function whpdf_section_general_output() {
            echo esc_html__( 'General display settings for PDF invoices.', 'wphobby-woo-pdf-invoice' );
        }

        public function whpdf_view_pdf_output() {
            $options = get_option( 'whpdf_general_data' );
            ?>
            <select name='whpdf_general_data[whpdf_field_view_pdf]'> 
                <option value='download' <?php if(isset($options['whpdf_field_view_pdf'])){selected( $options['whpdf_field_view_pdf'], 'download' );} ?>>Download PDF</option> 
                <option value='browser' <?php if(isset($options['whpdf_field_view_pdf'])){selected( $options['whpdf_field_view_pdf'], 'browser' );} ?>>Open PDF on browser new tab</option> 
            </select>
            <?php
        }

        public function whpdf_shop_name_output() {
            $options = get_option( 'whpdf_general_data' );
            ?>
            <input type="text" name="whpdf_general_data[whpdf_field_shop_name]" value='<?php echo isset($options['whpdf_field_shop_name']) ? esc_attr($options['whpdf_field_shop_name']) : get_bloginfo( 'name' ); ?>'/>
            <?php
        }

        public function whpdf_shop_address_output() {
            $options = get_option( 'whpdf_general_data' );
            ?>
            <textarea name="whpdf_general_data[whpdf_field_shop_address]"><?php echo esc_html($options['whpdf_field_shop_address']);?></textarea>
            <?php
        }

        public function whpdf_shop_logo_output() {
            $options = get_option( 'whpdf_general_data' );
            // Set variables
            $default_image = WHPDF_URL . 'assets/images/default-logo.png';

            if ( !empty( $options['whpdf_field_shop_logo'] ) ) {
                $image_attributes = wp_get_attachment_image_src( $options['whpdf_field_shop_logo'], array( $width, $height ) );
                $src = $image_attributes[0];
                $value = $options['whpdf_field_shop_logo'];
            } else {
                $src = $default_image;
                $value = '';
            }
            ?>
            <div class="upload">
                <img data-src="" name="" src="<?php echo esc_attr($src); ?>" />
                <div>
                    <input type="hidden" name="whpdf_general_data[whpdf_field_shop_logo]" id="" value="<?php echo esc_attr($value); ?>" />
                    <button type="submit" class="upload_image_button button"><?php echo __( 'Upload', 'wphobby-woo-pdf-invoice' ); ?></button>
                    <button type="submit" class="remove_image_button button">&times;</button>
                </div>
            </div>
            <?php
        }

        public function whpdf_section_advanced_output() {
            echo esc_html__( 'Advanced Settings for PDF Invoices.', 'wphobby-woo-pdf-invoice' );
        }

        public function whpdf_packing_list_output() {
            $options = get_option( 'whpdf_advanced_data' );
            $value = 1;
            $checked = isset($options['whpdf_field_packing_slip']) && $options['whpdf_field_packing_slip']== '1' ? 'checked' : '';
            ?>
            <label class="switch">
                <input type="checkbox" value='<?php echo esc_attr($value); ?>' name='whpdf_advanced_data[whpdf_field_packing_slip]' <?php echo esc_attr($checked); ?> />
                <span class="slider round"></span>
            </label>
            <?php
        }

        public function whpdf_currency_code_output() {
            $options = get_option( 'whpdf_advanced_data' );
            $value = 1;
            $checked = isset($options['whpdf_field_currency_code']) && $options['whpdf_field_currency_code']== '1' ? 'checked' : '';
            ?>
            <label class="switch">
                <input type="checkbox" value='<?php echo esc_attr($value); ?>' name='whpdf_advanced_data[whpdf_field_currency_code]' <?php echo esc_attr($checked); ?> />
                <span class="slider round"></span>
            </label>
            <?php
        }

        public function whpdf_attach_email_output() {
            $options = get_option( 'whpdf_advanced_data' );
            $value = 1;
            ?>
            <div class="whpdf_attach_email">
              <p><input type="checkbox"  value="<?php echo esc_attr($value); ?>" name="whpdf_advanced_data[whpdf_field_attach_email][new_order]" <?php echo isset($options['whpdf_field_attach_email'][new_order]) && $options['whpdf_field_attach_email'][new_order]== '1' ? 'checked' : '';?> >New Order</p>
              <p><input type="checkbox"  value="<?php echo esc_attr($value); ?>" name="whpdf_advanced_data[whpdf_field_attach_email][completed]" <?php echo isset($options['whpdf_field_attach_email'][completed]) && $options['whpdf_field_attach_email'][completed]== '1' ? 'checked' : '';?>>Completed Order</p>
              <p><input type="checkbox"  value="<?php echo esc_attr($value); ?>" name="whpdf_advanced_data[whpdf_field_attach_email][on_hold]" <?php echo isset($options['whpdf_field_attach_email'][on_hold]) && $options['whpdf_field_attach_email'][on_hold]== '1' ? 'checked' : '';?>>Order on Hold</p>
              <p><input type="checkbox"  value="<?php echo esc_attr($value); ?>" name="whpdf_advanced_data[whpdf_field_attach_email][processing]" <?php echo isset($options['whpdf_field_attach_email'][processing]) && $options['whpdf_field_attach_email'][processing]== '1' ? 'checked' : '';?>>Processing Order</p>
              <p><input type="checkbox"  value="<?php echo esc_attr($value); ?>" name="whpdf_advanced_data[whpdf_field_attach_email][customer_invoice]" <?php echo isset($options['whpdf_field_attach_email'][customer_invoice]) && $options['whpdf_field_attach_email'][customer_invoice]== '1' ? 'checked' : '';?>>Customer Invoice</p>
            </div>
            <?php
        }

        /**
         * Attach a generated pdf invoice to WooCommerce emails.
         *
         * @param array  $attachments attachments.
         * @param string $status      name of email.
         * @param object $order       order.
         *
         * @return array.
         */
        public function whpdf_attach_to_email( $attachments, $status, $order ) {
            // check if email is enabled.
            if ( ! $this->is_email_enabled( $status ) ) {
                return $attachments;
            }

            $order_id  = $order->get_id();
            $invoice   = new WHPDF_Document( $order_id, 'invoice' );
            $full_path = $invoice->get_full_path();

            // Attach invoice to email.
            $attachments[] = $full_path;

            return $attachments;
        }

        /**
         * Check if email is enabled.
         *
         * @param string $email Email ID.
         *
         * @return bool
         */
        public function is_email_enabled( $email ) {
            $options = get_option( 'whpdf_advanced_data' );
            if($email == 'on-hold'){
                $email = 'on_hold';
            }

            return isset($options['whpdf_field_attach_email'][$email]) && $options['whpdf_field_attach_email'][$email] == '1';
        }


    }

    new WHPDF_Admin;
}