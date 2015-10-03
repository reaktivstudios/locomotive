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
					<input type="radio" id="<?php echo esc_attr( $slug ); ?>" name="batch_process" class="batch-process-option" value="<?php echo esc_attr( $slug ); ?>">
					<label for="<?php echo esc_attr( $slug ); ?>">
						<?php echo esc_html( $batch['name'] ); ?>
						<small>
							last run: <?php echo esc_html( Batch_Process\time_ago( $batch['last_run'] ) ); ?>
						</small>
					</label>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php submit_button( 'Run Batch Process' ); ?>
	</form>
</div>

<div class="batch-processing-overlay">
	<div class="close">close</div>
	<div class="batch-overlay__inner"></div>
</div><!-- .batch-processing-overlay -->

<script type="text/html" id="tmpl-batch-processing-results">	
	<h2>Running: {{ data.batch }}</h2>
	<div class="batch-progress" data-progress="{{ data.progress }}">
		Progress: {{ data.progress }}%
	</div>
</script>
