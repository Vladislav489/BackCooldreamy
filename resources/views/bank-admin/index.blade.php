@extends('bank-admin.app')

@section('content')

    <div class="pricing-header px-3 py-3 pt-md-5 pb-md-4 mx-auto text-center">
        <h1 class="display-4">Checking Accounts</h1>
        <p class="lead">CoolDreamy Girl Profiles</p>
        @if($errors->any())
            <div class="text-danger">{{$errors->first()}}</div>
        @endif
    </div>

    <div class="container">
        <div class="card">
            <div class="card-header" style="display: flex;justify-content: space-between;">
                <h3 class="card-title">Latest Online Ankets</h3>
                <div class="card-tools">
                    <form action="{{route('bank-admin.index')}}">
                        @csrf
                        <div class="input-group input-group-sm" >
                            <input type="text" style="width: 200px;margin-right: 10px;" name="search" value="{{request()->get('search')}}" class="form-control float-right" placeholder="Search">
                            <div class="ml-2 input-group-append">
                                <button type="submit" class="btn btn-primary">
                                    Search
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card-body table-responsive">
                <table class="table table table-hover text-nowrap">
                    <thead>
                    <tr>
                        <th style="width: 10px">#</th>
                        <th style="width: 75px">Avatar</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Age</th>
                        <th>Registration Date</th>
                        <th>Last Online</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>{{$user->id}}</td>
                            <td style="display: flex;justify-content: center"><img src="{{$user->user_avatar_url}}" height="60" width="60" style="object-fit: cover; border-radius: 50%" alt=""></td>
                            <td><a href="{{route('bank-admin.show', ['id' => $user->id])}}">{{$user->name}}</a></td>
                            <td>{{$user->email}}</td>
                            <td>{{$user->age}}</td>
                            <td>{{$user->created_at}}</td>
                            <td>{{$user->updated_at}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card-footer clearfix">
                {!! $users->links() !!}
            </div>
        </div>
    </div>

@endsection
