<script id="videos-template" type="text/x-handlebars-template">
{{#each videos}}
<div class="pl-container">
	<img src="{{_source.thumbnail}}" alt="{{_source.title}}" class="pl-image">
	<h5 class="pl-title">{{_source.title}}</h5>
	<div class="actions-container">
	<a class="action-link {{_source.featured}}" data-featured="{{_source.featured}}" data-id="{{_id}}" data-websiteid="{{_source.website_id}}" data-playlistid="{{_source.playlist_id}}" data-type="featured" title="set as featured"><i class="fa fa-star-o"></i></a>
	</div>
	<div class="pl-description">
		{{limit_output _source.description 200}}...
	</div>
</div>		  			
{{/each}}
</script>