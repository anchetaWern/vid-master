var videos_template = Handlebars.compile($('#videos-template').html());

function loadPlaylistItems(id, from){

	$.post(
		'/playlist/videos',
		{
			'from': from,
			'id': id
		},
		function(response){
			
			var hits = response.hits.hits;
			var total_results = response.hits.total;

			var template_data = {
				'videos' : hits, 
				'id': id
			};

			var html = videos_template(template_data);
			if(from == 0){
				$('#videos .load-more').data({'playlistid' : id, 'from' : 0});
				$('#videos .tab-item').html(html);
			}else{
				$('#videos .load-more').data({'from' : from});
				$('#videos .tab-item').append(html);
			}

			var total_loaded = $('#videos .pl-container').length;

			console.log(total_results);
			console.log(total_loaded);

			if(total_results > total_loaded){
				$('.load-more-container').show();
			}else{
				$('.load-more-container').hide();
			}

		}
	);

}



Handlebars.registerHelper('limit_output', function(description, limit){
  return description.substr(0, limit);
});


$('.pl-image').click(function(){
	var self = $(this);
	$('.pl-image').removeClass('selected');
	self.addClass('selected');

	var id = self.data('id'); 
	var from = 0;
	
	loadPlaylistItems(id, from);
});

$('#videos').on('click', '.load-more', function(e){

	e.preventDefault();

	var self = $(this);
	var id = self.data('playlistid');
	var from = self.data('from') + 10;

	loadPlaylistItems(id, from);
});


$('#videos').on('click', '.action-link', function(e){

	e.preventDefault();

	var self = $(this);

	
	var id = self.data('id');
	var playlist_id = self.data('playlistid');
	var status = self.data('featured');
	$('#videos .action-link.featured').removeClass('featured').data('featured', '');

	

	$.post(
		'/video/featured',
		{
			'id': id,
			'playlist_id': playlist_id,
			'status': status
		},
		function(response){
			if(!status){
				self.addClass('featured').data('featured', 'featured');
			}
		}
	);
});