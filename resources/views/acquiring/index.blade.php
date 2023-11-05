@extends('acquiring.app')

@section('content')

    <div class="pricing-header px-3 py-3 pt-md-5 pb-md-4 mx-auto text-center">
        <h1 class="display-4">Checking Images</h1>
        <p class="lead">
            @if($type == 'Last')
                <b class="text-secondary w-50">{{$type}}</b>
            @else
            <b class="{{$type == 'Accepted' ? 'text-success' : 'text-danger'}}">{{$type}}</b>
            @endif
            Images presented here.
    </div>

    <div class="container">
        <div class="row">
            @if(count($images))
            @foreach($images as $image)
            <div class="col-md-4">
                <div class="card mb-4 box-shadow">
                    <img class="card-img-top" style="height: 220px; object-fit: cover" src="{{$image->url}}" data-holder-rendered="true">
                    <div class="card-body">
                        <p class="card-text">
                            User: {{$image->user->name}}
                        </p>
                        <p class="card-text">
                            Category: {{$image->category->title}}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="btn-group">
                                <form action="{{route('acquiring.block', ['image' => $image])}}" method="post">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Block</button>
                                </form>
                                <form action="{{route('acquiring.accept', ['image' => $image])}}" method="post">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success">Accept</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
            @else
                There is nothing here
            @endif

            <div class="card-footer">
                <div class="d-flex">
                    {!! $images->links() !!}
                </div>
            </div>
        </div>
    </div>

@endsection
