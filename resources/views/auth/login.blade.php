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

                 <form method="POST" action="/login">
                    @csrf

                    <!-- Email -->
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Enter email" required>
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                    </div>

                    <!-- Forgot Password -->
                    <div class="mb-3 text-end">
                        <a href="/forgot-password">Forgot Password?</a>
                    </div>

                    <!-- Login Button -->
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </div>

                    <!-- Register Redirect -->
                    <div class="text-center">
                        <a href="/register">Create Account</a>
                    </div>
                </form>

            </div>
        </div>

    </div>
</div>
```

</div>

</body>
</html>
