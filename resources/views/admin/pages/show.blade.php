@extends('adminlte::page')

@section('title', 'Импорт пользователей')

@section('content_header')
    <h1>Страница {{$page->url}}</h1>
@stop
@section('plugins.Datatables', true)

@section('content')
    <form action="{{route('admin.pages.update', ['page' => $page->id])}}" method="post">
        @csrf
        <input readonly class="form-control" type="text" name="url" value="{{$page->url}}">
        <div class="form-group">
            <label for="">Ru</label>
            <textarea class="form-control" required id="summernote" name="text_ru">{{$page->text_ru}}</textarea>
        </div>

        <div class="form-group">
            <label for="">En</label>
            <textarea class="form-control" required id="summernote1" name="text_en">{{$page->text_en}}</textarea>
        </div>
        <button class="btn btn-primary" type="submit">Сохранить</button>
    </form>

    <script>
        $(document).ready(function() {
            $('#summernote').summernote();
        });
    </script>
@endsection
