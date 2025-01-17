<?php

namespace App\Console\Commands;

use App\Http\Controllers\DBController;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateNextWorld extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:nextWorld';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aktualisiert die nächste Welt';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        \App\Util\BasicFunctions::ignoreErrs();
        if(! DBController::updateNeeded()) {
            echo "No Update needed\n";
            return;
        }
        $worldModel = new \App\World();
        $world = $worldModel->where('worldUpdated_at', '<', Carbon::createFromTimestamp(time()
                - (60 * 60) * env('DB_UPDATE_EVERY_HOURS')))->where('active', '=', 1)
                ->orderBy('worldUpdated_at', 'ASC')->first();
        echo "Server: {$world->server->code} World:{$world->name}\n";
        UpdateWorldData::updateWorldData($world->server->code, $world->name, 'v');
        UpdateWorldData::updateWorldData($world->server->code, $world->name, 'p');
        UpdateWorldData::updateWorldData($world->server->code, $world->name, 'a');
    }
}
