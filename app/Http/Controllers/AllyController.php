<?php

namespace App\Http\Controllers;

use App\Conquer;
use App\Ally;
use App\Util\BasicFunctions;
use App\Util\Chart;
use App\World;

class AllyController extends Controller
{
    public function ally($server, $world, $ally){
        BasicFunctions::local();
        World::existWorld($server, $world);

        $worldData = World::getWorld($server, $world);

        $allyData = Ally::ally($server, $world, $ally);
        if ($allyData == null){
            //TODO: View ergänzen für Fehlermeldungen
            echo "Keine Daten über den Stamm mit der ID '$ally' auf der Welt '$server$world' vorhanden.";
            exit;
        }


        $statsGeneral = ['points', 'rank', 'village'];
        $statsBash = ['gesBash', 'offBash', 'defBash'];

        $datas = Ally::allyDataChart($server, $world, $ally);
        
        $chartJS = "";
        for ($i = 0; $i < count($statsGeneral); $i++){
            $chartJS .= $this->chart($datas, $statsGeneral[$i]);
        }
        for ($i = 0; $i < count($statsBash); $i++){
            $chartJS .= $this->chart($datas, $statsBash[$i]);
        }
        
        $conquer = Conquer::allyConquerCounts($server, $world, $ally);
        
        return view('content.ally', compact('statsGeneral', 'statsBash', 'allyData', 'conquer', 'worldData', 'chartJS', 'server'));
    }

    public function chart($allyData, $data){
        if (!Chart::validType($data)) {
            return;
        }
        
        $population = \Lava::DataTable();

        $population->addDateColumn('Tag')
            ->addNumberColumn(Chart::chartLabel($data));

        $oldTimestamp = 0;
        $i = 0;
        foreach ($allyData as $aData){
            if (date('Y-m-d', $aData->get('timestamp')) != $oldTimestamp){
                $population->addRow([date('Y-m-d', $aData->get('timestamp')), $aData->get($data)]);
                $oldTimestamp =date('Y-m-d', $aData->get('timestamp'));
                $i++;
            }
        }

        if ($i == 1){
            $population->addRow([date('Y-m-d', $aData->get('timestamp')-60*60*24), 0]);
        }

        \Lava::LineChart($data, $population, [
            'title' => Chart::chartTitel($data),
            'legend' => 'none',
            'hAxis' => [
                'format' => 'dd/MM'
            ],
            'vAxis' => [
                'direction' => (Chart::displayInvers($data)?(-1):(1)),
                'format' => '0',
            ]
        ]);

        return \Lava::render('LineChart', $data, 'chart-'.$data);
    }
}
