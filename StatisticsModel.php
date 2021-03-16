<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Statistics extends Model
{
	public $udata = array();
	
	protected $table = 'statistics';
	
    public function getStat($user_id, $user_stream, $limit = 100)
	{
		return DB::table($this->table)
		->where('user_id', $user_id)
		->where('user_stream', $user_stream)
		->orderByDesc('id')
		->limit($limit)
		->get();
	}

	public function getStatByDate($user_id, $stream_id, $datepiker, $pagi)
	{
		return DB::table($this->table)
		->select('*', DB::raw('DATE_FORMAT(date_time, "%H:%i:%s") as time'))
		->where('user_id', $user_id)
		->where('user_stream', $stream_id)
		->whereDate('date_time', $datepiker)
		->orderByDesc('id')
		->paginate($pagi);	
	}
	
	public function clicksByStreamId($user_id, $stream_id)
	{
		return DB::table($this->table)
		->select('id')
		->where('user_id', $user_id)
		->where('user_stream', $stream_id)
		->count();
	}

	public function clicksByStreamsIds($user_id, $streams)
	{	
		$streams_clicks = array();

		foreach($streams as $stream){
			$streams_clicks[$stream->stream_id] = $this->clicksByStreamId($user_id, $stream->stream_id);
		}

		return $streams_clicks;
	}

	public function clicksByDate($user_id, $stream_id, $date)
	{
		return DB::table($this->table)
		->select('id')
		->where('user_id', $user_id)
		->where('user_stream', $stream_id)
		->whereDate('date_time', $date)
		->count();
	}

	public function clicksBlocedByDate($user_id, $stream_id, $date)
	{
		return DB::table($this->table)
		->select('id')
		->where('user_id', $user_id)
		->where('user_stream', $stream_id)
		->whereDate('date_time', $date)
		->where('filter', '<>', '')
		->count();
	}

	public function setStat()
	{
		return DB::table($this->table)->insert([
			'user_id' 		=> $this->udata['user_id'], 
			'user_stream' 	=> $this->udata['user_stream'],
			'ip' 			=> $this->udata['ip'],
			'date_time' 	=> $this->udata['date_time'],
			'geo' 			=> $this->udata['geo'],
			'isp' 			=> $this->udata['isp'],
			'user_agent' 	=> $this->udata['user_agent'],
			'is_mobile' 	=> $this->udata['is_mobile'],
			'referer' 		=> $this->udata['referer'],
			'filter' 		=> $this->udata['filter'],
			'redirect_page' => $this->udata['redirect_page']
		]);
	}

	public function clearStatsByStreamId($user_id, $user_stream)
	{
		DB::table($this->table)->where('user_id', '=', $user_id)->where('user_stream', '=', $user_stream)->delete();
	}
}
