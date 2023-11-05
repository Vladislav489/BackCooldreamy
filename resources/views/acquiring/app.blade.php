<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Acquiring</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="d-flex justify-content-between flex-column flex-md-row align-items-center p-3 px-md-4 mb-3 bg-white border-bottom box-shadow">
    <div class="d-flex">
    <h5 class="my-0 mr-md-auto font-weight-normal">Acquiring</h5>

        <nav class="my-2 my-md-0 mr-md-3">
            <a class="p-2 text-dark" href="{{route('acquiring.index')}}">Home</a>
            <a class="p-2 text-dark" href="{{route('acquiring.blocked')}}">Blocked</a>
            <a class="p-2 text-dark" href="{{route('acquiring.accepted')}}">Accepted</a>
        </nav>
    </div>
    <form action="{{route('acquiring.logout')}}" method="post">
        @csrf
        <button class="btn btn-outline-primary">Sign out</button>
    </form>
</div>

@yield('content')
</body>
</html>
