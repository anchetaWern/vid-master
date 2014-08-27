<?php

class HomeController extends BaseController {

	protected $layout = 'layouts.default';

	public function index(){

		$this->layout->title = 'Video Website Maker';
		$this->layout->content = View::make('index');
	}

	public function signup(){

		$this->layout->title = 'Sign Up';
		$this->layout->content = View::make('register');
	}

	public function doSignup(){

		$rules = array(
			'username' => 'required',
			'email' => 'email|required',
			'password' => 'min:8|required'
		);

		$validator = Validator::make(Input::all(), $rules);

		if($validator->fails()){
			return Redirect::to('/signup')
				->withErrors($validator)
				->withInput(Input::except('password'));
		}else{

			$username = Input::get('username');
			$email = Input::get('email');
			$password = Input::get('password');
		
			$user = new User;
			$user->username = $username;
			$user->email = $email;
			$user->password = Hash::make($password);
			$user->save();

			return Redirect::to('/login')
				->with('message', array('type' => 'success', 'text' => 'You have successfully created your account! You can now login.'));
		}

	}

	public function login(){

		$this->layout->title = '';
		$this->layout->content = View::make('login');
	}

	public function doLogin(){

		$rules = array(
			'email' => 'required',
			'password' => 'required'
		);

		$validator = Validator::make(Input::all(), $rules);
		if($validator->fails()){
			return Redirect::to('/login')
				->withErrors($validator)
				->withInput(Input::except('password'));
		}else{

			$user_data = array(
				'email' => Input::get('email'),
				'password' => Input::get('password')
			);
		
			if(Auth::attempt($user_data)){
               return Redirect::to('/admin');
			}else{
				return Redirect::to('/login')
					->with('message', array('type' => 'danger', 'text' => 'Incorrect email or password'));
			}

		}

	}
}
