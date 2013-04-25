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
			echo($friend['name'] . '(' . $friend['uid'] . ') LINKS:');
			$this->getPastWeek($friend['uid'], $friend['name']);
			echo('///////////////////////////');
		}
		
		//for all generated links, rank by date/popularity
		
		//return top 30
		
	}
	
	//given a user id, return past weeks worth of links
	public function getPastWeek($id, $name) {
		
		 //echo($id);
		
		$response = $this->fb->api(array(
			'method' => 'fql.query',
			'query' => 'SELECT title,created_time FROM link WHERE owner = ' . $id
			//'query' => 'SELECT title,created_time FROM link WHERE owner = ' . $id . ' AND now()-created_time < 604800'
		));
		
		//if(!empty($response['error'])) print_r($response['error']);
		/*
		if(empty($response['inbox3']['error']))
		{

			foreach($response['inbox3']['data'] as $thread)
			{
				$t++;			
				foreach($thread['recent_authors'] as $author) {
					if(!empty($this->friends[$author])) {				//filter out user
						$friend = $author;
						
						if(count($thread['recent_authors']) > 2) {
							$this->giveCriteriaScore($friend, 'inbox_in_conversation', 0.25);
							$this->giveCriteriaScore($friend, 'inbox_chat', $thread['message_count'] * 0.125);							
						}
						else {
							$this->giveCriteriaScore($friend, 'inbox_in_conversation');
							$this->giveCriteriaScore($friend, 'inbox_chat', $thread['message_count']);							
						}
					}
				}
			}
		} else {
			print_r($response['inbox3']['error']);
		}	*/	
		if(empty($response)) { echo($name . '/'); }
		//print_r($response);
		//echo('//////////');
	}
	
	

}

?>