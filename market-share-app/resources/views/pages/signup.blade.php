@extends('layouts/main-template')

@section('link')
    <!-- ADD LINKS DISPLAYED ON HEADER NAV BAR -->
    <!-- Active session links -->
    @if(Auth::check())
        <a class = "sysoLink" href='/account'>Home</a>
        <a class = "sysoLink" href='/search'>Search</a>
        <a class = "sysoLink" href='/community'>Community</a>
        <a class = "sysoLink" id="logoutLink" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            {{ __('Logout') }}
        </a>
    <!-- No session links -->
    @else
        <a class = "sysoLink" href='/landing'>Home</a>
        <a class = "sysoLink" href='/signin'>Login</a>
        <a class = "sysoLink" href='/signup'>Sign up</a>
    @endif
    <!-- Generic links -->
    <a class = "sysoLink" href='/about'>About</a>
@endsection

@section('content')
    <!-- PAGE SPECIFIC CONTENT GOES HERE -->
    <div class = "sysoBox sysoBoxFlex">
        <div class = "sysoContent sysoContent100">
            <h1 class = "sysoHeader1 sysoCenterText" id = "rego">Sign Up</h1>
            <div id = "signup">
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div>
                        <label for="name">{{ __('Name') }}</label>
                        <div>
                            <input class = "sysoInput" id="name" type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" placeholder="Enter name" value="{{ old('name') }}" required autofocus>
                            @if ($errors->has('name'))
                                <span id="loginError" class="invalid-feedback">
                                    <strong>{{ $errors->first('name') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <label for="email">{{ __('E-Mail Address') }}</label>
                        <div>
                            <input class = "sysoInput" id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" placeholder="Enter email address" value="{{ old('email') }}" required>
                            @if ($errors->has('email'))
                                <span class="invalid-feedback">
                                    <strong>{{ $errors->first('email') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <label for="password">{{ __('Password') }}</label>
                        <div>
                            <input class = "sysoInput" id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" placeholder="Enter password" required>
                            @if ($errors->has('password'))
                                <span class="invalid-feedback">
                                    <strong>{{ $errors->first('password') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <label for="password-confirm">{{ __('Confirm Password') }}</label>
                        <div>
                            <input class = "sysoInput" id="password-confirm" type="password" class="form-control" name="password_confirmation" placeholder="Re-enter password" required>
                        </div>
                    </div>
                    <div>
                        <div>
                            <button class = "sysoButton" type="submit">
                                {{ __('Register') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- END OF CONTENT -->
@endsection