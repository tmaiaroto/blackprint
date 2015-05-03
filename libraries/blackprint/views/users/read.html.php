<div class="row">
	<div class="col-xs-12">
		<br />
		<h3><?=$user->firstName; ?> <?=$user->lastName; ?></h3>
		<p>Member since <?=$this->BlackprintTime->to('meridiem', $user->created); ?>.</p>
		<p>Last seen <?=$this->BlackprintTime->to('meridiem_short', $user->lastLoginTime->sec); ?>.</p>
	</div>
</div>