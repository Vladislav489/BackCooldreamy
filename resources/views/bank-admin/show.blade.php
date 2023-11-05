@extends('bank-admin.app')

@section('content')

    <div class="pricing-header px-3 py-3 pt-md-5 pb-md-4 mx-auto text-center">
        <h1 class="display-4">Checking Account: {{$user->name}} #{{$user->id}}</h1>
        <p class="lead">{{ $user->email }}</p>
        @if($errors->any())
            <div class="text-danger">{{$errors->first()}}</div>
        @endif

        @if(isset($message))
            <div class="text-success">{{$message}}</div>
        @endif
    </div>

    <style>
        body{
            background-color:#f2f6fc;
            color:#69707a;
        }
        .img-account-profile {
            height: 10rem;
        }
        .rounded-circle {
            border-radius: 50% !important;
        }
        .card {
            box-shadow: 0 0.15rem 1.75rem 0 rgb(33 40 50 / 15%);
        }
        .card .card-header {
            font-weight: 500;
        }
        .card-header:first-child {
            border-radius: 0.35rem 0.35rem 0 0;
        }
        .card-header {
            padding: 1rem 1.35rem;
            margin-bottom: 0;
            background-color: rgba(33, 40, 50, 0.03);
            border-bottom: 1px solid rgba(33, 40, 50, 0.125);
        }
        .form-control, .dataTable-input {
            display: block;
            width: 100%;
            padding: 0.875rem 1.125rem;
            font-size: 0.875rem;
            font-weight: 400;
            line-height: 1;
            color: #69707a;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #c5ccd6;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            border-radius: 0.35rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .nav-borders .nav-link.active {
            color: #0061f2;
            border-bottom-color: #0061f2;
        }
        .nav-borders .nav-link {
            color: #69707a;
            border-bottom-width: 0.125rem;
            border-bottom-style: solid;
            border-bottom-color: transparent;
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
            padding-left: 0;
            padding-right: 0;
            margin-left: 1rem;
            margin-right: 1rem;
        }
    </style>

    <div class="container-xl px-4 mt-4">
        <!-- Account page navigation-->
        <nav class="nav nav-borders">
            <a class="nav-link active ms-0">Profile</a>
        </nav>
        <hr class="mt-0 mb-4">
        <div class="row">
            <div class="col-xl-4">
                <!-- Profile picture card-->
                <div class="card mb-4 mb-xl-0">
                    <div class="card-header">Profile Picture</div>
                    <div class="card-body text-center">
                        <!-- Profile picture image-->
                        <img class="img-account-profile rounded-circle mb-2" src="{{$user->user_avatar_url}}" alt="">
                        <!-- Profile picture help block-->
                        <div class="small font-italic text-muted mb-4">User info avatar</div>
                        <!-- Profile picture upload button-->
                        <a href="https://cool-date.netlify.app/en/users/{{$user->id}}" target="_blank" class="btn btn-primary" type="button">Show more</a>
                    </div>
                </div>
            </div>
            <div class="col-xl-8">
                <!-- Account details card-->
                <div class="card mb-4">
                    <div class="card-header">Account Details</div>
                    <div class="card-body">
                        <form>
                            <!-- Form Group (username)-->
                            <div class="mb-3">
                                <label class="small mb-1" for="inputUsername">Username (how your name will appear to other users on the site)</label>
                                <input class="form-control" id="inputUsername" type="text" placeholder="Enter your username" value="{{$user->name}}" readonly>
                            </div>
                            <!-- Form Row-->
                            <div class="row gx-3 mb-3">
                                <!-- Form Group (first name)-->
                                <div class="col-md-6">
                                    <label class="small mb-1" for="inputFirstName">Is Premium</label>
                                    <input class="form-control" id="inputFirstName" type="text" placeholder="Enter your first name" value="{{\App\Services\Premium\PremuimService::getUserCurrentSubscription($user) ? 'Yes' : 'No'}}" readonly>
                                </div>
                                <!-- Form Group (last name)-->
                                <div class="col-md-6">
                                    <label class="small mb-1" for="inputLastName">Is Donate</label>
                                    <input class="form-control" id="inputLastName" type="text" placeholder="Enter your last name" value="{{$user->is_donate ? 'Yes' : 'No'}}">
                                </div>
                            </div>
                            <!-- Form Row        -->
                            <div class="row gx-3 mb-3">
                                <!-- Form Group (organization name)-->
                                <div class="col-md-6">
                                    <label class="small mb-1" for="inputOrgName">State</label>
                                    <input class="form-control" id="inputOrgName" type="text" placeholder="State" value="{{$user->state ?? 'Empty' }}">
                                </div>
                                <!-- Form Group (location)-->
                                <div class="col-md-6">
                                    <label class="small mb-1" for="inputLocation">Country</label>
                                    <input class="form-control" id="inputLocation" type="text" placeholder="Country" value="{{$user->country ?? 'Empty'}}">
                                </div>
                            </div>
                            <!-- Form Group (email address)-->
                            <div class="mb-3">
                                <label class="small mb-1" for="inputEmailAddress">Email address</label>
                                <input class="form-control" id="inputEmailAddress" type="email" placeholder="Enter your email address" value="{{ $user->email }}" readonly>
                            </div>
                            <!-- Form Row-->
                            <div class="row gx-3 mb-3">
                                <!-- Form Group (phone number)-->
                                <div class="col-md-6">
                                    <label class="small mb-1" for="inputPhone">About Self</label>
                                    <input class="form-control" id="inputPhone" type="text" placeholder="About Self" value="{{$user->about_self ?? 'Empty'}}">
                                </div>
                                <!-- Form Group (birthday)-->
                                <div class="col-md-6">
                                    <label class="small mb-1" for="inputBirthday">Birthday</label>
                                    <input class="form-control" id="inputBirthday" type="text" name="birthday" placeholder="Enter your birthday" value="{{$user->birthday}}" readonly>
                                </div>
                            </div>
                            <!-- Save changes button-->
                            <a href="https://cool-date.netlify.app/en/users/{{$user->id}}" target="_blank" class="btn btn-primary" type="button">Open In Site</a>
                        </form>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">Images</div>
                    <div class="card-body">

                        <div class="row">
                            @if($user->anketPhotos->count())
                            @foreach($user->anketPhotos->sortByDesc('created_at') ?? [] as $anketImage)
                            <div class="col-md-4">
                                <div class="card mb-4 box-shadow">
                                    <img class="card-img-top" src="{{$anketImage->url}}" style="object-fit: cover;height: 330px;">
                                    <div class="card-body">
                                        <p class="card-text">Uploaded: {{$anketImage->created_at}}</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="btn-group">
                                                <a href="{{$anketImage->url}}" target="_blank" type="button" class="btn btn-sm btn-outline-secondary">View</a>
                                                <form action="{{route('management.delete.photo', ['userId' => $user->id, 'id' => $anketImage->id])}}" method="post">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                                </form>
                                            </div>
                                            <small class="text-muted"></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            @else
                                <div class="col-md-4">
                                    Empty
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card " style="margin-bottom: 150px;">
                    <div class="card-header">Videos</div>
                    <div class="card-body">

                        <div class="row">
                            @if($user->anketVideos->count())
                            @foreach($user->anketVideos->sortByDesc('created_at') ?? [] as $anketImage)
                                <div class="col-md-4">
                                    <div class="card mb-4 box-shadow">
                                        <img class="card-img-top" style="object-fit: cover;height: 330px;" data-src="holder.js/100px225?theme=thumb&amp;bg=55595c&amp;fg=eceeef&amp;text=Thumbnail" alt="Thumbnail [100%x225]" style="height: 225px; width: 100%; display: block;" src="data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22348%22%20height%3D%22225%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%20348%20225%22%20preserveAspectRatio%3D%22none%22%3E%3Cdefs%3E%3Cstyle%20type%3D%22text%2Fcss%22%3E%23holder_1894eccee67%20text%20%7B%20fill%3A%23eceeef%3Bfont-weight%3Abold%3Bfont-family%3AArial%2C%20Helvetica%2C%20Open%20Sans%2C%20sans-serif%2C%20monospace%3Bfont-size%3A17pt%20%7D%20%3C%2Fstyle%3E%3C%2Fdefs%3E%3Cg%20id%3D%22holder_1894eccee67%22%3E%3Crect%20width%3D%22348%22%20height%3D%22225%22%20fill%3D%22%2355595c%22%3E%3C%2Frect%3E%3Cg%3E%3Ctext%20x%3D%22116.68333435058594%22%20y%3D%22120.3%22%3EThumbnail%3C%2Ftext%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E" data-holder-rendered="true">
                                        <div class="card-body">
                                            <p class="card-text">Uploaded: {{$anketImage->created_at}}</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="btn-group">
                                                    <a href="{{$anketImage->url}}" target="_blank" type="button" class="btn btn-sm btn-outline-secondary">View</a>
                                                    <form action="{{route('management.delete.video', ['userId' => $user->id, 'id' => $anketImage->id])}}" method="post">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                                    </form>
                                                </div>
                                                <small class="text-muted"></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                                @else
                                <div class="col-md-4">
                                    Empty
                                </div>
                                @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
