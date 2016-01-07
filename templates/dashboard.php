<?php
/**
 * Admin dashbaord template file.
 *
 * @package Batch_processing/Admin
 */

?>
<div id="batch-main" class="batch-processing-form"></div>

<script type="text/html" id="tmpl-batch-processing-results">	
	<h2>{{ data.status }}: {{ data.batch }}</h2>
	<div class="progress-bar" data-progress="{{ data.progress }}">
		<span class="progress-bar__text">Progress: {{ data.progress }}%</span>
		<div class="progress-bar__visual"></div>
	</div>
</script>
