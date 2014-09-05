<?php
class AdminController extends BaseController {

	protected $layout = 'layouts.admin';

	public function index(){

		$this->layout->title = 'Admin';
		$this->layout->content = View::make('admin.index');
	}

	public function newWebsite(){

		$this->layout->title = 'Create New Website';
		$this->layout->switch = true;
		$this->layout->content = View::make('admin.new_website');

	}

	public function createWebsite(){

		$user_id = Auth::user()->id;

		$rules = array(
			'title' => 'required',
			'type' => 'required',
			'account_id' => 'required'
		);

		$validator = Validator::make(Input::all(), $rules);

		if($validator->fails()){
			return Redirect::to('/websites/new')
						->withErrors($validator)
						->withInput();
		}else{

			$client = new GuzzleHttp\Client();

			$title = Input::get('title');
			$tagline = Input::get('tagline');
			$about = Input::get('about');
			$type = Input::get('type');
			$account_id = Input::get('account_id');
			$domain_name = Input::get('domain_name');
			$is_public = 0;
			if(Input::has('public')){
				$is_public = 1;
			}

			$website = new Website;
			$website->user_id = $user_id;
			$website->long_id = Str::slug(substr($title, 0, 160) . '-' . str_random(6));
			$website->type = $type;
			$website->domain_name = $domain_name;
			$website->title = $title;
			$website->tagline = $tagline;
			$website->about = $about;
			$website->public = $is_public;
			$website->save();
			$website_id = $website->id;

			if($type == 'youtube'){			
				$youtube = new Youtube(array('key' => Config::get('keys.youtube')));
				$youtube_channel = $youtube->getChannelByName($account_id);

				$site_channel_id = $youtube_channel->id;
				$site_channel_title = $youtube_channel->snippet->title;
				$site_channel_description = $youtube_channel->snippet->description;
				$site_channel_thumbnail = $youtube_channel->snippet->thumbnails->high->url;
			}else if($type == 'vimeo'){

				$vimeo = $client->get("http://vimeo.com/api/v2/{$account_id}/info.json");
				$vimeo_channel = json_decode($vimeo->getBody(), true); 

				$site_channel_id = $vimeo_channel['id'];
				$site_channel_title = $vimeo_channel['display_name'];
				$site_channel_description = strip_tags($vimeo_channel['bio']);
				$site_channel_thumbnail = $vimeo_channel['portrait_huge'];

			}

			$channel = new Channel;
			$channel->user_id = $user_id;
			$channel->website_id = $website_id;
			$channel->channel_id = $site_channel_id;
			$channel->title = $site_channel_title;
			$channel->description = $site_channel_description;
			$channel->thumbnail = $site_channel_thumbnail;
			$channel->save();
			$channel_id = $channel->id;


			$next_playlisttoken = '';


			if($type == 'youtube'){

				do{

					$playlists = $youtube->getPlaylistsByChannelId($youtube_channel->id, array('maxResults' => 50, 'pageToken' => $next_playlisttoken));
					$next_playlisttoken = $youtube->page_info;

					if(!empty($playlists)){			
						foreach($playlists as $pl){

							//only cached playlists that are public
							if($pl->status->privacyStatus == 'public'){					
								$playlist_id = $pl->id;

								$playlist_params['body']  = array(
									'id' => $playlist_id,
									'user_id' => $user_id,
									'website_id' => $website_id,
									'channel_id' => $channel_id,
									'title' => $pl->snippet->title,
									'playlist_type' => 'youtube',
									'description' => $pl->snippet->description,
									'thumbnail' => $pl->snippet->thumbnails->default->url,
									'published_at' => $pl->snippet->publishedAt
								);
								$playlist_params['index'] = 'video-websites';
								$playlist_params['type']  = 'playlist';
								$playlist_params['id']    = $playlist_id;
								$ret = Es::index($playlist_params);	

								$next_videostoken = '';

								do{
									$playlist_items = $youtube->getPlaylistItemsByPlaylistId(
										$playlist_id, array('maxResults' => 50, 'pageToken' => $next_videostoken)
									);
									$next_videostoken = $youtube->page_info;

									if(!empty($playlist_items)){
										foreach($playlist_items as $video){

											if($video->status->privacyStatus == 'public'){
												$video_id = $video->id;

												$video_params['body']  = array(
													'id' => $video_id,
													'video_id' => $video->contentDetails->videoId,
													'user_id' => $user_id,
													'website_id' => $website_id,
													'channel_id' => $channel_id,
													'playlist_id' => $playlist_id,
													'video_type' => 'youtube',
													'position' => $video->snippet->position,
													'title' => $video->snippet->title,
													'description' => $video->snippet->description,
													'thumbnail' => $video->snippet->thumbnails->default->url,
													'published_at' => $video->snippet->publishedAt 
												);

												$video_params['index'] = 'video-websites';
												$video_params['type']  = 'video';
												$video_params['id']    = $video_id;
												$ret = Es::index($video_params);	
											}

										}
									}
								
								}while(is_string($next_videostoken));


							}
						}
					}

					

				}while(is_string($next_playlisttoken));
			}else if($type == 'vimeo'){

				$playlists_response = $client->get("http://vimeo.com/api/v2/{$account_id}/channels.json");
				$playlists = json_decode($playlists_response->getBody(), true); 

				if(!empty($playlists)){
					foreach($playlists as $pl){

						$playlist_id = $pl['id'];

						$playlist_params['body']  = array(
							'id' => $playlist_id,
							'user_id' => $user_id,
							'website_id' => $website_id,
							'channel_id' => $channel_id,
							'title' => $pl['name'],
							'playlist_type' => 'vimeo',
							'description' => $pl['description'],
							'thumbnail' => $pl['logo'],
							'published_at' => $pl['created_on']
						);

						$playlist_params['index'] = 'video-websites';
						$playlist_params['type']  = 'playlist';
						$playlist_params['id']    = $playlist_id;
						$ret = Es::index($playlist_params);	

						$video_page = 1;
						$video_index = 0;

						do{
							$videos_response = $client->get("http://vimeo.com/api/v2/{$playlist_id}/videos.json?page={$video_page}");
							$videos = json_decode($videos_response->getBody(), true);

							if(!empty($videos)){


								foreach($videos as $video){

									if($video['embed_privacy'] == 'anywhere'){
										$video_id = $video['id'];

										$video_params['body']  = array(
											'id' => $video_id,
											'video_id' => $video_id,
											'user_id' => $user_id,
											'website_id' => $website_id,
											'channel_id' => $channel_id,
											'playlist_id' => $playlist_id,
											'video_type' => 'vimeo',
											'position' => $video_index,
											'title' => $video['title'],
											'description' => strip_tags($video['description']),
											'thumbnail' => $video['thumbnail_small'],
											'published_at' => date('Y-m-d', strtotime($video['upload_date'])) 
										);

										$video_params['index'] = 'video-websites';
										$video_params['type']  = 'video';
										$video_params['id']    = $video_id;
										$ret = Es::index($video_params);	
									}

									$video_index += 1;
								}
							}

							$video_page += 1;
						}while(!empty($videos) && $video_page <= 3);

					}
				}else{

					$video_page = 1;
					$video_index = 0;
					
					do{
						$allvideos_response = $client->get("http://vimeo.com/api/v2/{$account_id}/all_videos.json?page={$video_page}");
						$videos = json_decode($allvideos_response->getBody(), true);
						if(!empty($videos)){


							foreach($videos as $video){

								if($video['embed_privacy'] == 'anywhere'){
									$video_id = $video['id'];

									$video_params['body']  = array(
										'id' => $video_id,
										'video_id' => $video_id,
										'user_id' => $user_id,
										'website_id' => $website_id,
										'channel_id' => $channel_id,
										'video_type' => 'vimeo',
										'position' => $video_index,
										'title' => $video['title'],
										'description' => strip_tags($video['description']),
										'thumbnail' => $video['thumbnail_small'],
										'published_at' => date('Y-m-d', strtotime($video['upload_date'])) 
									);

									$video_params['index'] = 'video-websites';
									$video_params['type']  = 'video';
									$video_params['id']    = $video_id;
									$ret = Es::index($video_params);	
								}

								$video_index += 1;

							}
						
						}

						$video_page += 1;
					}while(!empty($videos) && $video_page <= 3);
				}		

			}
			
			

			return Redirect::to('/websites/new')
				->with('message', array('type' => 'success', 'text' => 'You have successfully created a website!'));
		}
	}


	public function websites(){

		$user_id = Auth::user()->id;

		$websites = Website::where('user_id', '=', $user_id)->get();

		$page_data = array(
			'websites' => $websites,
			'website_count' => count($websites),
			'base_url' => Config::get('keys.base_url')
		);

		$this->layout->title = 'Websites';
		$this->layout->content = View::make('admin.websites', $page_data);
	}

	
	public function editWebsite($id){

		$user_id = Auth::user()->id;

		$website = Website::where('user_id', '=', $user_id)
			->where('id', '=', $id)
			->first();

		$playlistsearch_params = array(
			'index' => 'video-websites',
			'type' => 'playlist'
		);

		$playlistsearch_params['body']['query']['match']['website_id'] = $id;
		$playlistsearch_response = Es::search($playlistsearch_params);

		$playlists = (!empty($playlistsearch_response['hits']['hits'])) ? $playlistsearch_response['hits']['hits'] : array(); 

		
		$video_count = 0;
		$videosearch_response = array();


		if(empty($playlists)){

			$videosearch_params = array(
				'index' => 'video-websites',
				'type' => 'video'
			);

			$videosearch_params['body']['query']['filtered']['query']['match']['website_id'] = $id;
			$videosearch_response['body']['sort']['published_at']['order'] = 'desc';
			$videosearch_response = Es::search($videosearch_params);

			$videos = (!empty($videosearch_response['hits']['hits'])) ? $videosearch_response['hits']['hits'] : array(); 			
			$video_count = count($videos);
		}

		$page_data = array(
			'website' => $website,
			'playlists' => $playlistsearch_response,
			'playlist_count' => count($playlists),
			'videos' => $videosearch_response,
			'video_count' => $video_count
		);

		$this->layout->title = 'Edit Website';
		$this->layout->switch = true;
		$this->layout->edit_website = true;
		$this->layout->content = View::make('admin.edit_website', $page_data);
	}


	public function doEditWebsite($id){

		$user_id = Auth::user()->id;

		$rules = array(
			'title' => 'required',
			'type' => 'required',
			'account_id' => 'required'
		);

		$validator = Validator::make(Input::all(), $rules);

		if($validator->fails()){
			return Redirect::to('/websites/' . $id)
						->withErrors($validator)
						->withInput();
		}else{

			$title = Input::get('title');
			$tagline = Input::get('tagline');
			$about = Input::get('about');
			$type = Input::get('type');
			$account_id = Input::get('account_id');
			$domain_name = Input::get('domain_name');
			$is_public = 0;
			if(Input::has('public')){
				$is_public = 1;
			}

			$website = Website::where('user_id', '=', $user_id)
						->where('id', '=', $id)
						->first();
			$website->user_id = $user_id;
			$website->type = $type;
			$website->account_id = $account_id;
			$website->domain_name = $domain_name;
			$website->title = $title;
			$website->tagline = $tagline;
			$website->about = $about;
			$website->public = $is_public;
			$website->save();

			return Redirect::to('/websites/' . $id)
				->with('message', array('type' => 'success', 'text' => 'You have successfully updated the website!'));
		}

	}


	public function setFeaturedVideo(){

		$id = Input::get('id');
		$website_id = Input::get('website_id');
		$playlist_id = Input::get('playlist_id');
		$status = Input::get('status');

		$featured = 'featured';
		if(!empty($status)){
			$featured = '';
		}	

		$user_id = Auth::user()->id;

		//unset all currently featured videos
		$videosearch_params = array(
				'index' => 'video-websites',
				'type' => 'video'
			);

		if(!empty($playlist_id)){
			$videosearch_params['body']['query']['filtered']['query']['match']['playlist_id'] = $playlist_id;
		}

		$videosearch_params['body']['query']['filtered']['filter']['bool']['must'][]['term']['user_id'] = $user_id;
		$videosearch_params['body']['query']['filtered']['filter']['bool']['must'][]['term']['website_id'] = $website_id;
		$videosearch_params['body']['query']['filtered']['filter']['bool']['must'][]['term']['featured'] = 'featured';

		$videosearch_response = Es::search($videosearch_params);

		$update_params = array(
			'index' => 'video-websites',
			'type' => 'video'
		);
		
		
		foreach($videosearch_response['hits']['hits'] as $hit){
			
			$update_params['id'] = $hit['_id'];
			$source = $hit['_source'];
			$source['featured'] = '';
			$update_params['body']['doc'] = $source; 
			$res = Es::update($update_params);

		}
	

		$get_params = array(
			'index' => 'video-websites',
			'type' => 'video',
			'id' => $id
		);

		$video = Es::get($get_params);

		$source = $video['_source'];
		$source['featured'] = $featured;

		$update_params = array(
			'index' => 'video-websites',
			'type' => 'video',
			'id' => $id,
			'body' => array(
				'doc' => $source
			)
		);

		$response = Es::update($update_params);
		return $response;
	}


	public function logout(){

        Session::flush();
        Auth::logout();
        return Redirect::to("/login")
          ->with('message', array('type' => 'success', 'text' => 'You have successfully logged out'));

	}


}