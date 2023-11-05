@extends('adminlte::page')

@section('title', 'Импорт пользователей')

@section('content_header')
    <h1>Импорт пользователей</h1>
@stop
@section('plugins.Datatables', true)

@section('content')
    <form method="post" action="{{route('admin.ankets.timezone')}}">
        @csrf
        <button class="btn btn-success" type="submit">Load Timezone</button>
    </form>

    <h2>CSV Operators|Admin|Ankects</h2>
    <form method="POST" action="{{ route('admin.csv_operator_admin_ankets.upload') }}" enctype="multipart/form-data"
          id="uploadForm">
        @csrf
        <div style="display: flex; align-items: center">
            <div style="width: auto">
                <button type="submit" class="btn btn-primary">Upload</button>
                <label for="csv_file">Upload CSV file:</label>
                <input type="file" name="csv_file" accept=".csv, .txt">
            </div>
        </div>
    </form>


    <h2>Operators</h2>
    <div class="row">
        <form method="POST" action="{{ route('admin.export.csv.cperator') }}">
            @csrf
            <div style="display: flex; align-items: center">
            <div style="width: auto">
                <button type="submit" class="btn btn-success">Export</button>
            </div>
            <div style="width: auto;margin-left:10px">
                <select name="operator">
                    <option value="">Все</option>
                    @foreach($listOperator as $operator__)
                        <option value="{{$operator__['operator_id']}}">{{$operator__['name']."-".$operator__['operator_id']}}</option>
                    @endforeach
                </select>
            </div>
            </div>
        </form>
    </div>


    <div class="row">
        <div class="col-xs-12">
            <table class="table table-bordered" id="operators-table">
                <thead>
                <tr>
                    <th>User_id</th>
                    <th>Operator_id</th>
                    <th>Operator_name</th>
                    <th>Admin_id</th>
                    <th>Created at</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection
@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
@stop
@section('js')
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script>
        $(document).ready(function () {
            $.fn.dataTable.ext.errMode = 'none';
            $('#operators-table').DataTable({
                buttons: [
                    'pageLength'
                ],
                processing: false,
                serverSide: false,
                ajax: '/admin/operators/data',
                bFilter: true,
                responsive: true,
                order: [[0, "asc"]],
                autoWidth: true,
                lengthMenu: [
                    [10, 100, 500, -1],
                    ['10 строк', '100 строк', '500 строк', 'Все']
                ],
                columns: [
                    {data: 'user_id', name: 'user_id'},
                    {data: 'operator_id', name: 'operator_id'},
                    {data: 'operator_name', name: 'operator_name'},
                    {data: 'admin_id', name: 'admin_id'},
                    {data: 'created_at', name: 'created_at'},
                ],
                dom: 'Bfrtip',
            });
        });
    </script>
@stop
