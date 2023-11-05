@extends('adminlte::page')

@section('title', 'Message count')
@section('content_header')
    <h1>Статистика</h1>
@stop
@section('plugins.Datatables', true)
@section('content')
    <h2>Всего сообщений: <span id="total">{{$total}}</span></h2>
    <h2>Пол: женщины <span id="genders">{{$female}} ({{$percent_female}}%), мужчины {{$male}} ({{$percent_male}}%)</span></h2>
    <h2>Пользователей с прикреплённым фото ("Аватар"):</h2>
    <p>- женщины <span id="avatars">{{$percent_female_ava}}%<br>
    - мужчины {{$percent_male_ava}}%</span></p>

    <table border="0" cellspacing="5" cellpadding="5">
        <tbody>
        <tr>
            <td>Начальная дата:</td>
            <td><input type="text" id="date_min" name="date_min"></td>
        </tr>
        <tr>
            <td>Конечная дата:</td>
            <td><input type="text" id="date_max" name="date_max"></td>
        </tr>
        </tbody>
    </table>
    <div class="row">
        <div class="col-xs-12">
            <table class="table table-bordered" id="users-table">
                <thead>
                <tr>
                    <th>Пользователь</th>
                    <th>количество сообщений</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>

    <hr>
    <h2>Возраст мужчин:</h2>

    <div class="row">
        <div class="col-xs-12">
            <table class="table table-bordered" id="ages-table">
                <thead>
                <tr>
                    <th>Возраст</th>
                    <th>Коичество пользователей</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>

    <hr>
    <h2>Пользователи и регистрация:</h2>

    <table border="0" cellspacing="5" cellpadding="5">
        <tbody>
        <tr>
            <td>Начальная дата:</td>
            <td><input type="text" id="reg_date_min" name="reg_date_min"></td>
        </tr>
        <tr>
            <td>Конечная дата:</td>
            <td><input type="text" id="reg_date_max" name="reg_date_max"></td>
        </tr>
        <tr>
            <td>Город:</td>
            <td><select type="text" id="cities" name="cities"></select></td>
        </tr>
        <tr>
            <td>Страна:</td>
            <td><select type="text" id="countries" name="countries"></select></td>
        </tr>
        </tbody>
    </table>
    <div class="row">
        <div class="col-xs-12">
            <table class="table table-bordered" id="users-registration-table">
                <thead>
                <tr>
                    <th>ФИО</th>
                    <th>email</th>
                    <th>День рождения</th>
                    <th>Пол</th>
                    <th>Страна</th>
                    <th>Город</th>
                    <th>Дата регистрации</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>

@endsection

@section('css')
    <link type="text/css" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css" rel="stylesheet">
    <link type="text/css" href="https://cdn.datatables.net/datetime/1.1.2/css/dataTables.dateTime.min.css" rel="stylesheet">
@stop
@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.2/moment.min.js"></script>

    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/datetime/1.1.2/js/dataTables.dateTime.min.js"></script>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>

    <script>
        $(document).ready(function () {

            minDate = new DateTime($('#date_min'), {
                format: 'DD.MM.YYYY'
            });
            maxDate = new DateTime($('#date_max'), {
                format: 'DD.MM.YYYY'
            });
            minDate = new DateTime($('#reg_date_min'), {
                format: 'DD.MM.YYYY'
            });
            maxDate = new DateTime($('#reg_date_max'), {
                format: 'DD.MM.YYYY'
            });

            var check_table = 0;

            $('#date_min, #date_max').on('change', function () {
                if ($('#date_min').val().length > 0 && $('#date_max').val().length > 0) {
                    // console.log(1);
                    // load_total();
                    check_table = load_dt($('#date_min').val(), $('#date_max').val(),check_table);
                    console.log(check_table);
                }
            });

            load_dt_age();

            $('#reg_date_min, #reg_date_max').on('change', function () {

                let reg_date_min = $('#reg_date_min').val();
                let reg_date_max = $('#reg_date_max').val();
                let check_city = $('select[name=cities] option:checked').val();
                let check_country  = $("select[name=countries] option:checked").val();

                if (reg_date_min.length > 0 && $(reg_date_max.length > 0)) {
                    console.log("Указаны даты");
                    check_table = load_dt_users(reg_date_min,reg_date_max,check_city,check_country,check_table);
                }
            });

            $("select[name=cities]").on("change",function(){
                let reg_date_min = $('#reg_date_min').val();
                let reg_date_max = $('#reg_date_max').val();
                let check_city = $(this).val();
                let check_country  = $("select[name=countries] option:checked").val();

                if (!(reg_date_min.length > 0 && $(reg_date_max.length > 0))) {
                    reg_date_min = reg_date_max = "";
                }

                console.log("Выбран город");
                if(check_city) check_table = load_dt_users(reg_date_min,reg_date_max,check_city,check_country,check_table);

            });

            $("select[name=countries]").on("change",function(){
                let reg_date_min = $('#reg_date_min').val();
                let reg_date_max = $('#reg_date_max').val();
                let check_city = $("select[name=cities] option:checked").val();
                let check_country  = $(this).val();

                if (!(reg_date_min.length > 0 && $(reg_date_max.length > 0))) {
                    reg_date_min = reg_date_max = "";
                }

                console.log("Выбрана страна");
                if(check_country) check_table = load_dt_users(reg_date_min,reg_date_max,check_city,check_country,check_table);

            });

            load_cities();
            load_countries();

        });

        function load_total() {
            $.ajax({
                type: 'GET',
                url: '/admin/message-count',
                success: function (data) {

                    $('#total').text('111')
                },
                error: function () {
                    //console.log(data);
                }
            });


        }

        function load_dt(date_min, date_max, check_table) {
            $.fn.dataTable.ext.errMode = 'none';

            let urlLink = '/admin/message-count-by-users?date_min=' + date_min + '&date_max=' + date_max;

            console.log(urlLink);

            if (!check_table) {
                console.log('Создаём таблицу');
                return $('#users-table').DataTable({
                    buttons: [
                        'copy', 'csv', 'excel', 'pdf', 'print', 'pageLength'
                    ],
                    stateSave: true,
                    processing: true,
                    serverSide: true,
                    ajax: urlLink,
                    bFilter: true,
                    responsive: true,
                    order: [[0, "asc"]],
                    autoWidth: true,
                    lengthMenu: [
                        [10, 100, 500, -1],
                        ['10 строк', '100 строк', '500 строк', 'Все']
                    ],
                    columns: [
                        {data: 'user', name: 'user'},
                        {data: 'count', name: 'count'},
                    ],
                    dom: 'Bfrtip',
                });
            } else {
                console.log('Обновляем таблицу');
                return $('#users-table').DataTable({
                    buttons: [
                        'copy', 'csv', 'excel', 'pdf', 'print', 'pageLength'
                    ],
                    stateSave: true,
                    processing: true,
                    serverSide: true,
                    ajax: urlLink,
                    bFilter: true,
                    responsive: true,
                    order: [[0, "asc"]],
                    autoWidth: true,
                    lengthMenu: [
                        [10, 100, 500, -1],
                        ['10 строк', '100 строк', '500 строк', 'Все']
                    ],
                    columns: [
                        {data: 'user', name: 'user'},
                        {data: 'count', name: 'count'},
                    ],
                    dom: 'Bfrtip',
                }).ajax.reload();
            }
        }

        function load_dt_users(date_min, date_max, check_city, check_table) {
            $.fn.dataTable.ext.errMode = 'none';

            let urlLink = '/admin/registration-users?';
            if(date_min && date_max) urlLink += 'date_min=' + date_min + '&date_max=' + date_max;
            if(check_city) urlLink += 'city=' + check_city;

            console.log(urlLink);

            /*$.ajax({
                type: 'GET',
                cache: false,
                dataType: 'json',
                url: urlLink,
                success: function(data) {
                    if(data.result == "yes") {
                        console.log(data);
                    } else {
                        console.log(data);
                    }
                }
            });*/

            if (!check_table) {
                console.log('Создаём таблицу');
                return $('#users-registration-table').DataTable({
                    buttons: [
                        'copy', 'csv', 'excel', 'pdf', 'print', 'pageLength'
                    ],
                    stateSave: true,
                    processing: true,
                    serverSide: true,
                    ajax: urlLink,
                    bFilter: true,
                    responsive: true,
                    order: [[0, "asc"]],
                    autoWidth: true,
                    lengthMenu: [
                        [10, 100, 500, -1],
                        ['10 строк', '100 строк', '500 строк', 'Все']
                    ],
                    columns: [
                        {data: 'name', name: 'name'},
                        {data: 'email', name: 'email'},
                        {data: 'birthday', name: 'birthday'},
                        {data: 'gender', name: 'gender'},
                        {data: 'country', name: 'country'},
                        {data: 'state', name: 'state'},
                        {data: 'created_at', name: 'created_at'},
                    ],
                    dom: 'Bfrtip',
                });
            } else {
                console.log('Обновляем таблицу');
                return $('#users-registration-table').DataTable({
                    buttons: [
                        'copy', 'csv', 'excel', 'pdf', 'print', 'pageLength'
                    ],
                    stateSave: true,
                    processing: true,
                    serverSide: true,
                    ajax: urlLink,
                    bFilter: true,
                    responsive: true,
                    order: [[0, "asc"]],
                    autoWidth: true,
                    lengthMenu: [
                        [10, 100, 500, -1],
                        ['10 строк', '100 строк', '500 строк', 'Все']
                    ],
                    columns: [
                        {data: 'name', name: 'name'},
                        {data: 'email', name: 'email'},
                        {data: 'birthday', name: 'birthday'},
                        {data: 'gender', name: 'gender'},
                        {data: 'country', name: 'country'},
                        {data: 'state', name: 'state'},
                        {data: 'created_at', name: 'created_at'},
                    ],
                    dom: 'Bfrtip',
                }).ajax.reload();
            }
        }

        function load_dt_age() {
            $.fn.dataTable.ext.errMode = 'none';

            let urlLink = '/admin/age-count-by-users';

            console.log('Создаём таблицу');
            return $('#ages-table').DataTable({
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print', 'pageLength'
                ],
                stateSave: true,
                processing: true,
                serverSide: true,
                ajax: urlLink,
                bFilter: true,
                responsive: true,
                order: [[0, "asc"]],
                autoWidth: true,
                lengthMenu: [
                    [10, 100, 500, -1],
                    ['10 строк', '100 строк', '500 строк', 'Все']
                ],
                columns: [
                    {data: 'age', name: 'age'},
                    {data: 'count', name: 'count'},
                ],
                dom: 'Bfrtip',
            });
        }

        function load_cities() {

            let urlLink = '/admin/cities-list';

            console.log(urlLink);

            $.ajax({
                type: 'GET',
                cache: false,
                dataType: 'json',
                url: urlLink,
                success: function(data) {
                    var sel = $('#cities');
                    $(data).each(function() {
                        sel.append($("<option>").attr('value',this.state).text(this.state));
                    });
                    /*if(data.result == "yes") {
                        console.log(data);
                    } else {
                        console.log(data);
                    }*/
                }
            });
        }

        function load_countries() {

            let urlLink = '/admin/countries-list';

            console.log(urlLink);

            $.ajax({
                type: 'GET',
                cache: false,
                dataType: 'json',
                url: urlLink,
                success: function(data) {
                    var sel = $('#countries');
                    console.log(data);
                    $(data).each(function() {
                        sel.append($("<option>").attr('value',this.country).text(this.country));
                    });
                }
            });
        }

</script>
@stop
