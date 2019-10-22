<?php
/**
 * WHobby WooCommerce Product Filter Panel
 */
?>
<h2 class="nav-tab-wrapper">
    <?php
    $url = admin_url().'admin.php?page=whpdf-help';
    $premium_url = 'https://wphobby.com/wp/woo-pdf-invoice-menu';
    ?>
    <a href="<?php echo esc_url($url); ?>" class="nav-tab <?php echo ($_GET[ 'page' ] == 'whpdf-panel' && !isset($_GET[ 'tab' ]) )? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Help & Guide', 'whpdf-admin' ); ?></a>
    <a href="<?php echo esc_url($url.'&tab=change-log'); ?>" class="nav-tab <?php echo $_GET[ 'tab' ] == 'change-log' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Change Log', 'whpdf-admin' ); ?></a>
    <a href="<?php echo esc_url($premium_url); ?>" class="nav-tab <?php echo $_GET[ 'tab' ] == 'premium' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Go Premium Version', 'whpdf-admin' ); ?></a>
</h2>