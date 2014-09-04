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


			$youtube = new Youtube(array('key' => Config::get('keys.youtube')));
			$youtube_channel = $youtube->getChannelByName($account_id);

			$channel = new Channel;
			$channel->user_id = $user_id;
			$channel->website_id = $website_id;
			$channel->channel_id = $youtube_channel->id;
			$channel->title = $youtube_channel->snippet->title;
			$channel->description = $youtube_channel->snippet->description;
			$channel->thumbnail = $youtube_channel->snippet->thumbnails->high->url;
			$channel->save();
			$channel_id = $channel->id;


			$next_playlisttoken = '';

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
								'description' => $pl->snippet->description,
								'thumbnail' => $pl->snippet->thumbnails->high->url,
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
												'position' => $video->snippet->position,
												'title' => $video->snippet->title,
												'description' => $video->snippet->description,
												'thumbnail' => $video->snippet->thumbnails->high->url,
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


		$page_data = array(
			'website' => $website
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


	public function logout(){

        Session::flush();
        Auth::logout();
        return Redirect::to("/login")
          ->with('message', array('type' => 'success', 'text' => 'You have successfully logged out'));

	}


}