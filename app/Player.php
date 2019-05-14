<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Player extends Model
{
    private $hash = 59;
    protected $primaryKey = 'playerID';
    protected $fillable =[
            'id', 'name', 'ally', 'village', 'points', 'rank', 'off', 'offR', 'def', 'defR', 'tot', 'totR',
    ];

    public $timestamps = true;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->hash = env('HASH_PLAYER', 59);

    }

    /**
     *@return int
     */
    public function getHash(){
        return $this->hash;
    }


    public function allyLatest()
    {
        $table = explode('.', $this->table);
        return $this->mybelongsTo('App\Ally', 'ally_id', 'allyID', $table[0].'.ally_latest');
    }

    /*
     * Angepasste Funktion für die Ablageart der DB
     * */
    public function mybelongsTo($related, $foreignKey = null, $ownerKey = null, $table, $relation = null)
    {
        if (is_null($relation)) {
            $relation = $this->guessBelongsToRelation();
        }

        $instance = $this->newRelatedInstance($related);

        if (is_null($foreignKey)) {
            $foreignKey = Str::snake($relation).'_'.$instance->getKeyName();
        }

        $ownerKey = $ownerKey ?: $instance->getKeyName();

        $instance->setTable($table);

        return $this->newBelongsTo(
            $instance->newQuery(), $this, $foreignKey, $ownerKey, $relation
        );
    }

    public static function getAllyPlayer($server, $world){
        $playerModel = new Player();
        $replaceArray = array(
            '{server}' => $server,
            '{world}' => $world
        );
        $playerModel->setTable(str_replace(array_keys($replaceArray), array_values($replaceArray), env('DB_DATABASE_WORLD', 'c1welt_{server}{world}').'.player_latest'));

        return $playerModel->orderBy('rank')->get();
    }

    public static function getAllyPlayer20($server, $world, $order, $page){
        $playerModel = new Player();
        $replaceArray = array(
            '{server}' => $server,
            '{world}' => $world
        );
        $playerModel->setTable(str_replace(array_keys($replaceArray), array_values($replaceArray), env('DB_DATABASE_WORLD', 'c1welt_{server}{world}').'.player_latest'));

        return $playerModel->where($order, '>', $page*20-20)->orderBy($order)->limit(20)->get();
    }

    /*
     * Gibt die Top 10 Spieler zurück
     * */
    public static function top10Player($server, $world){
        $playerModel = new Player();
        $replaceArray = array(
            '{server}' => $server,
            '{world}' => $world
        );
        $playerModel->setTable(str_replace(array_keys($replaceArray), array_values($replaceArray), env('DB_DATABASE_WORLD', 'c1welt_{server}{world}').'.player_latest'));

        return $playerModel->orderBy('rank')->limit(10)->get();

    }

    public static function player($server, $world, $player){
        $playerModel = new Player();
        $replaceArray = array(
            '{server}' => $server,
            '{world}' => $world
        );
        $playerModel->setTable(str_replace(array_keys($replaceArray), array_values($replaceArray), env('DB_DATABASE_WORLD', 'c1welt_{server}{world}').'.player_latest'));

        return $playerModel->find($player);

    }

    public static function playerDataChart($server, $world, $playerID){
        $tabelNr = $playerID % env('HASH_PLAYER');

        $playerModel = new Player();
        $replaceArray = array(
            '{server}' => $server,
            '{world}' => $world
        );
        $playerModel->setTable(str_replace(array_keys($replaceArray), array_values($replaceArray), env('DB_DATABASE_WORLD', 'c1welt_{server}{world}').'.player_'.$tabelNr));

        $playerDataArray = $playerModel->where('playerID', $playerID)->orderBy('timestamp', 'DESC')->get();
        $playerDatas = collect();

        foreach ($playerDataArray as $player){
            $playerData = collect();
            $playerData->put('timestamp', $player->timestamp);
            $playerData->put('points', $player->points);
            $playerData->put('rank', $player->rank);
            $playerData->put('village', $player->village_count);
            $playerData->put('gesBash', $player->gesBash);
            $playerData->put('offBash', $player->offBash);
            $playerData->put('defBash', $player->deffBash);
            $playerData->put('utBash', $player->gesBash-$player->offBash-$player->deffBash);
            $playerDatas->push($playerData);
        }

        return $playerDatas;

    }

}