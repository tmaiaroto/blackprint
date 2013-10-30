<div class="row">
	<div class="col-md-8">
		<?=$this->html->link('<i class="icon-plus"></i> Upload New Asset', array('library' => 'blackprint', 'controller' => 'assets', 'action' => 'create', 'admin' => true), array('class' => 'btn btn-success', 'escape' => false)); ?>
	</div>
	<div class="col-md-4">
		<?=$this->blackprint->queryForm(array('placeholder' => 'filename...', 'buttonLabel' => 'Search', 'divClass' => 'pull-right')); ?>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<h3 id="page-heading">Assets</h3>

		<?php
		// TODO: Make helper...Or just go back to using Angular.js
		$page = $this->_request->page ?: 1;
		$sort = $this->_request->sort ?: false;

		$currentSortDirection = 'desc';
		$currentSortField = 'created';
		if($sort) {
			$sortPieces = explode(',', $sort);
			$currentSortDirection = (isset($sortPieces[1])) ? $sortPieces[1]:'desc';
			$currentSortField = (isset($sortPieces[0])) ? $sortPieces[0]:$currentSortField;
		}
		$sortDirection = ($currentSortDirection == 'desc') ? 'asc':'desc';
		?>

		<table class="table table-striped">
			<thead>
				<tr>
					<th class="left">
					<?php
					$titleArrow = '';
					if($currentSortField == 'originalFilename') {
						$titleArrow = ($currentSortDirection == 'desc') ? ' <i class="icon-caret-down"></i>':' <i class="icon-caret-up"></i>';
					}
					?>
					<?=$this->html->link('Filename' . $titleArrow, array('admin' => true, 'library' => 'blackprint', 'controller' => 'assets', 'action' => 'index', 'page' => $page, 'sort' => 'originalFilename,' . $sortDirection), array('escape' => false)); ?>
					</th>
					<th>
					<?php
					$typeArrow = '';
					if($currentSortField == 'contentType') {
						$typeArrow = ($currentSortDirection == 'desc') ? ' <i class="icon-caret-down"></i>':' <i class="icon-caret-up"></i>';
					}
					?>
					<?=$this->html->link('Content Type' . $typeArrow, array('admin' => true, 'library' => 'blackprint', 'controller' => 'assets', 'action' => 'index', 'page' => $page, 'sort' => 'contentType,' . $sortDirection), array('escape' => false)); ?>
					</th>
					<th>
					<?php
					$createdArrow = '';
					if($currentSortField == 'uploadDate') {
						$createdArrow = ($currentSortDirection == 'desc') ? ' <i class="icon-caret-down"></i>':' <i class="icon-caret-up"></i>';
					}
					?>
					<?=$this->html->link('Upload Date' . $createdArrow, array('admin' => true, 'library' => 'blackprint', 'controller' => 'assets', 'action' => 'index', 'page' => $page, 'sort' => 'uploadDate,' . $sortDirection), array('escape' => false)); ?>	
					</th>
					<th class="right">Actions</th>
				</tr>
			</thead>
			<?php foreach($documents as $document) { ?>
			<tr>
				<td>
					<?=$document->originalFilename; ?>
					<?php // $this->html->link($document->filename, array('library' => 'blackprint', 'controller' => 'assets', 'action' => 'update', 'admin' => true, 'args' => array($document->_id))); ?>
				</td>
				<td>
					<?=$document->contentType; ?>
				</td>
				<td>
					<?=$this->blackprint->date($document->uploadDate->sec); ?>
				</td>
				<td>
					<?=$this->html->link('View Details', array('library' => 'blackprint', 'controller' => 'assets', 'action' => 'read', 'admin' => true, 'args' => array($document->_id))); ?> |
					<?=$this->html->link('Delete', array('library' => 'blackprint', 'controller' => 'assets', 'action' => 'delete', 'admin' => true, 'args' => array($document->_id)), array('onClick' => 'return confirm(\'Are you sure you want to delete ' . $document->title . '?\')')); ?>
				</td>
			</tr>
			<?php } ?>
		</table>

		<?=$this->BlackprintPaginator->paginate(); ?>
		<div class="paging-text"><em>Showing page <?=$page; ?> of <?=$totalPages; ?>. <?=$total; ?> total record<?php echo ((int) $total > 1 || (int) $total == 0) ? 's':''; ?>.</em></div>
	</div>
</div>