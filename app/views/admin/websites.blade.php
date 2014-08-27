@section('content')
<div class="row">
	<div class="col-md-12">
		@include('partials.alert')
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		@if($website_count > 0)
		<table class="table table-striped table-hover">
		  <thead>
		    <tr>
		      <th>Title</th>
		      <th>Type</th>
		      <th>URL</th>
		      <th>Public</th>
		    </tr>
		  </thead>
		  <tbody>
		  @foreach($websites as $w)
		    <tr>
		  		<td>{{ $w->title }}</td>
		  		<td>{{ $w->type }}</td>
		  		<?php
		  		$url = $base_url . '/' . $w->long_id;
		  		if(!empty($w->domain_name)){
		  			$url = 'http://' . $w->domain_name;
		  		}
		  		?>
		  		<td><a href="{{ $url }}" target="_blank">{{ $url }}</a></td>
		  		<?php
		  		$public = 'no';
		  		if($w->public == 1){
		  			$public = 'yes';
		  		}
		  		?>
		  		<td>{{ $public }}</td>
		    </tr>
		   @endforeach
		  </tbody>
		</table> 
		@else
		<div class="alert alert-info">
			You haven't created any websites yet. <a href="/websites/new">Click here</a> to create one. 
		</div>
		@endif
	</div>
</div>
@stop