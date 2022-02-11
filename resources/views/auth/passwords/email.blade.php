@extends('layouts.app')

@section('content')

<div class="loginContainer">
	<div class="loginCenter">
		<div class="loginBox">
			<div class="loginLogo"><a href=""><img src="{{ asset('images/login-logo.png') }}" alt=""></a></div>
			
			<form method="POST" action="{{ route('password.email') }}">
				@csrf
				<div class="loginCover">
					<div class="forhead">
						<h2>Reset Password</h2>
						<p>If you've lost your password or wish to reset it, enter your email and reset password.</p>
					</div>
					<div class="loginRow">
						<input id="email" type="email" placeholder="Email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
					</div>

					<div class="submitRow">
						@error('email')
							<span class="invalid-feedback" style="color:#fff;" role="alert">
								<strong>{{ $message }}</strong>
							</span>
						@enderror
						
						@if (session('status'))
							<span class="invalid-feedback" style="color:#fff;" role="alert">
								{{ session('status') }}
							</span>
						@endif
						
						<button type="submit" class="btn btn-primary button">Reset Password</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
		
@endsection