<?php
/**
 * General Settings template
 */

?>
<div id="tab-activate" class="panel whpdf-panel">
    <div class="panel-wrapper">
        <h3>General Settings</h3>
        <form id="whpdf-panel" method="post" action="options.php">
            <?php
            settings_fields( 'whpdf_general' );
            do_settings_sections( 'whpdf_panel_general' );
            submit_button( 'Save Settings' );
            ?>
        </form>
    </div>
</div>
