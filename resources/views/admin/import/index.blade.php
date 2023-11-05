@extends('adminlte::page')

@section('title', 'Импорт пользователей')

@section('content_header')
    <h1>Импорт пользователей</h1>
@stop
@section('plugins.Datatables', true)
    @section('content')
      <div style="display: flla">
        <form method="POST" action="{{ route('admin.csv_users.upload.without.image') }}" enctype="multipart/form-data"
              id="uploadForm">
            @csrf
            <h1>Ankets</h1>
            <div style="display: flex; align-items: center">
                <button style="margin-right: 20px" type="submit" class="btn btn-primary">Upload Fast Without Image</button>
                    <div>
                        <label for="csv_file">Upload CSV file:</label>
                        <input type="file" name="csv_file" accept=".csv, .txt">
                    </div>
            </div>
        </form>
        <form  id="form_imageImport" method="POST" action="{{ route('admin.csv_users.upload.with.image') }}" enctype="multipart/form-data"
                id="uploadForm">
              @csrf
              <h1>Ankets</h1>
              <div style="display: flex; align-items: center">
                  <button style="margin-right: 20px"  type="submit" class="btn btn-primary">Upload With Image</button>
                  <div>
                      <label for="csv_file">Upload CSV file:</label>
                      <input type="file" name="csv_file" accept=".csv, .txt">
                  </div>
              </div>
            <div  id="ProgressBar" style="display: none">
                <div  style="display: flex; align-items: center">
                    <div>Downloading progress:</div>
                    <div  style=" width:100%">
                        <progress id="userProgeres" style=" width:400px;height:50px"  id="users" value="0" max="100"> 0% </progress>
                    </div>
                </div>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.upload_countries') }}" enctype="multipart/form-data"
              id="uploadForm">
            @csrf
            <h1>Countries</h1>
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

        <form id="search_form" method="POST" action="{{ route('admin.export.csv.user') }}">
            @csrf
        <div style="display: flex; align-items: center">

            <div>ID from
                <input style="width:40px;height: 22px" name="id_from"> to
                <input style="width:40px;height: 22px" name="id_to" >
            </div>
            <div style="margin-left:10px">Gender
                        <select name="gender">
                            <option value="">Все</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                         </select>
            </div>
            <div style="margin-left:10px">Status
                <select name="is_real">
                    <option value="">Все</option>
                    <option value="0">Fake</option>
                    <option value="1">Not Fake</option>
                </select>
            </div>
            <div style="margin-left:10px">Country
                <select name="country">
                    <option value="">Все</option>
                    @foreach($country as $items_c)
                        <option value="{{$items_c['title']}}">{{$items_c['title']}}</option>
                    @endforeach
                </select>
            </div>
            <div style="margin-left:10px">State
                <select name="state">
                    <option value="">Все</option>
                    @foreach($state as $items_s)
                        <option value="{{$items_s['title']}}">{{$items_s['title']}}</option>
                    @endforeach
                </select>
            </div>
            <div style="margin-left:10px">Ava
                <select name="ava">
                    <option value="">Все</option>
                    <option value="1">Is Has</option>
                    <option value="0">Is Not Has</option>
                </select>
            </div>
            <div style="margin-left:10px">
                <button type="submit" class="btn btn-success">Export</button>
            </div>
        </div>
          </form>
        <div class="row">
            <div class="col-xs-12">
                <table class="table table-bordered" id="users-table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th>Name</th>
                        <th>State</th>
                        <th>Country</th>
                        <th>Birthday</th>
                        <th>About Self</th>
                        <th>Created at</th>
                        <th>Action</th>
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
           var dataUserList = null;

            if(dataUserList !== null) {
               // $("#ProgressBar").css("display", "block")
              //  $("#userProgeres").attr('max', dataUserList.length)
                var index = 0;
             ///   stepLoad();


                function stepLoad() {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': ($('meta[name="csrf-token"]').attr('content') == undefined) ? this.token : $('meta[name="csrf-token"]').attr('content')
                        },
                    });
                    var checkSpot = true;
                    var intervak = setInterval(function () {
                        if (checkSpot && index < dataUserList.length) {
                            checkSpot = false;
                            $.ajax({
                                url: '{{route('admin.import.csv.user.one.image')}}',
                                type: 'post',
                                async: true,
                                timeout: 10 * 60 * 1000,
                                data: {'userdata': dataUserList[index]},
                                dataType: "json",
                                error: function (error) {
                                    console.log('error; ' + eval(error))
                                },
                                success: function (data) {
                                    $("#userProgeres").attr('value', parseInt(index) + 1);
                                    index++
                                    checkSpot = true;
                                }
                            });
                        }
                    }, 1000);
                }
            }





            function edititem() {
                jQuery('#library tr').click(function(e) {
                    e.stopPropagation();
                    var $this = jQuery(this);
                    var trid = $this.closest('tr').attr('id');
                    var x = 0, y = 0; // default values
                    x = window.screenX +5;
                    y = window.screenY +275;
                    window.open('../DataTables/editlibrary.php?id='+trid,'editlibrary','toolbar=0,scrollbars=1,height=600,width=800,resizable=1,left='+x+',screenX='+x+',top='+y+',screenY='+y);
                });
            }
            function getParams(){
                var Data ={};
                $("#search_form").find("input,select").each(function (index,obj) {
                    Data[$(obj).attr('name')] =  $(obj).val();
                });

                return Data;
            }

            function redraw(data){
                $('#users-table').DataTable().destroy();
                $('#users-table').DataTable({
                    buttons: [
                        'pageLength'
                    ],
                    processing: false,
                    serverSide: false,
                    ajax: {
                        url:'/admin/csv-users/data',
                        data:{'filter':data}
                    },
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
                        {data: 'email', name: 'email'},
                        {data: 'name', name: 'name'},
                        {data: 'state', name: 'state'},
                        {data: 'country', name: 'country'},
                        {data: 'birthday', name: 'birthday'},
                        {data: 'about_self', name: 'about_self'},
                        {data: 'created_at', name: 'created_at'},
                        {name: 'action',
                            "defaultContent": `<button style='width:100px' onclick='edititem();'>Edit</button>
                                               <button style='width:100px'  onclick='deleteitem();'>Delete</button>`
                        },

                    ],
                    dom: 'Bfrtip',
                });
            }

            $(document).ready(function () {


                $("#search_form").find("input").each(function (index,obj){
                    $(obj).blur(function(){
                        redraw(getParams())
                    })
                })

                $("#search_form").find("select").each(function (index,obj){
                    $(obj).change(function(){
                        redraw(getParams())
                    })
                })

                $.fn.dataTable.ext.errMode = 'none';

                $('#users-table').DataTable({
                    buttons: [
                        'pageLength'
                    ],
                    processing: false,
                    serverSide: false,
                    ajax: {
                        url:'/admin/csv-users/data',
                        data:{'filter':getParams()}
                    },
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
                        {data: 'email', name: 'email'},
                        {data: 'name', name: 'name'},
                        {data: 'state', name: 'state'},
                        {data: 'country', name: 'country'},
                        {data: 'birthday', name: 'birthday'},
                        {data: 'about_self', name: 'about_self'},
                        {data: 'created_at', name: 'created_at'},
                        {name: 'action',
                            "defaultContent": `<button style='width:100px' onclick='edititem();'>Edit</button>
                                               <button style='width:100px'  onclick='deleteitem();'>Delete</button>`
                        },

                    ],
                    dom: 'Bfrtip',
                });



            });
        </script>
    @stop
