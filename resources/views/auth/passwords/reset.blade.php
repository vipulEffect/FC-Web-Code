@extends('layouts.app')

@section('content')
<div class="loginContainer">
	<div class="loginCenter">
		<div class="loginBox">
			<div class="loginLogo"><a href=""><img src="{{ asset('images/login-logo.png') }}" alt=""></a></div>
			
			<form method="POST" action="{{ route('password.update') }}">
				@csrf
				<div class="loginCover">
					<div class="forhead">
						<h2>Reset Password</h2>
					</div>
					<div class="loginRow">
						<input type="hidden" name="token" value="{{ $token }}">
						<input id="email" type="email" placeholder="Email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>
						
						@error('email')
							<span class="invalid-feedback" style="color:#fff;" role="alert">
								<strong>{{ $message }}</strong>
							</span>
						@enderror
					</div>
					
					<div class="loginRow">
						<!--<label for="password" class="col-md-4 col-form-label text-md-right" style="color:#fff;">{{ __('Password') }}</label>-->

						<div class="col-md-6">
							<input id="password" type="password" placeholder="Password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

							@error('password')
								<span class="invalid-feedback" style="color:#fff;" role="alert">
									<strong>{{ $message }}</strong>
								</span>
							@enderror
						</div>
					</div>

					<div class="loginRow">
						<!--<label for="password-confirm" class="col-md-4 col-form-label text-md-right" style="color:#fff;">{{ __('Confirm Password') }}</label>-->

						<div class="col-md-6">
							<input id="password-confirm" type="password" placeholder="Confirm Password" class="form-control" name="password_confirmation" required autocomplete="new-password">
						</div>
					</div>

					<div class="submitRow">
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