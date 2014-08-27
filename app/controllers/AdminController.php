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
			$website->account_id = $account_id;
			$website->domain_name = $domain_name;
			$website->title = $title;
			$website->tagline = $tagline;
			$website->about = $about;
			$website->public = $is_public;
			$website->save();

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