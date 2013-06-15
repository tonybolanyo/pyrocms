<div class="padded">
	<?php if (! empty($views)): ?>

	    <table class="table table-hover table-bordered table-striped">
			<thead>
				<tr>	
				    <th><?php echo lang('streams:label.title');?> TITLE</th>
				    <th><?php echo lang('streams:label.sort');?> SORT</th>
				    <th><?php echo lang('streams:label.limit');?> LIMIT</th>
				    <th class="text-center"><?php echo lang('streams:label.is_locked');?> IS LOCKED</th>
				    <th></th>
				</tr>
			</thead>
			<tbody>		
			<?php foreach ($views as $view):?>
				<tr>
					<td><?php echo $this->fields->translate_label($view->title); ?></td>
					<td><?php echo $view->sort; ?></td>
					<td><?php echo $view->limit; ?></td>
					<td class="text-center"><?php echo $view->is_locked == 'yes' ? '<i class="icon-lock"></i>' : null; ?></td>
					<td>
						<div class="btn-group pull-right">
							<?php
							
								$all_buttons = array();

								if (isset($buttons))
								{
									foreach($buttons as $button)
									{
										// don't render button if field is locked and $button['locked'] is set to TRUE
										if($view->is_locked == 'yes' and isset($button['locked']) and $button['locked']) continue;
										$class = (isset($button['confirm']) and $button['confirm']) ? 'btn btn-small confirm' : 'btn btn-small';
										$class .= (isset($button['class']) and ! empty($button['class'])) ? ' '.$button['class'] : null;
										$all_buttons[] = anchor(str_replace('-view_id-', $view->id, $button['url']), $button['label'], 'class="'.$class.'"');
									}
								}
							
								echo implode('&nbsp;', $all_buttons);
								unset($all_buttons);
								
							?>
						</div>
					</td>
				</tr>
			<?php endforeach;?>
			</tbody>
	    </table>

	<?php else: ?>

	<div class="alert">
		<?php
			
			if (isset($no_views_message) and $no_views_message)
			{
				echo lang_label($no_views_message);
			}
			else
			{
				echo lang('streams:no_data').'Test';
			}

		?>
	</div><!--.no_data-->

	<?php endif;?>
</div>