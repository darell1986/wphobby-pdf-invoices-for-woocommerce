<?php
/**
 * Server Settings template
 */

?>
<div id="tab-activate" class="panel whpdf-panel">
    <div class="panel-wrapper">
        <h3>Server Info</h3>
        <form id="whpdf-panel" method="post" action="options.php">
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row">
                        <label for="server_ip">Server IP</label>
                        <div class="tooltip">
                            <i class="fa fa-question-circle"></i>
                            <span class="tooltiptext">Your Server IP Address</span>
                        </div>
                    </th>
                    <td>
                        <span><?php echo esc_html($_SERVER['SERVER_ADDR']);?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="php_version">PHP version</label>
                        <div class="tooltip">
                            <i class="fa fa-question-circle"></i>
                            <span class="tooltiptext">Your Server PHP Version</span>
                        </div>
                    </th>
                    <td>
                        <span><?php echo phpversion();?></span>
                    </td>
                </tr>
                </tbody>
            </table>
        </form>
    </div>
</div>
