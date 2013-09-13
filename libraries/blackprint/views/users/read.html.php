<div class="row">
	<div class="span12">
		<h2 id="page-heading">User Profile</h2>
		<br />
		<h3><?=$user->firstName; ?> <?=$user->lastName; ?></h3>
		<p>Member since <?=$this->time->to('meridiem', $user->created); ?>.</p>
		<p>Last seen <?=$this->time->to('meridiem_short', $user->lastLoginTime->sec); ?>.</p>
	</div>
</div>