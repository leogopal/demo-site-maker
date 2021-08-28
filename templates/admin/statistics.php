<div class="wrap">
	<form id="mp_demo_admin_statistics" enctype="multipart/form-data" method="GET" name="" action="">
		<input type="hidden" name="page" value="mp-demo-statistics">

		<div class="mp-demo-panel">
			<label for="mp-demo-datepicker-start"><?php _e('From', 'mp-demo'); ?></label>
			<input type="text" id="mp-demo-datepicker-start" name="mp-demo-start"
			       value="<?php echo $total['start']; ?>">
			<label for="mp-demo-datepicker-end"><?php _e('To', 'mp-demo'); ?></label>
			<input type="text" id="mp-demo-datepicker-end" name="mp-demo-end"
			       value="<?php echo $total['end']; ?>">
			<input type="submit" id="mp-demo-update-statistics" class="button"
			       value="<?php _e('Update', 'mp-demo'); ?>">
		</div>
		<div class="mp-demo-content">
			<h3><?php _e('Sandboxes Activity for', 'mp-demo'); ?> <?php echo $total['start']; ?> &ndash; <?php echo $total['end']; ?></h3>
			<table class="wp-list-table widefat fixed striped mp-demo-table">
				<thead>
				<tr class="mp-demo-table-header">
					<th id="cb" class="manage-column column-cb check-column" scope="col"></th>
					<th id="columnname" class="manage-column column-columnname"
					    scope="col"><?php _e('Date', 'mp-demo'); ?></th>
					<th id="columnname" class="manage-column column-columnname"
					    scope="col"><?php _e('Created', 'mp-demo'); ?></th>
					<th id="columnname" class="manage-column column-columnname"
					    scope="col"><?php _e('Activated', 'mp-demo'); ?></th>
				</tr>
				</thead>
				<tbody id="the-list">
				<?php
				$i = 0;
				foreach ($table as $key => $row) {
					$str = '<tr>';
					$str .= '<td>' . '</td>';
					$str .= '<td>' . $row['date'] . '</td><td>' . $row['created'] . '</td><td>' . $row['activated'] . '</td>';
					$str .= '</tr>';
					echo $str;
				}
				?>
				</tbody>
				<tfoot>
				<tr class="mp-demo-table-header">
					<th id="cb" class="manage-column column-cb check-column" scope="col"></th>
					<th id="columnname" class="manage-column column-columnname"
					    scope="col"><?php _e('Date', 'mp-demo'); ?></th>
					<th id="columnname" class="manage-column column-columnname"
					    scope="col"><?php _e('Created', 'mp-demo'); ?></th>
					<th id="columnname" class="manage-column column-columnname"
					    scope="col"><?php _e('Activated', 'mp-demo'); ?></th>
				</tr>
				</tfoot>
			</table>
			<br>

			<h3><?php _e('Summary for', 'mp-demo'); ?> <?php echo $total['start']; ?> &ndash; <?php echo $total['end']; ?></h3>

			<p>
				<?php _e('Total Created:', 'mp-demo'); ?> <b><?php echo $total['created']; ?></b>
			</p>

			<p>
				<?php _e('Total Activated:', 'mp-demo'); ?> <b><?php echo $total['activated']; ?></b>
			</p>
		</div>
	</form>
	<form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="post">
		<input type="hidden" name="action" value="generate_csv">
		<input type="hidden" name="mp-demo-start" value="<?php echo esc_attr( isset( $_GET['mp-demo-start'] ) ? $_GET['mp-demo-start'] : '' ); ?>">
		<input type="hidden" name="mp-demo-end" value="<?php echo esc_attr( isset( $_GET['mp-demo-end'] ) ? $_GET['mp-demo-end'] : '' ); ?>">
		<?php wp_nonce_field( 'generate-csv' ); ?>
		<input type="submit" name="submit" class="button button-primary" value="<?php _e('Generate CSV', 'mp-demo'); ?>" />
	</form>
</div>