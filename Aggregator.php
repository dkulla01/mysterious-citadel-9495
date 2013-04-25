<?php

class LinkAggregator {

	private $fb;
	
	public function __construct(Facebook $fb)
	{
		$this->fb	= $fb;
	}
	
	//given a sorted list of friends, return the top 30 links, sorted by date and popularity
	public function getLinks($friends) {
		
		//for each friend, get past weeks worth of links
		foreach($friends as $friend) {
			//echo($friend['uid']);
			$this->getPastWeek($friend['uid']);
		}
		
		//for all generated links, rank by date/popularity
		
		//return top 30
		
	}
	
	//given a user id, return past weeks worth of links
	public function getPastWeek($id) {
		
		 //echo($id);
		
		$response = $this->fb->api(array(
			'method' => 'fql.query',
			'query' => 'SELECT title,created_time FROM link WHERE owner = ' . $id . ' AND now()-created_time < 604800'
		));
		
		//if(!empty($response['error'])) print_r($response['error']);
		
		print_r($links);
		//echo('//////////');
	}
	
	

}

?>