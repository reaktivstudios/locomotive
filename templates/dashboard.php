<?php
/**
 * Admin dashbaord template file.
 *
 * @package Batch_processing/Admin
 */

?>
<div class="wrap">
	<h2><?php esc_html_e( get_admin_page_title() ); ?></h2>
	
	<form class="batch-processing-form" method="post">
		<ul class="batch-processes">
			<?php foreach ( $registered_batches as $slug => $batch ) : ?>
				<li>
					<input type="radio" id="<?php echo esc_attr( $slug ); ?>" name="batch-process" value="<?php echo esc_attr( $slug ); ?>">
					<label for="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $batch['name'] ); ?></label>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php submit_button( 'Run Batch Process' ); ?>
	</form>
</div>
