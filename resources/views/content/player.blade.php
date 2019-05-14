@extends('layouts.temp')

@section('content')
    <div class="row">
        <div class="col-12">
            <ul id="lang_menu">
                <li class = "language{{ App::isLocale('de') ? ' active' : '' }}"><a href="{{ route('locale', 'de') }}">Deutsch</a></li>
                <li class = "language{{ App::isLocale('en') ? ' active' : '' }}"><a href="{{ route('locale', 'en') }}">English</a></li>
            </ul>
        </div>
        <div class="col-12 mx-2">
            <div class="card">
                <table class="table table-bordered no-wrap">
                    <thead>
                    <tr>
                        <th>{{ ucfirst(__('Rang')) }}</th>
                        <th>{{ ucfirst(__('Name')) }}</th>
                        <th>{{ ucfirst(__('Stamm')) }}</th>
                        <th>{{ ucfirst(__('Punkte')) }}</th>
                        <th>{{ ucfirst(__('Dörfer')) }}</th>
                        <th>{{ ucfirst(__('Punkte pro Dorf')) }}</th>
                        <th>{{ ucfirst(__('Stammeswechsel')) }}</th>
                        <th>{{ ucfirst(__('Eroberungen')) }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <th>{{ \App\Util\BasicFunctions::numberConv($playerData->rank) }}</th>
                        <td>{{ \App\Util\BasicFunctions::outputName($playerData->name) }}</td>
                        <td>{!! ($playerData->ally_id != 0)?\App\Util\BasicFunctions::linkAlly($worldData, $playerData->ally_id, \App\Util\BasicFunctions::outputName($playerData->allyLatest->tag)) : '-' !!}</td>
                        <td>{{ \App\Util\BasicFunctions::numberConv($playerData->points) }}</td>
                        <td>{{ \App\Util\BasicFunctions::numberConv($playerData->village_count) }}</td>
                        <td>{{ \App\Util\BasicFunctions::numberConv($playerData->points/$playerData->village_count) }}</td>
                        <td>{{ __('In Arbeit') }}</td>
                        <td>{{ $conquer->get('total') }}(<i class="text-success">{{ $conquer->get('new') }}</i>-<i class="text-danger">{{ $conquer->get('old') }}</i>)</td>
                    </tr>
                    </tbody>
                </table>
                <br>
                <table class="table table-bordered no-wrap">
                    <thead>
                        <th colspan="3">{{ __('Besiegte Gegner') }}-{{ __('Insgesamt') }}</th>
                        <th colspan="2">{{ __('Besiegte Gegner') }}-{{ __('Angreifer') }}</th>
                        <th colspan="2">{{ __('Besiegte Gegner') }}-{{ __('Verteidiger') }}</th>
                    </thead>
                    <thead>
                    <tr>
                        <th>{{ ucfirst(__('Rang')) }}</th>
                        <th>{{ ucfirst(__('Punkte')) }}</th>
                        <th>{{ ucfirst(__('KP-Rate')) }}</th>
                        <th>{{ ucfirst(__('Rang')) }}</th>
                        <th>{{ ucfirst(__('Punkte')) }}</th>
                        <th>{{ ucfirst(__('Rang')) }}</th>
                        <th>{{ ucfirst(__('Punkte')) }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <th>{{ \App\Util\BasicFunctions::numberConv($playerData->gesBashRank) }}</th>
                        <td>{{ \App\Util\BasicFunctions::numberConv($playerData->gesBash) }}</td>
                        <td>{{ \App\Util\BasicFunctions::numberConv(($playerData->gesBash/$playerData->points)*100) }}%</td>
                        <th>{{ \App\Util\BasicFunctions::numberConv($playerData->offBashRank) }}</th>
                        <td>{{ \App\Util\BasicFunctions::numberConv($playerData->offBash) }}</td>
                        <th>{{ \App\Util\BasicFunctions::numberConv($playerData->deffBashRank) }}</th>
                        <td>{{ \App\Util\BasicFunctions::numberConv($playerData->deffBash) }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-6">
            <div class="col-12">
                Diagramm:
                <select id="statsGeneral" class="form-control">
                    @for($i = 0; $i < count($statsGeneral); $i++)
                        <option value="{{ $statsGeneral[$i] }}" {{ ($i == 0)? 'selected=""' : null }}>{{ __('chart.titel_'.$statsGeneral[$i]) }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-12">
                @for($i = 0; $i < count($statsGeneral); $i++)
                    <div id="{{ $statsGeneral[$i] }}" class="col-12 position-absolute px-0">
                        <div class="card">
                            <div id="chart-{{ $statsGeneral[$i] }}"></div>
                        </div>
                    </div>
                @endfor
            </div>
        </div>
        <div class="col-6">
            <div class="col-12">
                Diagramm:
                <select id="statsBash" class="form-control">
                    @for($i = 0; $i < count($statsBash); $i++)
                        <option value="{{ $statsBash[$i] }}" {{ ($i == 0)? 'selected=""' : null }}>{{ __('chart.titel_'.$statsBash[$i]) }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-12">
                @for($i = 0; $i < count($statsBash); $i++)
                    <div id="{{ $statsBash[$i] }}" class="col-12 position-absolute px-0">
                        <div class="card">
                            <div id="chart-{{ $statsBash[$i] }}"></div>
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function () {
            $("#{{ $statsGeneral[0] }}").css('visibility', 'visible');
            $("#{{ $statsGeneral[1] }}").css('visibility', 'hidden');
            $("#{{ $statsGeneral[2] }}").css('visibility', 'hidden');
            $("#{{ $statsBash[0] }}").css('visibility', 'visible');
            $("#{{ $statsBash[1] }}").css('visibility', 'hidden');
            $("#{{ $statsBash[2] }}").css('visibility', 'hidden');
            $("#{{ $statsBash[3] }}").css('visibility', 'hidden');
        });

        $("#statsGeneral").change(function () {
            var option1 = $("#statsGeneral").val();
            if (option1 == '{{ $statsGeneral[0] }}') {
                $("#{{ $statsGeneral[0] }}").css('visibility', 'visible');
                $("#{{ $statsGeneral[1] }}").css('visibility', 'hidden');
                $("#{{ $statsGeneral[2] }}").css('visibility', 'hidden');
            }
            if (option1 == '{{ $statsGeneral[1] }}') {
                $("#{{ $statsGeneral[0] }}").css('visibility', 'hidden');
                $("#{{ $statsGeneral[1] }}").css('visibility', 'visible');
                $("#{{ $statsGeneral[2] }}").css('visibility', 'hidden');
            }
            if (option1 == '{{ $statsGeneral[2] }}') {
                $("#{{ $statsGeneral[0] }}").css('visibility', 'hidden');
                $("#{{ $statsGeneral[1] }}").css('visibility', 'hidden');
                $("#{{ $statsGeneral[2] }}").css('visibility', 'visible');
            }
        });

        $("#statsBash").change(function () {
            var option1 = $("#statsBash").val();
            {{--$(location).attr("href", "{{ URL::route('troopForm') }}/" + option1 + "/" + option2);--}}
            if (option1 == '{{ $statsBash[0] }}') {
                $("#{{ $statsBash[0] }}").css('visibility', 'visible');
                $("#{{ $statsBash[1] }}").css('visibility', 'hidden');
                $("#{{ $statsBash[2] }}").css('visibility', 'hidden');
                $("#{{ $statsBash[3] }}").css('visibility', 'hidden');
            }
            if (option1 == '{{ $statsBash[1] }}') {
                $("#{{ $statsBash[0] }}").css('visibility', 'hidden');
                $("#{{ $statsBash[1] }}").css('visibility', 'visible');
                $("#{{ $statsBash[2] }}").css('visibility', 'hidden');
                $("#{{ $statsBash[3] }}").css('visibility', 'hidden');
            }
            if (option1 == '{{ $statsBash[2] }}') {
                $("#{{ $statsBash[0] }}").css('visibility', 'hidden');
                $("#{{ $statsBash[1] }}").css('visibility', 'hidden');
                $("#{{ $statsBash[2] }}").css('visibility', 'visible');
                $("#{{ $statsBash[3] }}").css('visibility', 'hidden');
            }
            if (option1 == '{{ $statsBash[3] }}') {
                $("#{{ $statsBash[0] }}").css('visibility', 'hidden');
                $("#{{ $statsBash[1] }}").css('visibility', 'hidden');
                $("#{{ $statsBash[2] }}").css('visibility', 'hidden');
                $("#{{ $statsBash[3] }}").css('visibility', 'visible');
            }
        });

    </script>
@endsection