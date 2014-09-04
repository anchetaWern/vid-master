<script id="videos-template" type="text/x-handlebars-template">
{{#each videos}}
<div class="pl-container">
	<img src="{{_source.thumbnail}}" alt="{{_source.title}}" class="pl-image">
	<h5 class="pl-title">{{_source.title}}</h5>
	<div class="pl-description">
		{{limit_output _source.description 200}}...
	</div>
</div>		  			
{{/each}}
</script>