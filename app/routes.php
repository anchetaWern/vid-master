<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::filter('nocache', function($route, $request, $response)
{
  $response->header('Expires', 'Tue, 1 Jan 1980 00:00:00 GMT');
  $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0,    pre-check=0');
  $response->header('Pragma', 'no-cache');
  return $response;
});

Route::pattern('long_id', '[a-z0-9\-\+]{8,160}');
Route::pattern('id', '[0-9]+');

Route::get('/', 'HomeController@index');
Route::get('/signup', 'HomeController@signup');
Route::post('/signup', 'HomeController@doSignup');

Route::get('/login', 'HomeController@login');
Route::post('/login', 'HomeController@doLogin');

Route::post('/playlist/videos', 'HomeController@playlistVideos');

Route::get('/logout', 'AdminController@logout');

Route::group(array('before' => 'auth', 'after' => 'nocache'), function()
{
	Route::get('/admin', 'AdminController@index');

	Route::get('/websites/new', 'AdminController@newWebsite');
	Route::post('/websites', 'AdminController@createWebsite');

	Route::get('/websites', 'AdminController@websites');
	Route::get('/websites/{id}', 'AdminController@editWebsite');
	Route::post('/websites/{id}', 'AdminController@doEditWebsite');

	Route::post('/video/featured', 'AdminController@setFeaturedVideo');
});

Route::get('/get/featured', function(){
	$videosearch_params = array(
			'index' => 'video-websites',
			'type' => 'video'
		);

	$videosearch_params['body']['query']['filtered']['query']['match']['playlist_id'] = 'PLEsfXFp6DpzRFiPsPIDX1S5CYeJV7c9N9';
	$videosearch_params['body']['query']['filtered']['filter']['bool']['must'][]['term']['user_id'] = 1;
	$videosearch_params['body']['query']['filtered']['filter']['bool']['must'][]['term']['featured'] = 'featured';

	$videosearch_response = Es::search($videosearch_params);

	$update_params = array(
		'index' => 'video-websites',
		'type' => 'video'
	);
	
	/*
	foreach($videosearch_response['hits']['hits'] as $hit){
		
		$update_params['id'] = $hit['_id'];
		$source = $hit['_source'];
		$source['featured'] = '';
		$update_params['body']['doc'] = $source; 
		$res = Es::update($update_params);
		print_r($res);

	}
	*/
	
	return $videosearch_response;

});

Route::get('/tester', function(){
	$youtube = new Youtube(array('key' => 'AIzaSyC7btBy7Z5HUb31W1Kca0vOiTSRMu_yGRA'));
	//$video = $youtube->getVideoInfo('2NWeucMKrLI');
	
	$channel = $youtube->getChannelByName('CodingEntrepreneurs');

	//UCJbPGzawDH1njbqV-D5HqKw

	
	echo "<pre>";
	print_r($channel);
	echo "</pre>";

	echo "channel id: " . $channel->id;
	echo "<br>channel title: " . $channel->snippet->title;
	echo "<br>description: " . $channel->snippet->description;
	echo "<br>photo: " . $channel->snippet->thumbnails->high->url;
	
	echo "<hr>";
	
	$playlists = $youtube->getPlaylistsByChannelId($channel->id, array('maxResults' => 5, 'pageToken' => ''));
	echo "<pre>";
	print_r($playlists);
	echo "</pre>";

	/*
	echo "<hr>playlist items: <br>";
	foreach($playlists as $pl){
		
		$playlist_id =  $pl->id;
		$playlist_items = $youtube->getPlaylistItemsByPlaylistId($playlist_id, array('maxResults' => 10));
		
		echo "<pre>";
		print_r($playlist_items);
		echo "</pre>";

	}
	*/

	/*works!
	$page_info = $youtube->page_info();
	echo "page info: ";
	echo "<pre>";
	print_r($page_info);
	echo "</pre>";
	if(is_array($page_info)){
		echo "all done";
	}else{
		echo "theres more<br>";
		$playlists = $youtube->getPlaylistsByChannelId($channel->id, array('maxResults' => 5, 'pageToken' => $page_info));
		echo "<pre>";
		print_r($playlists);
		echo "</pre>";

	}
	*/
	
	//echo $youtube->page_info['totalResults'] . "<br>";
	//echo $youtube->page_info['resultsPerPage'] . "<br>";
	/*
	$playlist_items = $youtube->getPlaylistItemsByPlaylistId('PL6gx4Cwl9DGAKIXv8Yr6nhGJ9Vlcjyymq');
	echo "<pre>";
	print_r($playlist_items);
	echo "</pre>";
	*/
});

Route::get('/mock', function(){

	$youtube = new Youtube(array('key' => Config::get('keys.youtube')));
	$youtube_channel = $youtube->getChannelByName('CodingEntrepreneurs');
	

	$next_playlisttoken = '';

	do{

		$playlists = $youtube->getPlaylistsByChannelId($youtube_channel->id, array('maxResults' => 50, 'pageToken' => $next_playlisttoken));
		$next_playlisttoken = $youtube->page_info;

		if(!empty($playlists)){			
			foreach($playlists as $pl){

				//only cached playlists that are public
				if($pl->status->privacyStatus == 'public'){					
					$playlist_id = $pl->id;
					$next_videostoken = '';

					do{
						$playlist_items = $youtube->getPlaylistItemsByPlaylistId(
							$playlist_id, array('maxResults' => 50, 'pageToken' => $next_videostoken)
						);

						$next_videostoken = $youtube->page_info;
						echo "next video token: ";
						print_r($next_videostoken);
						echo "<br>";

						if(!empty($playlist_items)){
							foreach($playlist_items as $video){

								echo "<pre>";
								print_r($video);
								echo "</pre>";

							}
						}
					
					}while(is_string($next_videostoken));


				}
			}
		}
	}while(is_string($next_playlisttoken));

});


Route::get('/delete/index', function(){
	$deleteParams['index'] = 'video-websites';
    $ret = Es::indices()->delete($deleteParams);
    return $ret;
});


Route::get('/dowhile', function(){

	$index = 0;
	do{
		$index += 1;
		$token = 'awesome ' . $index;
		if($index == 4){
			$token = array('name' => 'jet');
		}
	}while(is_string($token));

	print_r($token);

});

Route::get('/es/create/index', function(){
	$indexParams['index'] = 'video-websites';
	$indexParams['body']['settings']['number_of_shards'] = 2;
	$indexParams['body']['settings']['number_of_replicas'] = 0;
	$ret = Es::indices()->create($indexParams);
	return $ret;
});

Route::get('/es/get', function(){
	$getParams = array();
    $getParams['index'] = 'my_index';
    $getParams['type']  = 'awesome';
    $getParams['id']    = '1';
    $retDoc = Es::get($getParams);
    return $retDoc;
});

Route::get('/len', function(){
	return strlen('Coding for Entrepreneurs (CFE) Setup Playlist for Linux Ubuntu. Learn Django, Python, Twitter Bootstrap, and more. Technical Support? Questions or comments? Sign up for the basic course for free on Udemy: ');
});

Route::get('/es/search', function(){
    $searchParams['index'] = 'video-websites';
    $searchParams['type']  = 'video';
    $searchParams['body']['query']['match']['title'] = 'django';
    $queryResponse = Es::search($searchParams);
    return $queryResponse;
});

Route::get('/vimeo', function(){
	/*
	$app_id = Config::get('keys.vimeo.app_id');
	$app_secret = Config::get('keys.vimeo.app_secret');
	//$access_token = Config::get('keys.access_token');
	$access_token = 'c3f3efee6f38253e8e44a125aefbf39c'; //app only

	//$vimeo = new Vimeo\Vimeo($app_id, $app_secret, $access_token);
	$vimeo = new Vimeo\Vimeo($app_id, $app_secret);

	//$token_response = $vimeo->clientCredentials();
	$res = $vimeo->request('/api/v2/fronttrends/channels.json');
	return $res;
	*/

	$username = 'fronttrends';
	$client = new GuzzleHttp\Client();
	$res = $client->get("http://vimeo.com/api/v2/{$username}/channels.json");
	$data = json_decode($res->getBody(), true); 
	print_r($data);
});


Route::get('/vimeo/redirect', function(){

	$app_id = '1149255775453ab09e7d68c379d5feafa2486191';
	$app_secret = '360648f00a1e386b02511a7b985c509285adb343';

	$vimeo = new Vimeo\Vimeo($app_id, $app_secret);
	$redirect_url = 'http://localhost:7778/vimeo/connect';
	$connect_url = $vimeo->buildAuthorizationEndpoint($redirect_url);
	return Redirect::to($connect_url);
});

Route::get('/vimeo/connect', function(){
	return Input::get();
	// vimeo access token:
	// 9f58506a9862177aea600ea3700fdb2519129a40
});

Route::get('/vimeo/advanced', function(){

	$app_id = '1149255775453ab09e7d68c379d5feafa2486191';
	$app_secret = '360648f00a1e386b02511a7b985c509285adb343';

	$access_token = '9f58506a9862177aea600ea3700fdb2519129a40';

	$vimeo = new Vimeo\Vimeo($app_id, $app_secret);
	$vimeo->setToken($access_token);

	$vimeo->request('/me/videos', array('type' => 'POST', 'redirect_url' => $redirect_target), 'POST');
});

Route::get('/get/vimeo', function(){

	$videosearch_params = array(
			'index' => 'video-websites',
			'type' => 'video'
		);

	$videosearch_params['body']['query']['filtered']['query']['match']['website_id'] = 4;
	$videosearch_params['body']['query']['filtered']['filter']['bool']['must'][]['term']['user_id'] = 1;


	$videosearch_response = Es::search($videosearch_params);
	return $videosearch_response;
});

Route::get('/vimeo/cache', function(){

	$user_id = 1;
	$video_page = 2;
	$website_id = 4;
	$channel_id = 4;

	$video_index = 21;

	$account_id = 'sayanee';

	$client = new GuzzleHttp\Client();

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
					print_r($ret);
				}

				$video_index += 1;

			}
		
		}

		$video_page += 1;
	}while(!empty($videos) && $video_page <= 3);

});