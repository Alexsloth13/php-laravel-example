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
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request, Statistics $stat, StreamsModel $streams)
    {
		$user_id = Auth::id();
		
		// Удалить поток
		if ($request->get('stream_id') && $request->get('action') === 'trash') {
			if (!SubModel::is_active($user_id)) {
				return redirect('streams')->with('status', 'Подписка не активна');
			}

			$stream_id = $request->get('stream_id');
			// Удаляем статистику потока
			$stat->clearStatsByStreamId($user_id, $stream_id);
			// Удаляем поток
			$streams->delete_stream($stream_id, $user_id);	
		}

		// Вывести список всех потоков
		$all_streams = $streams->select_streams( $user_id );
		// Возвращает массив 'stream_id' => count_clicks(145)
		$clicks = $stat->clicksByStreamsIds($user_id, $all_streams);

        return view('streams', ['streams' => $all_streams, 'clicks' => $clicks]);
    }
	
	public function add(Request $request, StreamsModel $streams)
	{
		$user_id = Auth::id();

		// Проверка подписки
		if (!SubModel::is_active($user_id)) {
			return redirect('streams')->with('status', 'Подписка не активна');	
		}

		// Добавляем проверку количества разрешенныых потоков
		$streams_count = $streams->streams_count($user_id);
		$allowed_streams_count = SubModel::allowed_streams_count($user_id);

		// Если потоков >= количеству разрешенных потоков, блокируем добавление новых потоков
		if ($streams_count >= $allowed_streams_count) {
			return redirect('streams')->with('status', "Привышение лимита потоков: макс. $allowed_streams_count");
		}
		
		// Проверяем основные данные
		if (empty($request->input('title')) && empty($request->input('safe_page')) && empty($request->input('offer_page'))) {
			$geo = $streams->create_geo_options('');
			$device = $streams->create_device_options('');
			return view('edit', ['geo' => $geo, 'device' => $device]);	
		}

		// Инициализируем данные
		$data['user_id'] = $user_id;
		$data['title'] = $request->input('title');
		$data['safe_page'] = $request->input('safe_page');
		$data['offer_page'] = $request->input('offer_page');
		$data['on_off'] = (int)$request->input('on_off');
		$data['ipv6'] = (int)$request->input('ipv6');
		$data['geo'] = empty($request->input('geo')) ? '' : implode(', ', $request->input('geo'));
		$data['device'] = empty($request->input('device')) ? '' : implode(',', $request->input('device'));
		// Добавляем поток
		$streams->insert_stream( $data );

		return redirect('streams')->with('success', "Поток создан, установите код интеграции.");
	}
	
	public function edit(Request $request, StreamsModel $streams)
	{
		$user_id = Auth::id();

		// Проверка подписки
		if (!SubModel::is_active($user_id)) {
			return redirect('streams')->with('status', 'Подписка не активна');
		}
		
		// Вернем пусную страничку, если не задан stream_id
		if (empty($request->get('stream_id'))) {
			return redirect('streams');
		}

		// Передаем данные для редактирования
		if ($request->input('stream_id') && $request->input('action') !== 'updata') {
			$stream_id	= $request->get('stream_id');	
			$stream = $streams->select_stream_by_id($stream_id, $user_id);
			$geo = $streams->create_geo_options($stream[0]->geo);
			$device = $streams->create_device_options($stream[0]->device);
			return view('edit',['streams' => $stream, 'geo' => $geo, 'device' => $device]);
		} else {
			// Обновляем данные
			$data['stream_id'] = $request->input('stream_id');
			$data['user_id'] = $user_id;
			$data['title'] = $request->input('title');
			$data['safe_page'] = $request->input('safe_page');
			$data['offer_page'] = $request->input('offer_page');
			$data['on_off'] = (int)$request->input('on_off');
			$data['ipv6'] = (int)$request->input('ipv6');
			$data['geo'] = empty($request->input('geo')) ? '' : implode(', ', $request->input('geo'));
			$data['device'] = empty($request->input('device')) ? '' : implode(',', $request->input('device'));
			// Обновим поток
			$streams->update_stream($data);
			return redirect('streams')->with('success', "Поток обновлен");
		}
	}	
}
