<?php

class LinkAggregator {

	private $fb;
	
	public function __construct(Facebook $fb)
	{
		$this->fb	= $fb;
	}
	
	//given a sorted list of friends, return the top 30 links, sorted by date and popularity
	public function getLinks($friends) {
		
		$max_num = count($friends);
		$nums = array_fill(0,$num,false);
		$links = array();
		$i = 0;
		
		while($i < 50) {		
			$s = rand(0, $max_num);					//random person from list
			if(!$nums[$s]) {						//dont repeat person
				$nums[$s] = true;
				$friend = $friends[$s];
				//$i++;
				//print_r($friend);
				$link = $this->getFriendLinks($friend['uid']);
				//$i++;
				if(!empty($link)) {				//some people return nothing, randomly
					$links[$friend['uid']] = $link;
				 	echo('<p>' . $i . ': ' . $friend['name'] . '- ' . $links[$friend['uid']]['title'] . '</p>');
					$i++;
				}	
			}		
			
		}
		
		print_r($links);
		/*
		for($i = 0; $i < 40; $i++){
			
			$s = rand(0, $num);
			if($nums[$s]) {
				$nums[$s] = true;
				//echo($s);
				$friend = $friends[$s];
				//print_r($friend);
				$link = $this->getFriendLinks($friend['uid']);
				
				if(!empty($link)) {
					
				}
				
			} else $i--;
		}*/
		
		//for each friend, get past weeks worth of links
		//foreach($friends as $friend) {
			//echo($friend['name'] . '(' . $friend['uid'] . ') LINKS:');
		//	$this->getPastWeek($friend['uid'], $friend['name']);
			//echo('///////////////////////////');
		//}
		
		
		
		//for all generated links, rank by date/popularity
		
		//return top 30
		
	}
	
	//given a user id, return 1 link
	private function getFriendLinks($id) {
		

		
		$response = $this->fb->api(array(
			'method' => 'fql.query',
			'query' => 'SELECT title,created_time,url,summary FROM link WHERE owner = ' . $id . ' LIMIT 1'
			//'query' => 'SELECT title,created_time FROM link WHERE owner = ' . $id . ' AND now()-created_time < 604800'
		));
		
		//if(!empty($response['error'])) print_r($response['error']);
		
		if(empty($response['error']))
		{
			//echo($name);
			//print_r($response);
			
			$link = $response[0];
			//echo(': ' . $link['title'] . ' / ');
			return $link;
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
	
	//sort links from earliest > latest
	private function sortByDate($links) {

		usort($links, function($a, $b){			
			if($a['created_time'] == $b['created_time'])
			{
				return;
			}

			return $a['created_time'] > $b['created_time'] ? -1 : 1;
		});

		return $links;
	}
	

}

?>