<?php
/**
 * Custom Style template
 */

?>
<div id="tab-activate" class="panel whpdf-panel">
    <div class="panel-wrapper">
        <h3>Selectors Settings</h3>
        <form id="whpdf-panel" method="post" action="options.php">
            <?php
            settings_fields( 'whpdf_custom' );
            do_settings_sections( 'whpdf_panel_custom_style' );
            submit_button( 'Save Settings' );
            ?>
        </form>
    </div>
</div>
