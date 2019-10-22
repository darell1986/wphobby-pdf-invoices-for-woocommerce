<?php
/**
 * WHobby WooCommerce Product Filter Panel
 */
?>
<h2 class="nav-tab-wrapper">
    <?php
    $url = admin_url().'admin.php?page=whpdf-panel';
    $premium_url = 'https://wphobby.com/wp/woo-pdf-invoice-menu';
    ?>
    <a href="<?php echo esc_url($url); ?>" class="nav-tab <?php echo ($_GET[ 'page' ] == 'whpdf-panel' && !isset($_GET[ 'tab' ]) )? 'nav-tab-active' : ''; ?>"><?php esc_html_e('General', 'whpdf-admin' ); ?></a>
    <a href="<?php echo esc_url($url.'&tab=advanced'); ?>" class="nav-tab <?php echo $_GET[ 'tab' ] == 'advanced' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Advanced', 'whpdf-admin' ); ?></a>
    <a href="<?php echo esc_url($url.'&tab=server'); ?>" class="nav-tab <?php echo $_GET[ 'tab' ] == 'server' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Server Info', 'whpdf-admin' ); ?></a>
    <a href="<?php echo esc_url($premium_url); ?>" class="nav-tab <?php echo $_GET[ 'tab' ] == 'premium' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Go Premium Version', 'whpdf-admin' ); ?></a>
</h2>