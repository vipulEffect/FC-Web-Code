@extends('layouts.app')

@section('content')

<div class="loginContainer">
	<div class="loginCenter">
		<div class="loginBox">
			<div class="loginLogo"><a href=""><img src="{{ asset('images/login-logo.png') }}" alt=""></a></div>
			<form method="POST" action="{{ route('login') }}">
			@csrf
			<div class="loginCover">
				<div class="loginRow">
					<!--<input type="text" placeholder="Username">
					<span class="tooltip warning">Please enter correct</span>-->

					<input id="email" type="email" placeholder="Email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

					@error('email')
						<span class="invalid-feedback" role="alert">
							<strong>{{ $message }}</strong>
						</span>
					@enderror
				</div>

				<div class="loginRow">
				   <!-- <input type="password" placeholder="Password">
					<span class="tooltip success">Please enter correct</span>-->

					<input id="password" type="password" placeholder="Password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

					@error('password')
						<span class="invalid-feedback" role="alert">
							<strong>{{ $message }}</strong>
						</span>
					@enderror
				</div>
				<div class="loginRow">
					<div class="loginRow50 form-check">
						<label for="rememberCheck" class="remember">
							<!--<input type="checkbox" id="rememberCheck">-->
							<input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
							<span>Remember me</span>
						</label>
					</div>
					<div class="loginRow50">
						<!--<a href="" class="forgotPassword">Forgot Password?</a>-->
						@if (Route::has('password.request'))
							<a class="btn btn-link forgotPassword" href="{{ route('password.request') }}">
								{{ __('Forgot Password?') }}
							</a>
						@endif
					</div>
				</div>
				<div class="submitRow">
					<!--<input class="button" type="submit" value="Login">-->
					<button type="submit" class="btn btn-primary button">
							{{ __('Login') }}
					</button>
				</div>
			</div>
			</form>
		</div>
	</div>
</div>
@endsection
