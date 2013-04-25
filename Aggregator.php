<?php

class LinkAggregator {

	private $fb;
	
	public function __construct(Facebook $fb)
	{
		$this->fb	= $fb;
	}
	
	//given a sorted list of friends, return the top 30 links, sorted by date and popularity
	public function getLinks($friends) {
		
		$num = count($friends);
		
		for($i = 0; $i < 5; $i++){
			
			$s = rand(0, $num);
			echo($s);
			$friend = $friends[$s];
			//print_r($friend);
			$this->getPastWeek($friend['uid'], $friend['name']);
		}
		
		//for each friend, get past weeks worth of links
		//foreach($friends as $friend) {
			//echo($friend['name'] . '(' . $friend['uid'] . ') LINKS:');
		//	$this->getPastWeek($friend['uid'], $friend['name']);
			//echo('///////////////////////////');
		//}
		
		
		
		//for all generated links, rank by date/popularity
		
		//return top 30
		
	}
	
	//given a user id, return past weeks worth of links
	public function getPastWeek($id, $name) {
		

		
		$response = $this->fb->api(array(
			'method' => 'fql.query',
			'query' => 'SELECT title,created_time FROM link WHERE owner = ' . $id . ' LIMIT 1'
			//'query' => 'SELECT title,created_time FROM link WHERE owner = ' . $id . ' AND now()-created_time < 604800'
		));
		
		//if(!empty($response['error'])) print_r($response['error']);
		
		if(empty($response['error']))
		{
			//echo($name);
			print_r($response);
			//echo(' / ');
			//foreach($response['data'] as $link)
			//{
				//print_r($link);
				
				//echo($name . ': ' . $link['title'] . ' / ');
			//}
		} else {
			print_r($response['error']);
		}		
		//if(empty($response)) { echo($name . '/'); }
		//print_r($response);
		//echo('//////////');
	}
	
	

}

?>