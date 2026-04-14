<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="{{ asset('css/styles.min.css') }}"/>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h3>Forgot Password</h3>
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <input type="email" name="email" class="form-control mb-2" placeholder="Enter email">
        <button class="btn btn-warning btn-block">Send Reset Link</button>
    </form>

</div>
</body>
