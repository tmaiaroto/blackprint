<div class="row">
	<div class="col-md-12">
		<h3 id="page-heading">Asset Details</h3>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
	 	<?php var_dump($document->data()); ?>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<h4>Thumbnails</h4>
		<?php
		if($thumbnails) {
			foreach($thumbnails as $thumbnail) {
				var_dump($thumbnail->data());
			}
		}
		?>
	</div>
</div>
