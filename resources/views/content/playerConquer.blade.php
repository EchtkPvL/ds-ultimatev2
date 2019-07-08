@extends('layouts.temp')

@section('titel', ucfirst(__('Spieler')).': '.\App\Util\BasicFunctions::outputName($playerData->name))

@section('content')
    <div class="row justify-content-center">
        <!-- Titel für Tablet | PC -->
        <div class="p-lg-5 mx-auto my-1 text-center d-none d-lg-block">
            <h1 class="font-weight-normal">{{ $typeName.': '.\App\Util\BasicFunctions::decodeName($playerData->name) }}</h1>
        </div>
        <!-- ENDE Titel für Tablet | PC -->
        <!-- Titel für Mobile Geräte -->
        <div class="p-lg-5 mx-auto my-1 text-center d-lg-none truncate">
            <h1 class="font-weight-normal">
                {{ $typeName.': ' }}
            </h1>
            <h4>
                {{ \App\Util\BasicFunctions::decodeName($playerData->name) }}
            </h4>
        </div>
        <!-- ENDE Titel für Tablet | PC -->
        <!-- Datachart Stammeswechsel -->
        <div class="col-12 mt-2">
            <div class="card">
                <div class="card-body">
                    <table id="table_id" class="table table-hover table-sm w-100">
                        <thead>
                        <tr>
                            <th>{{ ucfirst(__('Datum')) }}</th>
                            <th>{{ ucfirst(__('Dorfname')) }}</th>
                            <th>{{ ucfirst(__('alter Besitzer')) }}</th>
                            <th>{{ ucfirst(__('neuer Besitzer')) }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- ENDE Datachart Stammeswechsel -->
    </div>
@endsection

@section('js')
    <script>

        $(document).ready( function () {
            $.extend( $.fn.dataTable.defaults, {
                responsive: true
            } );

            $('#table_id').DataTable({
                "columnDefs": [
                    {"targets": 1, "className": 'text-right'},
                    {"targets": 2, "className": 'text-right'},
                    {"targets": 3, "className": 'text-right'},
                ],
                "processing": true,
                "serverSide": true,
                "ajax": "{{ route('api.playerConquer', [$worldData->server->code, $worldData->name, $type, $playerData->playerID]) }}",
                "columns": [
                    { "data": "timestamp" },
                    { "data": "village_name", "render": function (value, type, row) {return "<a href='{{ route('world', [$worldData->server->code, $worldData->name]) }}/village/"+ row.villageID +"'>"+ value +'</a>'}, "orderable": false},
                    { "data": "old_owner_name", "render": function (value, type, row) {return (row.old_owner_exists)?("<a href='{{ route('world', [$worldData->server->code, $worldData->name]) }}/player/"+ row.old_owner +"'>"+ value +'</a>'):(value)}, "orderable": false},
                    { "data": "new_owner_name", "render": function (value, type, row) {return (row.new_owner_exists)?("<a href='{{ route('world', [$worldData->server->code, $worldData->name]) }}/player/"+ row.new_owner +"'>"+ value +'</a>'):(value)}, "orderable": false},
                ],
                responsive: true,
                {!! \App\Util\Datatable::language() !!}
            });
        } );
    </script>
@endsection