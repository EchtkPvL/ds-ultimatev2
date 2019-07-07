@extends('layouts.temp')

@section('titel', __('Suche'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="col-md-5 p-lg-5 mx-auto my-1 text-center">
                <h1 class="font-weight-normal">{{ __('Suche') }}: {!! ucfirst(($type == 'player')? __('Spieler'): (($type == 'ally')? __('Stämme'): __('Dörfer'))) !!}</h1>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">{{ucfirst(__('Suchergebnisse'))}}: {!! $result->count() !!}</h4>
                    @if ($type == 'player')
                        <table id="table_id" class="table table-striped table-hover table-sm w-100">
                            <thead><tr>
                                <th>{{ ucfirst(__('Welt')) }}</th>
                                <th>{{ ucfirst(__('Name')) }}</th>
                                <th>{{ ucfirst(__('Punkte')) }}</th>
                                <th>{{ ucfirst(__('Dörfer')) }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($result as $player)
                                <tr>
                                    <th>{{$player->get('world')->displayName()}}</th>
                                    <td>{!! \App\Util\BasicFunctions::linkPlayer($player->get('world'),$player->get('player')->playerID,\App\Util\BasicFunctions::outputName($player->get('player')->name)) !!}</td>
                                    <td>{{\App\Util\BasicFunctions::numberConv($player->get('player')->points)}}</td>
                                    <td>{{\App\Util\BasicFunctions::numberConv($player->get('player')->village_count)}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @elseif ($type == 'ally')
                        <table id="table_id" class="table table-hover table-sm w-100">
                            <thead><tr>
                                <th>{{ ucfirst(__('Welt')) }}</th>
                                <th>{{ ucfirst(__('Name')) }}</th>
                                <th>{{ ucfirst(__('Tag')) }}</th>
                                <th>{{ ucfirst(__('Punkte')) }}</th>
                                <th>{{ ucfirst(__('Dörfer')) }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($result as $ally)
                                <tr>
                                    <th>{{$ally->get('world')->displayName()}}</th>
                                    <td>{!! \App\Util\BasicFunctions::linkAlly($ally->get('world'),$ally->get('ally')->allyID,\App\Util\BasicFunctions::outputName($ally->get('ally')->name)) !!}</td>
                                    <td>{!! \App\Util\BasicFunctions::linkAlly($ally->get('world'),$ally->get('ally')->allyID,\App\Util\BasicFunctions::outputName($ally->get('ally')->tag)) !!}</td>
                                    <td>{{$ally->get('ally')->points}}</td>
                                    <td>{{$ally->get('ally')->village_count}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @else
                        <table id="table_id" class="table table-hover table-sm w-100">
                            <thead><tr>
                                <th>{{ ucfirst(__('Welt')) }}</th>
                                <th>{{ ucfirst(__('Name')) }}</th>
                                <th>{{ ucfirst(__('Punkte')) }}</th>
                                <th>{{ ucfirst(__('Kontinent')) }}</th>
                                <th>{{ ucfirst(__('Koordinaten')) }}</th>
                                <th>{{ ucfirst(__('Bonus')) }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($result as $village)
                                <tr>
                                    <th>{{$village->get('world')->displayName()}}</th>
                                    <td>{!! \App\Util\BasicFunctions::linkVillage($village->get('world'),$village->get('village')->villageID,\App\Util\BasicFunctions::outputName($village->get('village')->name)) !!}</td>
                                    <td>{{\App\Util\BasicFunctions::numberConv($village->get('village')->points)}}</td>
                                    <td>{{$village->get('village')->continentString()}}</td>
                                    <td>{{$village->get('village')->coordinates()}}</td>
                                    <td>{{$village->get('village')->bonusText()}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready( function () {
            $('#table_id').DataTable({
                ordering: false,
                responsive: true,
                {!! \App\Util\Datatable::language() !!}
            });
        } );
    </script>
@endsection
