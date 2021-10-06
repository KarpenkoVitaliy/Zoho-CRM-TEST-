<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" />
    <link href="https://cdn.datatables.net/1.10.23/css/jquery.dataTables.min.css" rel="stylesheet">    

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>  
    <script src="https://cdn.datatables.net/1.10.23/js/jquery.dataTables.min.js"></script>    
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js"></script>

	<title>@yield('title-block')</title>
	<link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="/css/app.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

	<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="{{ asset('js/bootbox.all.min.js') }}" defer></script>
	<script src="{{ asset('js/work.js') }}" defer></script>

</head>

<body>
	@include('layouts.header')
	<div id="page" style="display:none;">{{csrf_token()}}</div>
	<div class="container">
		@if (session()->has('success'))
			<div class="alert alert-success {{ Session::has('flash_message_important') ? 'alert-important' : '' }}">
				<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
				{{ session('success') }}
			</div>
		@endif


	</div>



<!-- 	
	@if(Request::is('/'))

	@endif
-->
	<div class="container mt-5">
				@yield('content')				
<!-- 		<div class="row">
			<div class="col-8">
				ААААААА
			</div>
			<div class="col-4">
				DDDDDDD
			</div>
		</div>
 -->	</div>

	@include('layouts.footer')

	<script>
		$('div.alert').not('.alert-important').delay(3000).slideUp(300);
	</script>
</body>
</html>