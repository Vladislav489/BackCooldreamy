@extends('adminlte::page')

@section('title', 'Импорт Айсов')

@section('content_header')
    <h1>Импорт Айсов</h1>
@stop
@section('plugins.Datatables', true)

    @section('content')
        <form method="POST" action="{{ route('admin.csv_aces.upload') }}" enctype="multipart/form-data"
              id="uploadForm">
            @csrf
            <h1>Aces</h1>
            <div class="row">
                <div class="col-xs-12">
                    <div class="form-group">
                        <label for="csv_file">Upload CSV file:</label>
                        <input type="file" name="csv_file" accept=".csv, .txt">
                    </div>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </div>
        </form>

        <div class="row">
            <div class="col-xs-12">
                <table class="table table-bordered" id="users-table">
                    <thead>
                    <tr>
                        <th>Message_type_id</th>
                        <th>Text</th>
                        <th>Target_id</th>
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

                $('#users-table').DataTable({
                    buttons: [
                        'pageLength'
                    ],
                    processing: false,
                    serverSide: false,
                    ajax: '/admin/csv-aces/data',
                    bFilter: true,
                    responsive: true,
                    order: [[0, "asc"]],
                    autoWidth: true,
                    lengthMenu: [
                        [10, 100, 500, -1],
                        ['10 строк', '100 строк', '500 строк', 'Все']
                    ],
                    columns: [
                        {data: 'message_type_id', name: 'message_type_id'},
                        {data: 'text', name: 'text'},
                        {data: 'target_id', name: 'target_id'},

                    ],
                    dom: 'Bfrtip',
                });
            });
        </script>
    @stop
