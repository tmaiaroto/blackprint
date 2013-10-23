<div class="row">
	<div class="col-md-8">
		<?=$this->html->link('<i class="icon-plus"></i> Create New Content', array('library' => 'blackprint', 'controller' => 'content', 'action' => 'create', 'admin' => true), array('class' => 'btn btn-success', 'escape' => false)); ?>
	</div>
	<div class="col-md-4">
		<?=$this->blackprint->queryForm(array('placeholder' => 'title...', 'buttonLabel' => 'Search', 'divClass' => 'pull-right')); ?>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<h3 id="page-heading">Content</h3>

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
					if($currentSortField == 'title') {
						$titleArrow = ($currentSortDirection == 'desc') ? ' <i class="icon-caret-down"></i>':' <i class="icon-caret-up"></i>';
					}
					?>
					<?=$this->html->link('Title' . $titleArrow, array('admin' => true, 'library' => 'blackprint', 'controller' => 'content', 'action' => 'index', 'page' => $page, 'sort' => 'title,' . $sortDirection), array('escape' => false)); ?>
					</th>
					<th>
					<?php
					$typeArrow = '';
					if($currentSortField == 'type') {
						$typeArrow = ($currentSortDirection == 'desc') ? ' <i class="icon-caret-down"></i>':' <i class="icon-caret-up"></i>';
					}
					?>
					<?=$this->html->link('Type' . $typeArrow, array('admin' => true, 'library' => 'blackprint', 'controller' => 'content', 'action' => 'index', 'page' => $page, 'sort' => '_type,' . $sortDirection), array('escape' => false)); ?>
					</th>
					<th>
					<?php
					$activeArrow = '';
					if($currentSortField == 'published') {
						$activeArrow = ($currentSortDirection == 'desc') ? ' <i class="icon-caret-down"></i>':' <i class="icon-caret-up"></i>';
					}
					?>
					<?=$this->html->link('Published' . $activeArrow, array('admin' => true, 'library' => 'blackprint', 'controller' => 'content', 'action' => 'index', 'page' => $page, 'sort' => 'published,' . $sortDirection), array('escape' => false)); ?>
					</th>
					<th>
					<?php
					$createdArrow = '';
					if($currentSortField == 'created') {
						$createdArrow = ($currentSortDirection == 'desc') ? ' <i class="icon-caret-down"></i>':' <i class="icon-caret-up"></i>';
					}
					?>
					<?=$this->html->link('Created' . $createdArrow, array('admin' => true, 'library' => 'blackprint', 'controller' => 'content', 'action' => 'index', 'page' => $page, 'sort' => 'created,' . $sortDirection), array('escape' => false)); ?>	
					</th>
					<th class="right">Actions</th>
				</tr>
			</thead>
			<?php foreach($documents as $document) { ?>
			<tr>
				<td>
					<?php $active = ($document->published) ? 'active':'inactive'; ?>
					<?=$this->html->link($document->title, array('library' => 'blackprint', 'controller' => 'content', 'action' => 'update', 'admin' => true, 'args' => array($document->_id))); ?>
				</td>
				<td>
					<?=$document->_type; ?>
				</td>
				<td>
					<?php echo ($document->published) ? '<i class="icon-ok" style="color: green"></i>':'<i class="icon-remove" style="color: red"></i>'; ?>
				</td>
				<td>
					<?=$this->blackprint->date($document->created->sec); ?>
				</td>
				<td>
					<?=$this->html->link('Edit', array('library' => 'blackprint', 'controller' => 'content', 'action' => 'update', 'admin' => true, 'args' => array($document->_id))); ?> |
					<?=$this->html->link('Delete', array('library' => 'blackprint', 'controller' => 'content', 'action' => 'delete', 'admin' => true, 'args' => array($document->_id)), array('onClick' => 'return confirm(\'Are you sure you want to delete ' . $document->title . '?\')')); ?>
				</td>
			</tr>
			<?php } ?>
		</table>

		<?=$this->BlackprintPaginator->paginate(); ?>
		<div class="paging-text"><em>Showing page <?=$page; ?> of <?=$totalPages; ?>. <?=$total; ?> total record<?php echo ((int) $total > 1 || (int) $total == 0) ? 's':''; ?>.</em></div>
	</div>
</div>