<div class="row">
	<div class="col-md-8">
		<?php 
		// Old admin editor.
		// $this->html->link('<i class="fa fa-plus"></i> New Blog Post', array('library' => 'blackprint', 'controller' => 'posts', 'action' => 'create', 'admin' => true), array('class' => 'btn btn-success', 'escape' => false));
		// New way will be to create a new story on the front-end of the site using the Medium editor clone.
		?>
		<?=$this->html->link('<i class="fa fa-plus"></i> New Blog Post', array('library' => 'blackprint', 'controller' => 'posts', 'action' => 'create_blank', 'admin' => true), array('class' => 'btn btn-success', 'escape' => false)); ?>
	</div>
	<div class="col-md-4">
		<?=$this->blackprint->queryForm(array('placeholder' => 'search title or body...', 'buttonLabel' => 'Search', 'divClass' => 'pull-right')); ?>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<h3 id="page-heading">Blog Posts</h3>

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
					<?=$this->html->link('Title' . $titleArrow, array('admin' => true, 'library' => 'blackprint', 'controller' => 'posts', 'action' => 'index', 'page' => $page, 'sort' => 'title,' . $sortDirection), array('escape' => false)); ?>
					</th>
					<th>
					<?php
					$authorArrow = '';
					if($currentSortField == 'authorAlias') {
						$authorArrow = ($currentSortDirection == 'desc') ? ' <i class="icon-caret-down"></i>':' <i class="icon-caret-up"></i>';
					}
					?>
					<?=$this->html->link('Author' . $authorArrow, array('admin' => true, 'library' => 'blackprint', 'controller' => 'posts', 'action' => 'index', 'page' => $page, 'sort' => '_author.lastName,' . $sortDirection), array('escape' => false)); ?>
					</th>
					<th>
					<?php
					$createdArrow = '';
					if($currentSortField == 'created') {
						$createdArrow = ($currentSortDirection == 'desc') ? ' <i class="icon-caret-down"></i>':' <i class="icon-caret-up"></i>';
					}
					?>
					<?=$this->html->link('Created' . $createdArrow, array('admin' => true, 'library' => 'blackprint', 'controller' => 'posts', 'action' => 'index', 'page' => $page, 'sort' => 'created,' . $sortDirection), array('escape' => false)); ?>	
					</th>
					<th class="right">Actions</th>
				</tr>
			</thead>
			<?php foreach($documents as $document) { ?>
			<tr class="valign-middle-row">
				<td class="index-list-item-title">
					<?php echo $document->title; ?>
				</td>
				<td>
					<?=$document->_author->firstName . ' ' . $document->_author->lastName; ?>
				</td>
				<td>
					<?=$this->blackprint->date($document->created->sec); ?>
				</td>
				<td>
					<?=$this->html->link('View', array('library' => 'blackprint', 'controller' => 'posts', 'action' => 'read', 'args' => array($document->url)), array('target' => '_blank')); ?> |
					<?=$this->html->link('Edit', array('library' => 'blackprint', 'controller' => 'posts', 'action' => 'draft', 'args' => array($document->draftHash))); ?> |
					<?=$this->html->link('Delete', array('library' => 'blackprint', 'controller' => 'posts', 'action' => 'delete', 'admin' => true, 'args' => array($document->_id)), array('onClick' => 'return confirm(\'Are you sure you want to delete ' . $document->title . '?\')')); ?>
				</td>
			</tr>
			<?php } ?>
		</table>

		<?=$this->BlackprintPaginator->paginate(); ?>
		<div class="paging-text"><em>Showing page <?=$page; ?> of <?=$totalPages; ?>. <?=$total; ?> total record<?php echo ((int) $total > 1 || (int) $total == 0) ? 's':''; ?>.</em></div>
	</div>
</div>