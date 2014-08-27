@section('content')
<div class="row">
	<div class="col-md-12">
		@include('partials.alert')
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<form class="form-horizontal" method="POST" action="/websites/{{ $website->id }}">
		  <fieldset>
		    <legend>Edit Website</legend>
		    <div class="form-group">
		      <label for="title" class="col-lg-2 control-label">Title</label>
		      <div class="col-lg-10">
		        <input type="text" class="form-control" name="title" id="title" value="{{ Input::old('title', $website->title) }}">
		      </div>
		    </div>

		    <div class="form-group">
		      <label for="tagline" class="col-lg-2 control-label">Tagline</label>
		      <div class="col-lg-10">
		        <input type="text" class="form-control" name="tagline" id="tagline" value="{{ Input::old('tagline', $website->tagline) }}">
		      </div>
		    </div>

		    <div class="form-group">
		      <label for="about" class="col-lg-2 control-label">About</label>
		      <div class="col-lg-10">
		        <textarea class="form-control" rows="3" name="about" id="about" value="{{ Input::old('about', $website->about) }}">{{ Input::old('about', $website->about) }}</textarea>
		        <span class="help-block">Tell something about your website.</span>
		      </div>
		    </div>
		
			<div class="form-group">
			<?php
			$youtube_checked = '';
			$vimeo_checked = '';
			if($website->type == 'youtube'){
				$youtube_checked = 'checked';
			}else if($website->type == 'vimeo'){
				$vimeo_checked = 'checked';
			}
			?>

			  <label class="col-lg-2 control-label">Type</label>
			  <div class="col-lg-10">
			    <div class="radio">
			      <label>
			        <input type="radio" name="type" id="youtube" value="youtube" {{ $youtube_checked }}>
			        Youtube
			      </label>
			    </div>
			    <div class="radio">
			      <label>
			        <input type="radio" name="type" id="vimeo" value="vimeo" {{ $vimeo_checked }}>
			        Vimeo
			      </label>
			    </div>
				</div>
			</div>	

		    <div class="form-group">
		      <label for="account_id" class="col-lg-2 control-label">Account ID</label>
		      <div class="col-lg-10">
		        <input type="text" class="form-control" name="account_id" id="account_id" value="{{ Input::old('account_id', $website->account_id) }}">
		        <span class="help-block">Can be a youtube or vimeo username.</span>
		      </div>
		    </div>	

		    <div class="form-group">
		      <label for="domain_name" class="col-lg-2 control-label">Domain Name</label>
		      <div class="col-lg-10">
		        <input type="text" class="form-control" name="domain_name" id="domain_name" value="{{ Input::old('domain_name', $website->domain_name) }}">
		        <span class="help-block">e.g. my-awesomewebsite.com. A unique URL will be assigned if left blank.</span>
		      </div>
		    </div>	

			<div class="form-group">
			<?php
			$public_checked = '';
			if($website->public == 1){
				$public_checked = 'checked';
			}
			?>
				<label for="active" class="col-lg-2 control-label">Public</label>
				<div class="col-lg-10">
					<div class="checkbox">
			          <label>
			            <input type="checkbox" name="public" id="public" {{ $public_checked }}>
			          </label>
			        </div>
				</div>
			</div>


		    <div class="form-group">
		      <div class="col-lg-10 col-lg-offset-2">
		        <button type="submit" class="btn btn-primary">Update Website</button>
		      </div>
		    </div>
		  </fieldset>
		</form>		
	</div>
</div>
@stop