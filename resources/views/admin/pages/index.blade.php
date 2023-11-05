@extends('adminlte::page')

@section('title', 'Импорт пользователей')

@section('content_header')
    <h1>Страницы</h1>
@stop
@section('plugins.Datatables', true)

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <table class="table table-bordered" id="users-table">
                <thead>
                <tr>
                    <th>Id</th>
                    <th style="width: 100%;">Url</th>
                    <th>Дата создания</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
    <hr>
@endsection
@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
@stop
@section('js')
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script>
        $(document).ready(function () {
            $.fn.dataTable.ext.errMode = 'none';

            let table = $('#users-table').DataTable({
                buttons: [
                    'pageLength'
                ],
                processing: false,
                serverSide: false,
                ajax: '/admin/pages/data',
                bFilter: true,
                responsive: true,
                order: [[0, "asc"]],
                autoWidth: true,
                lengthMenu: [
                    [10, 100, 500, -1],
                    ['10 строк', '100 строк', '500 строк', 'Все']
                ],
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'url', name: 'url'},
                    {data: 'created_at', name: 'created_at'},
                ],
                dom: 'Bfrtip',
            });
            table.on('click', 'tbody tr', function () {
                let data = table.row(this).data();
                console.log(data);
                window.location.href = '/admin/pages/' + data.id;
            })

            // $('#operators-table').DataTable({
            //     buttons: [
            //         'pageLength'
            //     ],
            //     processing: false,
            //     serverSide: false,
            //     ajax: '/admin/csv-operators/data',
            //     bFilter: true,
            //     responsive: true,
            //     order: [[0, "asc"]],
            //     autoWidth: true,
            //     lengthMenu: [
            //         [10, 100, 500, -1],
            //         ['10 строк', '100 строк', '500 строк', 'Все']
            //     ],
            //     columns: [
            //         {data: 'user_id', name: 'user_id'},
            //         {data: 'email', name: 'email'},
            //         {data: 'name', name: 'name'},
            //         {data: 'created_at', name: 'created_at'},
            //     ],
            //     dom: 'Bfrtip',
            // });
            //
            // $('#administrators-table').DataTable({
            //     buttons: [
            //         'pageLength'
            //     ],
            //     processing: false,
            //     serverSide: false,
            //     ajax: '/admin/csv-administrators/data',
            //     bFilter: true,
            //     responsive: true,
            //     order: [[0, "asc"]],
            //     autoWidth: true,
            //     lengthMenu: [
            //         [10, 100, 500, -1],
            //         ['10 строк', '100 строк', '500 строк', 'Все']
            //     ],
            //     columns: [
            //         {data: 'user_id', name: 'user_id'},
            //         {data: 'email', name: 'email'},
            //         {data: 'name', name: 'name'},
            //         {data: 'created_at', name: 'created_at'},
            //     ],
            //     dom: 'Bfrtip',
            // });
        });
    </script>
@stop
