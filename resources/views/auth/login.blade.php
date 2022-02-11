@extends('layouts.app')

@section('content')

<div class="loginContainer">
	<div class="loginCenter">
		<div class="loginBox">
			<div class="loginLogo"><a href=""><img src="{{ asset('images/login-logo.png') }}" alt=""></a></div>
			<form method="POST" action="{{ route('webLoginPost') }}">
			@csrf
			
			<div class="loginCover">
				<div class="loginRow">
					<input id="email" type="email" placeholder="Email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
				</div>

				<div class="loginRow">
				  <input id="password" type="password" placeholder="Password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
				</div>
				
				<div class="loginRow">
					<div class="loginRow50 form-check">
						<label for="rememberCheck" class="remember">
							<input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') !== null ? 'checked' : '' }}>
							<span>Remember me</span>
						</label>
					</div>
					<div class="loginRow50">
						@if (Route::has('password.request'))
							<a class="btn btn-link forgotPassword" href="{{ route('password.request') }}">
								{{ __('Forgot Password?') }}
							</a>
						@endif
					</div>
				</div>
				<div class="submitRow">
					<!--@error('email')
					<span class="invalid-feedback" style="color:#fff;" role="alert">
						<strong>{{ $message }}</strong>
					</span>
					@enderror-->
					
					@if (session()->has('message'))
						<span class="invalid-feedback" style="color:#fff;" role="alert">
							<strong>{{ session('message') }}</strong>
						</span>
					@endif
					
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
