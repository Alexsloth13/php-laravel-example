<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\StreamsModel;
use App\Models\SubModel;
use App\Models\Statistics;
use App\Http\Controllers\Route;

class StreamsController extends Controller
{
    public function __construct(StreamsModel $streams)
    {
        $this->middleware('auth');
		$this->streams = $streams;
    }

    public function index(Request $request, Statistics $stat)
    {
		$user_id = Auth::id();
		
		// Удалить поток
		if($request->get('stream_id') && $request->get('action') === 'trash'){
			if(SubModel::is_active($user_id)){
				$stream_id	= $request->get('stream_id');
				$stat->clearStatsByStreamId($user_id, $stream_id);
				$this->streams->delete_stream( $stream_id, $user_id );
			}else{
				return redirect('streams')->with('status', 'Подписка не активна');
			}
		}

		// Вывести список потоков
		$streams = $this->streams->select_streams( $user_id );

		// Возвращает массив 'stream_id'(1) = > count_clicks(145)
		$clicks = $stat->clicksByStreamsIds($user_id, $streams);

        return view('streams',['streams' => $streams, 'clicks' => $clicks]);
    }
	
	public function add(Request $request){
		
		$user_id = Auth::id();

		if(SubModel::is_active($user_id))
		{
			// Добавляем проверку количества разрешенныых потоков
			$streams_count =  $this->streams->streams_count($user_id);

			$allowed_streams_count = SubModel::allowed_streams_count($user_id);

			if($streams_count < $allowed_streams_count){

				if($request->input('title') && 
				$request->input('safe_page') && 
				$request->input('offer_page'))
				{
					$data['user_id']	= $user_id;
					$data['title']		= $request->input('title');
					$data['safe_page']	= $request->input('safe_page');
					$data['offer_page']	= $request->input('offer_page');
					if(!empty($request->input('geo')))
						$data['geo']	= implode(', ', $request->input('geo'));
					else
						$data['geo']	= "";
					$data['on_off']		= (int)$request->input('on_off');
					$data['ipv6']		= (int)$request->input('ipv6');

					if(!empty($request->input('device')))
						$data['device']	= implode(',', $request->input('device'));
					else
						$data['device']	= "";

					$streams = $this->streams->insert_stream( $data );
					return redirect('streams')->with('success', "Поток создан, установите код интеграции.");
				}else{
					$geo = $this->streams->create_geo_options('');
					$device = $this->streams->create_device_options('');
					return view('edit', ['geo' => $geo, 'device' => $device]);
				}
			}
			else
			{
				return redirect('streams')->with('status', "Привышение лимита потоков: макс. $allowed_streams_count");
			}
		}
		else
		{
			return redirect('streams')->with('status', 'Подписка не активна');
		}
	}
	
	public function edit(Request $request){
		
		$user_id = Auth::id();
		
		if(SubModel::is_active($user_id))
		{
			
			if($request->input('stream_id') && $request->input('action') === 'updata')
			{
				// Обновляем данные
				$data['stream_id'] 	= $request->input('stream_id');
				$data['user_id']	= $user_id;
				$data['title']		= $request->input('title');
				$data['safe_page']	= $request->input('safe_page');
				$data['offer_page']	= $request->input('offer_page');
				if(!empty($request->input('geo')))
					$data['geo']	= implode(', ', $request->input('geo'));
				else
					$data['geo']	= "";
				$data['on_off']		= (int)$request->input('on_off');
				$data['ipv6']		= (int)$request->input('ipv6');

				if(!empty($request->input('device')))
					$data['device']	= implode(',', $request->input('device'));
				else
					$data['device']	= "";
					
				$streams = $this->streams->update_stream($data);
				
				return redirect('streams')->with('success', "Поток обновлен");
			}
			elseif($request->get('stream_id'))
			{
				// Передаем данные для редактирования
				$stream_id	= $request->get('stream_id');
				
				$streams = $this->streams->select_stream_by_id( $stream_id, $user_id);
				
				$geo = $this->streams->create_geo_options( $streams[0]->geo );

				$device = $this->streams->create_device_options( $streams[0]->device );

				return view('edit',[
					'streams' => $streams,
					'geo' => $geo,
					'device' => $device
				]);

			}else{
				return redirect('streams');
			}
		}
		else
		{
			return redirect('streams')->with('status', 'Подписка не активна');
		}
	}
}
