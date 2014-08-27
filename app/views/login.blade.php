@section('content')
@section('content')
<div class="row">
	<div class="col-md-12">
		@include('partials.alert')
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<form class="form-horizontal" method="POST" action="/login">
		  <fieldset>
		    <legend>Login</legend>
		    <div class="form-group">
		      <label for="email" class="col-lg-2 control-label">Email</label>
		      <div class="col-lg-10">
		        <input type="email" class="form-control" name="email" id="email">
		      </div>
		    </div>

		    <div class="form-group">
		      <label for="password" class="col-lg-2 control-label">Password</label>
		      <div class="col-lg-10">
		        <input type="password" class="form-control" name="password" id="password">
		      </div>
		    </div>

		    <div class="form-group">
		      <div class="col-lg-10 col-lg-offset-2">
		        <button type="submit" class="btn btn-primary">Login</button>
		      </div>
		    </div>
		  </fieldset>
		</form>
	</div>
</div>
@stop
@stop