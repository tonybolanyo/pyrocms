<div class="hidden additional-<?php echo $stream->stream_slug; ?>-filters-data">

	<section class="NONE">
		Select a field...
	</section>

	<?php foreach ($filters['additional_filters'] as $params): ?>
	<section class="<?php echo $params['field']; ?>">
		<?php echo lang_label(isset($params['label']) ? $params['label'] : humanize($params['field'])); ?>
	</section>
	<?php endforeach; ?>

</div>