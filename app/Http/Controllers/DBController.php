<?php

namespace App\Http\Controllers;

use App\Ally;
use App\Conquer;
use App\Player;
use App\Server;
use App\Util\BasicFunctions;
use App\Village;
use App\World;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DBController extends Controller
{
    public function serverTable(){
        DB::statement('CREATE DATABASE '.env('DB_DATABASE_MAIN'));
        Schema::create(env('DB_DATABASE_MAIN').'.server', function (Blueprint $table){
            $table->integer('id')->autoIncrement();
            $table->char('code');
            $table->char('flag');
            $table->text('url');
        });
    }

    public function logTable(){
        Schema::create(env('DB_DATABASE_MAIN').'.log', function (Blueprint $table){
            $table->bigIncrements('id')->autoIncrement();
            $table->text('type');
            $table->text('msg');
            $table->timestamps();
        });
    }

    public function worldTable(){
        Schema::create(env('DB_DATABASE_MAIN').'.worlds', function (Blueprint $table){
            $table->integer('id')->autoIncrement();
            $table->integer('server_id');
            $table->text('name');
            $table->integer('ally_count')->nullable();
            $table->integer('player_count')->nullable();
            $table->integer('village_count')->nullable();
            $table->text('url');
            $table->text('config');
            $table->timestamps();
        });
    }

    public function playerTable($dbName, $tableName){
        Schema::create($dbName.'.player_'.$tableName, function (Blueprint $table) {
            $table->integer('playerID');
            $table->string('name');
            $table->integer('ally_id');
            $table->integer('village_count');
            $table->integer('points');
            $table->integer('rank');
            $table->bigInteger('offBash')->nullable();
            $table->integer('offBashRank')->nullable();
            $table->bigInteger('defBash')->nullable();
            $table->integer('defBashRank')->nullable();
            $table->bigInteger('gesBash')->nullable();
            $table->integer('gesBashRank')->nullable();
            $table->timestamps();
        });
    }

    public function allyTable($dbName, $tableName){
        Schema::create($dbName.'.ally_'.$tableName, function (Blueprint $table) {
            $table->integer('allyID');
            $table->string('name');
            $table->string('tag');
            $table->integer('member_count');
            $table->integer('points');
            $table->integer('village_count');
            $table->integer('rank');
            $table->bigInteger('offBash')->nullable();
            $table->integer('offBashRank')->nullable();
            $table->bigInteger('defBash')->nullable();
            $table->integer('defBashRank')->nullable();
            $table->bigInteger('gesBash')->nullable();
            $table->integer('gesBashRank')->nullable();
            $table->timestamps();
        });
    }

    public function villageTable($dbName, $tableName){
        Schema::create($dbName.'.village_'.$tableName, function (Blueprint $table) {
            $table->integer('villageID');
            $table->string('name');
            $table->integer('x');
            $table->integer('y');
            $table->integer('points');
            $table->integer('owner');
            $table->integer('bonus_id');
            $table->timestamps();
        });
    }

    public function conquerTable($dbName){
        Schema::create($dbName.'.conquer', function (Blueprint $table) {
            $table->integer('villageID');
            $table->string('timestamp');
            $table->integer('new_owner');
            $table->integer('old_owner');
            $table->timestamps();
        });
    }

    public function getWorld(){

        if (BasicFunctions::existTable(env('DB_DATABASE_MAIN'), 'worlds') === false){
            $this->worldTable();
        }

        $serverArray = Server::getServer();

        foreach ($serverArray as $serverUrl){
            $worldFile = file_get_contents($serverUrl->url.'/backend/get_servers.php');
            $worldTable = new World();
            $worldTable->setTable(env('DB_DATABASE_MAIN').'.worlds');
            $worldArray = unserialize($worldFile);
            foreach ($worldArray as $world => $link){

                $worldName = substr($world, 2);

                if ($worldTable->where('server_id', $serverUrl->id)->where('name', $worldName)->count() >= 1){
                    //world exists already -> ignore
                    // FIXME: Update saved world data with new data
                    continue;
                }
                
                $worldNew = new World();
                $worldNew->setTable(env('DB_DATABASE_MAIN').'.worlds');
                $worldNew->server_id = $serverUrl->id;
                $worldNew->name = $worldName;
                $worldNew->url = $link;
                $txt = file_get_contents("$link/interface.php?func=get_config");
                $worldNew->config = $txt;

                if ($worldNew->save() !== true){
                    BasicFunctions::createLog('ERROR_insert[World]', "Welt $world konnte nicht der Tabelle 'worlds' hinzugefügt werden.");
                    continue;
                }

                BasicFunctions::createLog('insert[World]', "Welt $world wurde erfolgreich der Tabelle '$world' hinzugefügt.");
                $name = BasicFunctions::getDatabaseName('', '').$world;
                if (BasicFunctions::existTable($name, 'player_latest') !== false) {
                    BasicFunctions::createLog("ERROR_createBD[$world]", "DB '$name' existierte bereits.");
                    continue;
                }
                if (DB::statement('CREATE DATABASE ' . $name) !== true) {
                    BasicFunctions::createLog("ERROR_createBD[$world]", "DB '$name' konnte nicht erstellt werden.");
                    continue;
                }
                BasicFunctions::createLog("createBD[$world]", "DB '$name' wurde erfolgreich erstellt.");
            }
        }
    }
    
    // FIXME: use server Url from servers table (to enable support for other languages
    public function latestPlayer($server, $world){
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '400M');
        $dbName = BasicFunctions::getDatabaseName($server, $world);
        $worldUpdate = World::getWorld($server, $world);

        if (BasicFunctions::existTable($dbName, 'player_latest_temp') === false){
            $this->playerTable($dbName, 'latest_temp');
        }

        $lines = gzfile("$worldUpdate->url/map/player.txt.gz");
        if(!is_array($lines)) {
            BasicFunctions::createLog("ERROR_update[$server$world]", "player.txt.gz konnte nicht ge&ouml;ffnet werden");
            return;
        }

        $players = collect();
        $playerOffs = collect();
        $playerDefs = collect();
        $playerTots = collect();

        foreach ($lines as $line){
            list($id, $name, $ally, $villages, $points, $rank) = explode(',', $line);
            $player = collect();
            $player->put('id', (int)$id);
            $player->put('name', $name);
            $player->put('ally', (int)$ally);
            $player->put('villages', (int)$villages);
            $player->put('points', (int)$points);
            $player->put('rank', (int)$rank);
            $players->put($player->get('id'),$player);
        }

        $offs = gzfile("$worldUpdate->url/map/kill_att.txt.gz");
        if(!is_array($offs)) {
            BasicFunctions::createLog("ERROR_update[$server$world]", "kill_att.txt.gz konnte nicht ge&ouml;ffnet werden");
            return;
        }
        foreach ($offs as $off){
            list($rank, $id, $kills) = explode(',', $off);
            $playerOff = collect();
            $playerOff->put('offRank', (int)$rank);
            $playerOff->put('off', (int)$kills);
            $playerOffs->put($id, $playerOff);
        }

        $defs = gzfile("$worldUpdate->url/map/kill_def.txt.gz");
        if(!is_array($defs)) {
            BasicFunctions::createLog("ERROR_update[$server$world]", "kill_def.txt.gz konnte nicht ge&ouml;ffnet werden");
            return;
        }
        foreach ($defs as $def){
            list($rank, $id, $kills) = explode(',', $def);
            $playerDef = collect();
            $playerDef->put('defRank', (int)$rank);
            $playerDef->put('def', (int)$kills);
            $playerDefs->put($id, $playerDef);
        }

        $tots = gzfile("$worldUpdate->url/map/kill_all.txt.gz");
        if(!is_array($tots)) {
            BasicFunctions::createLog("ERROR_update[$server$world]", "kill_all.txt.gz konnte nicht ge&ouml;ffnet werden");
            return;
        }
        foreach ($tots as $tot){
            list($rank, $id, $kills) = explode(',', $tot);
            $playerTot = collect();
            $playerTot->put('totRank', (int)$rank);
            $playerTot->put('tot', (int)$kills);
            $playerTots->put($id, $playerTot);
        }

        $insert = new Player();
        $insert->setTable($dbName.'.player_latest_temp');
        foreach ($players as $player) {
            $id = $player->get('id');
            $dataPlayer = [
                'playerID' => $player->get('id'),
                'name' => $player->get('name'),
                'ally_id' => $player->get('ally'),
                'village_count' => $player->get('villages'),
                'points' => $player->get('points'),
                'rank' => $player->get('rank'),
                'offBash' => (is_null($playerOffs->get($id)))? null :$playerOffs->get($id)->get('off'),
                'offBashRank' => (is_null($playerOffs->get($id)))? null : $playerOffs->get($id)->get('offRank'),
                'defBash' => (is_null($playerDefs->get($id)))? null : $playerDefs->get($id)->get('def'),
                'defBashRank' => (is_null($playerDefs->get($id)))? null : $playerDefs->get($id)->get('defRank'),
                'gesBash' => (is_null($playerTots->get($id)))? null : $playerTots->get($id)->get('tot'),
                'gesBashRank' => (is_null($playerTots->get($id)))? null : $playerTots->get($id)->get('totRank'),
                'created_at' => Carbon::createFromTimestamp(time()),
                'updated_at' => Carbon::createFromTimestamp(time()),
            ];
            $arrayPlayer []= $dataPlayer;
        }

        foreach (array_chunk($arrayPlayer,3000) as $t){
            $insert->insert($t);
        }

        if (BasicFunctions::existTable($dbName, 'player_latest') === true){
            DB::statement("DROP TABLE $dbName.player_latest");
        }
        DB::statement("ALTER TABLE $dbName.player_latest_temp RENAME TO $dbName.player_latest");
        
        $hashPlayer = $this->hashTable($arrayPlayer, 'p', 'playerID');

        for ($i = 0; $i < env('HASH_PLAYER'); $i++){
            if (array_key_exists($i ,$hashPlayer)) {
                if (BasicFunctions::existTable($dbName, 'player_' . $i) === false) {
                    $this->playerTable($dbName, $i);
                }
                $insert->setTable($dbName . '.player_' . $i);
                foreach (array_chunk($hashPlayer[$i], 3000) as $t) {
                    $insert->insert($t);
                }
                if (BasicFunctions::existTable($dbName, 'player_' . $i) === true) {
                    $delete = $insert->where('updated_at', '<', Carbon::createFromTimestamp(time() - (60 * 60 * 24) * env('DB_SAVE_DAY')));
                    $delete->delete();
                }
            }
        }

        $count = count($arrayPlayer);

        $worldUpdate->player_count = $count;
        $worldUpdate->save();

        //Log::debug($worldName.'-> Player: '.$count);
    }

    public function latestVillages($server, $world){
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '500M');
        $dbName = BasicFunctions::getDatabaseName($server, $world);
        $worldUpdate = World::getWorld($server, $world);

        if (BasicFunctions::existTable($dbName, 'village_latest_temp') === false) {
            $this->villageTable($dbName, 'latest_temp');
        }

        $lines = gzfile("$worldUpdate->url/map/village.txt.gz");
        if (!is_array($lines)) {
            BasicFunctions::createLog("ERROR_update[$server$world]", "village.txt.gz konnte nicht ge&ouml;ffnet werden");
            return;
        }
        $villages = collect();
        foreach ($lines as $line) {
            list($id, $name, $x, $y, $owner, $points, $bonus_id) = explode(',', $line);
            $village = collect();
            $village->put('id', (int)$id);
            $village->put('name', $name);
            $village->put('x', (int)$x);
            $village->put('y', (int)$y);
            $village->put('points', (int)$points);
            $village->put('owner', (int)$owner);
            $village->put('bonus_id', (int)$bonus_id);
            $villages->put($village->get('id'), $village);
        }

        $insert = new Village();
        $insert->setTable($dbName . '.village_latest_temp');
        $array = array();
        foreach ($villages as $village) {
            $data = [
                'villageID' => $village->get('id'),
                'name' => $village->get('name'),
                'x' => $village->get('x'),
                'y' => $village->get('y'),
                'points' => $village->get('points'),
                'owner' => $village->get('owner'),
                'bonus_id' => $village->get('bonus_id'),
                'created_at' => Carbon::createFromTimestamp(time()),
                'updated_at' => Carbon::createFromTimestamp(time()),
            ];
            $array [] = $data;
        }
        foreach (array_chunk($array, 3000) as $t) {
            $insert->insert($t);
        }

        if (BasicFunctions::existTable($dbName, 'village_latest') === true){
            DB::statement("DROP TABLE $dbName.village_latest");
        }

        DB::statement("ALTER TABLE $dbName.village_latest_temp RENAME TO $dbName.village_latest");

        $hashVillage = $this->hashTable($array, 'v', 'villageID');
        for ($i = 0; $i < env('HASH_VILLAGE'); $i++) {
            if (array_key_exists($i, $hashVillage)) {
                if (BasicFunctions::existTable($dbName, 'village_' . $i) === false) {
                    $this->villageTable($dbName, $i);
                }
                $insert->setTable($dbName . '.village_' . $i);
                foreach (array_chunk($hashVillage[$i], 3000) as $t) {
                    $insert->insert($t);
                }
                if (BasicFunctions::existTable($dbName, 'village_' . $i) === true) {
                    $delete = $insert->where('updated_at', '<', Carbon::createFromTimestamp(time() - (60 * 60 * 24) * env('DB_SAVE_DAY')));
                    $delete->delete();
                }
            }
        }

        $count = count($array);

        $worldUpdate->village_count = $count;
        $worldUpdate->save();


        //Log::debug($worldName.'-> Village: '.$count);
    }

    public function latestAlly($server, $world){
        ini_set('max_execution_time', 1800);
        ini_set('memory_limit', '200M');
        $dbName = BasicFunctions::getDatabaseName($server, $world);
        $worldUpdate = World::getWorld($server, $world);

        if (BasicFunctions::existTable($dbName, 'ally_latest_temp') === false){
            $this->allyTable($dbName, 'latest_temp');
        }

        $lines = gzfile("$worldUpdate->url/map/ally.txt.gz");
        if(!is_array($lines)) {
            BasicFunctions::createLog("ERROR_update[$server$world]", "ally.txt.gz konnte nicht ge&ouml;ffnet werden");
            return;
        }

        $allys = collect();
        $allyOffs = collect();
        $allyDefs = collect();
        $allyTots = collect();

        foreach ($lines as $line){
            list($id, $name, $tag, $members, $villages, $points, $points_all, $rank) = explode(',', $line);
            $ally = collect();
            $ally->put('id', (int)$id);
            $ally->put('name', $name);
            $ally->put('tag', $tag);
            $ally->put('member_count', (int)$members);
            $ally->put('points', (int)$points_all);
            $ally->put('village_count', (int)$villages);
            $ally->put('rank', (int)$rank);
            $allys->put($ally->get('id'),$ally);
        }

        $offs = gzfile("$worldUpdate->url/map/kill_att_tribe.txt.gz");
        if(!is_array($offs)) {
            BasicFunctions::createLog("ERROR_update[$server$world]", "kill_att_tribe.txt.gz konnte nicht ge&ouml;ffnet werden");
            return;
        }
        foreach ($offs as $off){
            list($rank, $id, $kills) = explode(',', $off);
            $allyOff = collect();
            $allyOff->put('offRank', (int)$rank);
            $allyOff->put('off', (int)$kills);
            $allyOffs->put($id, $allyOff);

        }

        $defs = gzfile("$worldUpdate->url/map/kill_def_tribe.txt.gz");
        if(!is_array($defs)) {
            BasicFunctions::createLog("ERROR_update[$server$world]", "kill_def_tribe.txt.gz konnte nicht ge&ouml;ffnet werden");
            return;
        }
        foreach ($defs as $def){
            list($rank, $id, $kills) = explode(',', $def);
            $allyDef = collect();
            $allyDef->put('defRank', (int)$rank);
            $allyDef->put('def', (int)$kills);
            $allyDefs->put($id, $allyDef);
        }

        $tots = gzfile("$worldUpdate->url/map/kill_all_tribe.txt.gz");
        if(!is_array($tots)) {
            BasicFunctions::createLog("ERROR_update[$server$world]", "kill_all_tribe.txt.gz konnte nicht ge&ouml;ffnet werden");
            return;
        }
        foreach ($tots as $tot){
            list($rank, $id, $kills) = explode(',', $tot);
            $allyTot = collect();
            $allyTot->put('totRank', (int)$rank);
            $allyTot->put('tot', (int)$kills);
            $allyTots->put($id, $allyTot);
        }

        $insert = new Ally();
        $insert->setTable($dbName.'.ally_latest_temp');
        $array = array();
        foreach ($allys as $ally) {
            $id = $ally->get('id');
            $data = [
                'allyID' => $ally->get('id'),
                'name' => $ally->get('name'),
                'tag' => $ally->get('tag'),
                'member_count' => $ally->get('member_count'),
                'points' => $ally->get('points'),
                'village_count' => $ally->get('village_count'),
                'rank' => $ally->get('rank'),
                'offBash' => (is_null($allyOffs->get($id)))? null :$allyOffs->get($id)->get('off'),
                'offBashRank' => (is_null($allyOffs->get($id)))? null : $allyOffs->get($id)->get('offRank'),
                'defBash' => (is_null($allyDefs->get($id)))? null : $allyDefs->get($id)->get('def'),
                'defBashRank' => (is_null($allyDefs->get($id)))? null : $allyDefs->get($id)->get('defRank'),
                'gesBash' => (is_null($allyTots->get($id)))? null : $allyTots->get($id)->get('tot'),
                'gesBashRank' => (is_null($allyTots->get($id)))? null : $allyTots->get($id)->get('totRank'),
                'created_at' => Carbon::createFromTimestamp(time()),
                'updated_at' => Carbon::createFromTimestamp(time()),
            ];
            $array []= $data;
        }
        foreach (array_chunk($array,3000) as $t){
            $insert->insert($t);
        }


        if (BasicFunctions::existTable($dbName, 'ally_latest') === true){
            DB::statement("DROP TABLE $dbName.ally_latest");
        }
        DB::statement("ALTER TABLE $dbName.ally_latest_temp RENAME TO $dbName.ally_latest");

        $hashAlly = $this->hashTable($array, 'a', 'allyID');

        for ($i = 0; $i < env('HASH_ALLY'); $i++){
            if (array_key_exists($i ,$hashAlly)) {
                if (BasicFunctions::existTable($dbName, 'ally_' . $i) === false) {
                    $this->allyTable($dbName, $i);
                }
                $insert->setTable($dbName . '.ally_' . $i);
                foreach (array_chunk($hashAlly[$i], 3000) as $t) {
                    $insert->insert($t);
                }
                if (BasicFunctions::existTable($dbName, 'ally_' . $i) === true) {
                    $delete = $insert->where('updated_at', '<', Carbon::createFromTimestamp(time() - (60 * 60 * 24) * env('DB_SAVE_DAY')));
                    $delete->delete();
                }
            }
        }

        $count = count($array);

        $worldUpdate->ally_count = $count;
        $worldUpdate->save();

        //Log::debug($worldName.'-> Ally: '.$count);
    }

    public function conquer($server, $world){
        // FIXME: use $server_URL/interface.php?func=get_conquer&since=$last_timestamp
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '700M');
        date_default_timezone_set("Europe/Berlin");
        $dbName = BasicFunctions::getDatabaseName($server, $world);
        $worldUpdate = World::getWorld($server, $world);

        if (BasicFunctions::existTable($dbName, 'conquer') === false) {
            $this->conquerTable($dbName);
        }
        $conquer = new Conquer();
        $conquer->setTable($dbName.'.conquer');
        $count = $conquer->get()->count();

        $lines = gzfile("$worldUpdate->url/map/conquer.txt.gz");
        if (!is_array($lines)) {
            BasicFunctions::createLog("ERROR_update[$server$world]", "conquer.txt.gz konnte nicht ge&ouml;ffnet werden");
            return;
        }
        $i = 0;
        $array = array();
        foreach ($lines as $line) {
            if ($i >= $count){
                $array[$i] = array();
                list($array[$i]['villageID'], $array[$i]['timestamp'], $array[$i]['new_owner'], $array[$i]['old_owner']) = explode(',', $line);
                $array[$i]['created_at'] = Carbon::createFromTimestamp(time());
                $array[$i]['updated_at'] = Carbon::createFromTimestamp(time());
            }
            $i ++;
        }

        $insert = new Conquer();
        $insert->setTable($dbName . '.conquer');

        foreach (array_chunk($array, 3000) as $t) {
            $insert->insert($t);
        }

    }

    public function hashTable($mainArray, $type, $index){
        $hashArray = array();
        foreach ($mainArray as $main){
            $id = $main[$index];
            if (! array_key_exists(BasicFunctions::hash($id, $type), $hashArray)) {
                $hashArray[BasicFunctions::hash($id, $type)] = array();
            }
            $hashArray[BasicFunctions::hash($id, $type)][] = $main;
        }

        return $hashArray;
    }
}
