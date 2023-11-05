@extends('adminlte::page')

@section('title', 'Импорт пользователей')

@section('content_header')
    <h1>App</h1>
@stop
@section('plugins.Datatables', true)

@section('content')
    <div class="container">
        <div class="row">
                @foreach($settings as $setting)
                    <div class="col-6">
                        <form action="{{route('admin.change_settings')}}" method="post">
                            @csrf
                            <div class="form-group">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="name" value="{{$setting->name}}">
                                    <input class="form-check-input" @if($setting->value) checked @endif type="checkbox" name="value" id="flexSwitchCheckDefault1">
                                    <label class="form-check-label" for="flexSwitchCheckDefault">{{str_replace("checker","App",$setting->name)}}</label>
                                </div>
                            </div>
                            <button class="btn btn-primary" type="submit">Изменить</button>
                        </form>
                    </div>
                @endforeach

                @foreach($settingGeos as $settingGeo)
                    <div class="col-6">
                        <form action="{{route('admin.change_settings')}}" method="post">
                            @csrf
                            <div class="form-group">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="name" value="{{$settingGeo->name}}">
                                    <input class="form-check-input" @if($settingGeo->value) checked @endif type="checkbox" name="value" id="flexSwitchCheckDefault">
                                    <label class="form-check-label" for="flexSwitchCheckDefault">{{$settingGeo->name}}s</label>
                                </div>
                            </div>
                            <button class="btn btn-primary" type="submit">Изменить</button>
                        </form>
                    </div>
                @endforeach
        </div>
    </div>
@endsection
