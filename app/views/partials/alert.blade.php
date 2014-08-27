@if(Session::get('message'))
<?php
$message = Session::get('message');
$type = $message['type'];
$text = $message['text'];
?>
<div class="alert alert-{{ $type }}">
	<button type="button" class="close" data-dismiss="alert" style="display:block;" aria-hidden="true">&times;</button>
	{{ $text }}
</div>
@elseif(count($errors) > 0)
  <div class="alert alert-danger">
    <button type="button" class="close" data-dismiss="alert" style="display:block;" aria-hidden="true">&times;</button>
    @foreach($errors->all() as $message)
      <li>{{ $message }}</li>
    @endforeach
  </div>
@else
	<div id="alert-box">
		<button type="button" class="close" data-hide="alert" aria-hidden="true">&times;</button>
		<span></span>
	</div>
@endif