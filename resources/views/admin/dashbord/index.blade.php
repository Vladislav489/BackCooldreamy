@extends('adminlte::page')

@section('title', 'Dashbord')

@section('content_header')
    <h1>Dashbord</h1>
@stop
@section('plugins.Datatables', true)
@section('content')
    <form method="POST" action="{{route('admin.dashbord.export.user.statistic')}}" id="search_form">
        @csrf
        <div class="row mb-lg-1">
            <div class="col-md-2">
                <div id="date-from" class="input-append date"   data-date-format="yyyy-mm-dd">
                    <label style="margin-right:15px">Дата с:</label><input name="date_registration_from" class="span2" size="16" type="text" value="">
                    <span class="add-on"><i class="icon-th"></i></span>
                </div>
            </div>
            <div class="col-md-2">
                <div id="date-to" class="input-append date"    data-date-format="yyyy-mm-dd">
                    <label style="margin-right:15px">Дата по:</label><input name="date_registration_to" class="span2" size="16" type="text" value="">
                    <span class="add-on"><i class="icon-th"></i></span>
                </div>
            </div>
            <div class="col-md-2">
                <label style="margin-right:15px">Пол:</label>
                <select style="width: 150px;height:30px"  name="gender">
                    <option value="">Все</option>
                    <option value="male">Мужчины</option>
                    <option value="female">Жещины</option>
                </select>
            </div>
            <div class="col-md-2">
                <label style="margin-right:15px">Страна:</label>
                <select  style="width: 150px;height:30px" name="country">
                    <option value="">Все</option>
                    @foreach($country as $item)
                        <option value="{{$item['title']}}">{{$item['title']}}</option>
                    @endforeach
                </select>
            </div>
            <div  class="col-md-2">
                <label style="margin-right:15px">Город:</label>
                <select style="width:150px;height:30px" name="state">
                    <option value="">Все</option>
                    @foreach($state as $item)
                        <option value="{{$item['title']}}">{{$item['title']}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 " >
                <button class="btn btn-success" id="submitFilter" type="button">Применить</button>
            </div>
            <div class="col-md-1 " >
                <button type="submit" class="btn btn-success" type="button">Экспорт</button>
            </div>
        </div>
        <form>
            <div class="row">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <div class="info-box-content">
                            <span class="info-box-text">Количеставо пользователей</span>
                            <span id="count_user" class="info-box-number">{{$count_user}}</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <div class="info-box-content">
                            <span class="info-box-text">Количеставо входов пользователей</span>
                            <span id="count_session" class="info-box-number">{{$count_session}}</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <div class="info-box-content">
                            <span class="info-box-text">Количеставо переходов по сайту</span>
                            <span id ="count_link" class="info-box-number">{{$count_link}}</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <div class="info-box-content">
                            <span class="info-box-text">Средне количество сообщени от мужчин</span>
                            <span id="count_message_male" class="info-box-number">{{$count_message_male}}</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <div class="info-box-content">
                            <span class="info-box-text">Общиее количество денг внесеное на сайт </span>
                            <span id="pay_onsite" class="info-box-number">${{$pay_onsite}}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <table class="table table-bordered" id="users-table">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Почта</th>
                            <th>Имя</th>
                            <th>Sub_id</th>
                            <th>App</th>
                            <th>Страна</th>
                            <th>Город</th>
                            <th>Возраст</th>
                            <th>Дата создания</th>
                            <th>Количество лайков</th>
                            <th>Количесвот включений</th>
                            <th>Количесвот переходов</th>
                            <th>Просмотрел анкет</th>
                            <th>Отправил сообщений</th>
                            <th>Потратил бесп. кредитов</th>
                            <th>Потратил реал. кредитов</th>
                            <th>Внес денег на сайт</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
            @endsection
            @section('css')
                <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css"/>
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css"/>
            @stop
            @section('js')
                <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js"></script>
                <script>
                    function loadTable(dataSend = {}){
                        $('#users-table').DataTable().destroy();
                        $('#users-table').DataTable({
                            buttons: [
                                'pageLength'
                            ],
                            processing: false,
                            serverSide: false,
                            ajax: {
                                url:'{{route('admin.dashbord.user.list')}}',
                                data:dataSend
                            },
                            bFilter: true,
                            responsive: true,
                            order: [[6, "desc"]],
                            autoWidth: true,
                            lengthMenu: [
                                [10, 100, 500, -1],
                                ['10 строк', '100 строк', '500 строк', 'Все']
                            ],
                            columns: [
                                {data: 'id', name: 'id'},
                                {data: 'email', name: 'email'},
                                {data: 'name', name: 'name'},
                                {data: 'subid', name: 'subid'},
                                {data: 'app_name', name: 'app_name'},
                                {data: 'country', name: 'country'},
                                {data: 'state', name: 'state'},
                                {data: 'age', name: 'age'},
                                {data: 'created_at', name: 'created_at'},
                                {data: 'like_', name: 'like_'},
                                {data: 'coming', name: 'coming'},
                                {data: 'link', name: 'link'},
                                {data: 'view', name: 'view'},
                                {data: 'send_message', name: 'send_message'},
                                {data: 'credits', name: 'credits'},
                                {data: 'real_credits', name: 'real_credits'},
                                {data: 'pay', name: 'pay'},
                            ],
                            dom: 'Bfrtip',
                        });
                    }

                    function getParamsFilte(){
                        var Data ={};
                        $("#search_form").find("input,select").each(function (index,obj) {
                            Data[$(obj).attr('name')] =  $(obj).val();
                        });
                        return Data;
                    }

                    function changeMianMetrik(data){
                        $.ajax({
                            url: '{{route('admin.dashbord.user.statistic')}}',
                            type: 'post',
                            async: true,
                            data: data,
                            dataType: "json",
                            error: function (error) {
                                console.log('error; ' + eval(error))
                            },
                            success: function (data) {
                                for(var key in data){
                                    $("#"+key).text(data[key]);
                                }
                            }
                        });
                    }

                    $(document).ready(function () {
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': ($('meta[name="csrf-token"]').attr('content') == undefined) ? this.token : $('meta[name="csrf-token"]').attr('content')
                            },
                        });
                        $('#date-from').datepicker();
                        $('#date-to').datepicker();
                        $("#submitFilter").click(function(){
                            changeMianMetrik(getParamsFilte())
                            loadTable(getParamsFilte())
                        })
                        $.fn.dataTable.ext.errMode = 'none';
                        loadTable();
                    });
                </script>
@stop
