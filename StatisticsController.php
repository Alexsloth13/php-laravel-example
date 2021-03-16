<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\StatisticsModel;
use Carbon\Carbon;

class StatisticsController extends Controller
{

    public function __construct(Statistics $statistics)
    {
      $this->middleware('auth');
      $this->statistics = $statistics;
    }

    public function index(Request $request, Statistics $stat)
    {	
      $user_id = Auth::id();
      $stream_id = $request->get('stream_id');
      $date = $request->get('date');

      // Удалить статистику потока
      if($request->get('action') === 'clear'){
        $stat->clearStatsByStreamId($user_id, $stream_id);
      }

      if (empty($date))
		$date = Carbon::today()->toDateString();

		$datas = $this->statistics->getStatByDate($user_id, $stream_id, $date, 40);

		$total_clicks = $this->statistics->clicksByDate($user_id, $stream_id, $date);

		$clicks_bloced = $this->statistics->clicksBlocedByDate($user_id, $stream_id, $date);

		$clicks_allow =  $total_clicks - $clicks_bloced;

      return view('statistics', [
        'datas' =>  $datas, 
        'stream_id' => $stream_id, 
        'date' => $date,
        'total_clicks' => $total_clicks,
        'clicks_bloced' => $clicks_bloced,
        'clicks_allow' => $clicks_allow
      ]);
    }
    
}
