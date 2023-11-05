<aside class="main-sidebar {{ config('adminlte.classes_sidebar', 'sidebar-dark-primary elevation-4') }}">

    {{-- Sidebar brand logo --}}
    @if(config('adminlte.logo_img_xl'))
        @include('adminlte::partials.common.brand-logo-xl')
    @else
        @include('adminlte::partials.common.brand-logo-xs')
    @endif

    {{-- Sidebar menu --}}
    <div class="sidebar">
        <nav class="pt-2">
            <ul class="nav nav-pills nav-sidebar flex-column {{ config('adminlte.classes_sidebar_nav', '') }}"
                data-widget="treeview" role="menu"
                @if(config('adminlte.sidebar_nav_animation_speed') != 300)
                    data-animation-speed="{{ config('adminlte.sidebar_nav_animation_speed') }}"
                @endif
                @if(!config('adminlte.sidebar_nav_accordion'))
                    data-accordion="false"
                @endif>
                {{-- Configured sidebar links --}}
                {{--                @each('adminlte::partials.sidebar.menu-item', $adminlte->menu('sidebar'), 'item')--}}
                @if(Auth::user()->hasRole("admin"))
                    <li class="nav-item"><a class="nav-link  " href="/admin"><i class="fa fa-user"></i>
                            <p>Dashbord</p></a></li>
                    <li class="nav-item"><a class="nav-link  " href="/admin/import"><i class="fa fa-user"></i>
                            <p> Импорт пользователей</p></a></li>
                    <li class="nav-item"><a class="nav-link  " href="/admin/import-aces"><i class="fa fa-mail-bulk"></i>
                            <p> Импорт Айсов</p></a></li>
                    <li class="nav-item"><a class="nav-link  " href="/admin/import/operators/"><i class="fa fa-book"></i>
                            <p> Импорт Операторов и Админов для Анкет</p></a></li>
                    <li class="nav-item"><a class="nav-link  " href="/admin/pages/"><i class="fa fa-book"></i>
                            <p> Страницы</p></a></li>

                    <li class="nav-item"><a class="nav-link  " href="/admin/settings/"><i class="fa fa-book"></i>
                            <p>App</p></a></li>

                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-copy"></i>
                            <p>
                                Статистика
                                <i class="fas fa-angle-left right"></i>
                                <span class="badge badge-info right"></span>
                            </p>
                        </a>
                        <ul class="nav nav-treeview" style="display: none;">
                            <li class="nav-item">
                                <a href="/admin/message-count" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Message count</p>
                                </a>
                            </li>

                        </ul>
                    </li>

                @endif


            </ul>
        </nav>
    </div>

</aside>
