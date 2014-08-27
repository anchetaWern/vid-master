@section('content')
<div class="row">
	<div class="col-md-12">
		@include('partials.alert')
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<form class="form-horizontal" method="POST" action="/signup">
		  <fieldset>
		    <legend>Sign Up</legend>


		    <div class="form-group">
		      <label for="email" class="col-lg-2 control-label">Email</label>
		      <div class="col-lg-10">
		        <input type="email" class="form-control" name="email" id="email" value="{{ Input::old('email') }}">
		      </div>
		    </div>

		    <div class="form-group">
		      <label for="username" class="col-lg-2 control-label">Username</label>
		      <div class="col-lg-10">
		        <input type="text" class="form-control" name="username" id="username" value="{{ Input::old('username') }}">
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
		        <button type="submit" class="btn btn-primary">Submit</button>
		      </div>
		    </div>
		  </fieldset>
		</form>
	</div>
</div>
@stop