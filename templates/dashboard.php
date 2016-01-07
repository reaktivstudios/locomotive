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
		<div id="batch-main"></div>
	</form>
</div>

<div class="batch-processing-overlay">
	<div class="close">close</div>
	<div class="batch-overlay__inner"></div>
</div><!-- .batch-processing-overlay -->

<script type="text/html" id="tmpl-batch-processing-results">	
	<h2>{{ data.status }}: {{ data.batch }}</h2>
	<div class="progress-bar" data-progress="{{ data.progress }}">
		<span class="progress-bar__text">Progress: {{ data.progress }}%</span>
		<div class="progress-bar__visual"></div>
	</div>
</script>
