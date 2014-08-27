
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>{{ $title }}</title>

    <link href="{{ asset('assets/lib/bootstrap/bootstrap.css') }}" rel="stylesheet">   
    <link href="{{ asset('assets/css/jumbotron-narrow.css') }}" rel="stylesheet">

    @if(!empty($switch))
    <link rel="stylesheet" href="{{ asset('assets/lib/bootstrap-switch/bootstrap-switch.css') }}">
    @endif


    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>

    <div class="container">
		@include('partials.admin_header')

		@yield('content')

      <div class="footer">
        <p>&copy; Company 2014</p>
      </div>

    </div> <!-- /container -->
	<script src="{{ asset('assets/js/jquery.js') }}"></script>
	<script src="{{ asset('assets/lib/bootstrap/bootstrap.js') }}"></script>
  
  @if(!empty($switch))
  <script src="{{ asset('assets/lib/bootstrap-switch/bootstrap-switch.js') }}"></script>
  <script src="{{ asset('assets/js/switch.js') }}"></script>
  @endif

  </body>
</html>
