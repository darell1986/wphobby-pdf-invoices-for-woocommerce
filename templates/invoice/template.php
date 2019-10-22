<?php
/**
 * The Template for invoice
 *
 * Override this template by copying it to yourtheme/invoice/template.php
 *
 * @author      WPHobby
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
?>

<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
	<?php
	/**
	 * whpdf_invoice_template_head hook
	 *
	 * @hooked add_style_files - 10 (add css file based on type of current document
	 */
	do_action( 'whpdf_invoice_template_head' );
	?>
</head>

<body>
<?php
/**
 * whpdf_invoice_template_content hook
 *
 * @hooked add_invoice_content - 10 (add invoice template content
 */
do_action( 'whpdf_invoice_template_content' );
?>

</body>
</html>