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