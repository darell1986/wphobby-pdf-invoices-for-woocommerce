<?php if ( 'yes' == get_option( 'whpdf_show_invoice_notes' ) ) : ?>
	<div class="notes">
		<span class="notes-title"><?php esc_html_e("Notes", 'wphobby-woo-pdf-invoice'); ?></span>
		<span><?php echo nl2br( get_option( 'whpdf_invoice_notes' ) ); ?></span>
	</div>
<?php endif; ?>

<?php if ( 'yes' == get_option( 'whpdf_show_invoice_footer' ) ) : ?>
	<footer>
		<span><?php echo nl2br( get_option( 'whpdf_invoice_footer' ) ); ?></span>
	</footer>
<?php endif; ?>
