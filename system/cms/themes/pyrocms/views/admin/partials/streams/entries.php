<?php $this->load->view('admin/partials/streams/filters'); ?>

<?php if ($entries) { ?>

    <table class="table">
		<thead>
			<tr>
				<?php if ($this->input->get($stream->stream_slug.'-columns')): ?>

					<?php foreach( explode('|', $this->input->get($stream->stream_slug.'-columns')) as $column ): ?>
					<th><?php echo lang_label($stream_fields->$column->field_name); ?></th>
					<?php endforeach; ?>

				<?php else: ?>
					<?php foreach ($stream->view_options as $view_option): ?>
					<th><?php echo lang_label($stream_fields->$view_option->field_name); ?></th>
					<?php endforeach; ?>
				<?php endif; ?>
			    <th></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($entries as $field => $data_item) { ?>

			<tr>

				<?php if ($this->input->get($stream->stream_slug.'-columns')): ?>

					<?php foreach( explode('|', $this->input->get($stream->stream_slug.'-columns')) as $column ): ?>
					<td>
					<?php
					
						if ($column == 'created' or $column == 'updated')
						{
							if ($data_item->$column):echo date('M j Y g:i a', $data_item->$column); endif;	
						}				
						elseif ($column == 'created_by')
						{
						
							?><a href="<?php echo site_url('admin/users/edit/'. $data_item->created_by_user_id); ?>"><?php echo $data_item->created_by_username; ?></a><?php
						}
						else
						{
							echo $data_item->$column;
						}
						
					?>
					</td>
					<?php endforeach; ?>

				<?php else: ?>

					<?php if (is_array($stream->view_options)): ?>
						<?php foreach( $stream->view_options as $view_option ): ?>
						<td>
										
						<?php
						
							if ($view_option == 'created' or $view_option == 'updated')
							{
								if ($data_item->$view_option):echo date('M j Y g:i a', $data_item->$view_option); endif;	
							}				
							elseif ($view_option == 'created_by')
							{
							
								?><a href="<?php echo site_url('admin/users/edit/'. $data_item->created_by_user_id); ?>"><?php echo $data_item->created_by_username; ?></a><?php
							}
							else
							{
								echo $data_item->$view_option;
							}
							
						?>
						</td>
						<?php endforeach; ?>
					<?php endif; ?>

				<?php endif; ?>
				<td>
				
					<?php
				
						if (isset($buttons))
						{
							echo '<div class="btn-group pull-right">';
							$all_buttons = array();

							foreach($buttons as $button)
							{
								$class = (isset($button['confirm']) and $button['confirm']) ? 'btn btn-small confirm' : 'btn btn-small';
								$class .= (isset($button['class']) and ! empty($button['class'])) ? ' '.$button['class'] : null;
								$all_buttons[] = anchor(str_replace('-entry_id-', $data_item->id, $button['url']), $button['label'], 'class="'.$class.'"');
							}
						
							echo implode('&nbsp;', $all_buttons);
							unset($all_buttons);
							echo '</div>';
						}
						
					?>			
				</td>
			</tr>
		<?php } ?>
		</tbody>
    </table>
    
<?php echo $pagination['links']; ?>

<?php } else { ?>

<div class="alert margin">
	<?php
		
		if (isset($no_entries_message) and $no_entries_message)
		{
			echo lang_label($no_entries_message);
		}
		else
		{
			echo lang('streams:no_entries');
		}

	?>
</div><!--.no_data-->

<?php } ?>
