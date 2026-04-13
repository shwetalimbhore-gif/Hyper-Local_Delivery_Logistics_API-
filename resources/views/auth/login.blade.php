<!DOCTYPE html>

<html>
<head>
    <title>Login</title>


<!-- Bootstrap 4 CDN -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">


</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">


        <div class="card shadow">
            <div class="card-body">

                <h4 class="text-center mb-4">Login</h4>

                {{-- Error Message --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email -->
                    <div class="form-group">
                        <label>Email address</label>
                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            placeholder="Enter email"
                            value="{{ old('email') }}"
                            required
                        >
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label>Password</label>
                        <input
                            type="password"
                            name="password"
                            class="form-control"
                            placeholder="Enter password"
                            required
                        >
                    </div>

                    <!-- Remember Me -->
                    <div class="form-group form-check">
                        <input
                            type="checkbox"
                            name="remember"
                            class="form-check-input"
                            id="remember"
                        >
                        <label class="form-check-label" for="remember">
                            Remember Me
                        </label>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn btn-primary btn-block">
                        Login
                    </button>

                </form>

            </div>
        </div>

    </div>
</div>
```

</div>

</body>
</html>
