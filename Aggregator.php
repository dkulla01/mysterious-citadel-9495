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
		
		
		//for all generated links, rank by date/popularity
		
		//return top 30
		
	}
	
	//given a user id, return past weeks worth of links
	public function getPastWeek($id) {
		
		$app_using_friends = $fb->api(array(
			'method' => 'fql.query',
			'query' => 'SELECT uid, name FROM user WHERE uid IN(SELECT uid2 FROM friend WHERE uid1 = me()) AND is_app_user = 1'
		));
	}
	
	

}

?>