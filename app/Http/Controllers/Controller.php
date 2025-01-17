<?php

namespace App\Http\Controllers;

use App\Ally;
use App\News;
use App\Player;
use App\Server;
use App\Util\BasicFunctions;
use App\World;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function index(){
        BasicFunctions::local();
        $serverArray = Server::getServer();
        $news = News::all();
        return view('content.index', compact('serverArray', 'news'));
    }

    /*
     * https://ds-ultimate.de/de
     * */
    public function server($server){
        BasicFunctions::local();
        World::existServer($server);
        $worldsArray = World::worldsCollection($server);
        return view('content.server', compact('worldsArray', 'server'));
    }

    /*
     * https://ds-ultimate.de/de/164
     * */
    public function world($server, $world){
        BasicFunctions::local();
        World::existWorld($server, $world);

        $playerArray = Player::top10Player($server, $world);
        $allyArray = Ally::top10Ally($server, $world);
        $worldData = World::getWorld($server, $world);

        return view('content.world', compact('playerArray', 'allyArray', 'worldData', 'server'));

    }

    /*
     * https://ds-ultimate.de/de/164/allys
     * */
    public function allys($server, $world){
        BasicFunctions::local();
        World::existWorld($server, $world);

        $worldData = World::getWorld($server, $world);
        
        return view('content.worldAlly', compact('worldData', 'server'));
    }

    /*
     * https://ds-ultimate.de/de/164/players
     * */
    public function players($server, $world){
        BasicFunctions::local();
        World::existWorld($server, $world);

        $worldData = World::getWorld($server, $world);

        return view('content.worldPlayer', compact('worldData', 'server'));
    }

    public function search($server, $type, $search){
        BasicFunctions::local();
        switch ($type){
            case 'player':
                $result = SearchController::searchPlayer($server, $search);
                return view('content.search', compact('search', 'type', 'result', 'server'));
            case 'ally':
                $result = SearchController::searchAlly($server, $search);
                return view('content.search', compact('search', 'type', 'result', 'server'));
            case 'village':
                $result = SearchController::searchVillage($server, $search);
                return view('content.search', compact('search', 'type', 'result', 'server'));
        }
    }

    public function sitemap() {
        $servers = array();
        $serverArray = Server::getServer();
        
        foreach($serverArray as $server) {
            $worldsArray = World::worldsCollection($server->code);
            $servers[$server->code] = collect();
            
            if($worldsArray->get('world') != null && count($worldsArray->get('world')) > 0) {
                $servers[$server->code] = $servers[$server->code]->merge($worldsArray->get('world'));
            }
            if($worldsArray->get('speed') != null && count($worldsArray->get('speed')) > 0) {
                $servers[$server->code] = $servers[$server->code]->merge($worldsArray->get('speed'));
            }
            if($worldsArray->get('casual') != null && count($worldsArray->get('casual')) > 0) {
                $servers[$server->code] = $servers[$server->code]->merge($worldsArray->get('casual'));
            }
            if($worldsArray->get('classic') != null && count($worldsArray->get('classic')) > 0) {
                $servers[$server->code] =  $servers[$server->code]->merge($worldsArray->get('classic'));
            }
        }
        
        return response()->view('sitemap', compact('servers'))->header('Content-Type', 'text/xml');
    }
}
