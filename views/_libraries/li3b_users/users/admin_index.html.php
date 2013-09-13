<div class="row">
	<div class="col-md-8">
		<?=$this->html->link('<i class="icon-plus"></i> Create New User', array('library' => 'li3b_users', 'controller' => 'users', 'action' => 'create', 'admin' => true), array('class' => 'btn btn-success', 'escape' => false)); ?>
	</div>
	<div class="col-md-4">
		<?=$this->blackprint->queryForm(array('placeholder' => 'name or e-mail...', 'buttonLabel' => 'Search', 'divClass' => 'pull-right')); ?>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<h3 id="page-heading">Users</h3>
		<table class="table table-striped">
			<thead>
				<tr>
					<th class="left">E-mail</th>
					<th>Role</th>
					<th>Created</th>
					<th class="right">Actions</th>
				</tr>
			</thead>
			<?php foreach($documents as $user) { ?>
			<tr>
				<td>
					<?php $active = ($user->active) ? 'active':'inactive'; ?>
					<?=$this->html->link($user->email, array('library' => 'li3b_users', 'controller' => 'users', 'action' => 'read', 'admin' => null, 'args' => array($user->url)), array('class' => 'tip', 'title' => $user->firstName . ' ' . $user->lastName . ' (' . $active . ')')); ?>
				</td>
				<td>
					<?=$user->role; ?>
				</td>
				<td>
					<?=$this->html->date($user->created->sec); ?>
				</td>
				<td>
					<?=$this->html->link('Edit', array('library' => 'li3b_users', 'controller' => 'users', 'action' => 'update', 'admin' => true, 'args' => array($user->_id))); ?> |
					<?=$this->html->link('Delete', array('library' => 'li3b_users', 'controller' => 'users', 'action' => 'delete', 'admin' => true, 'args' => array($user->_id)), array('onClick' => 'return confirm(\'Are you sure you want to delete ' . $user->email . '?\')')); ?>
				</td>
			</tr>
			<?php } ?>
		</table>

		<?=$this->BootstrapPaginator->paginate(); ?>
		<div class="paging-text"><em>Showing page <?=$page; ?> of <?=$total_pages; ?>. <?=$total; ?> total record<?php echo ((int) $total > 1 || (int) $total == 0) ? 's':''; ?>.</em></div>
	</div>
</div>