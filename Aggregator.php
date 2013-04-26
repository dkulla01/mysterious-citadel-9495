<?php

class LinkAggregator {

	private $fb;
	
	private $links = array();
	
	public function __construct(Facebook $fb)
	{
		$this->fb	= $fb;
	}
	
	public function tester() {
		return $this->links;
	} 
	
	//given a sorted list of friends, return the top 30 links, sorted by date and popularity
	public function getLinks($friends, $relationship) {
		print_r($friends);
		echo('////////////////////////////');
		/*
		$max_num = count($friends);
		$nums = array_fill(0,$max_num,false);
		$i = 0;
		
		while($i < 1) {		
			$s = rand(0, $max_num);					//random person from list
			if(!$nums[$s]) {						//dont repeat person
				$nums[$s] = true;
				$friend = $friends[$s];
				//$i++;
				//print_r($friend);
				echo $friend['uid'] . ' / ';
				
				//$link = $this->getFriendLinks($friend['uid']);
				
				//$i++;
				if(!empty($link) && !$this->containsLink($link['title'])) {				//some people return nothing, randomly
					$link['name'] = $friend['name'];
					$link['class'] = $relationship;
					$this->links[$friend['uid']] = $link;
				 	//echo('<p>' . $i . ': ' . $friend['name'] . '- ' . $links[$friend['uid']]['title'] . '</p>');
					$i++;
				}	
			}		
			
		}*/

		//$newlinks = $this->sortByDate($links);
		//return $newlinks;
		
		
		/*
		$i = 0;
		foreach($newlinks as $link) {
			echo('<p>' . $i . ': ' . $link['title'] . ' (' . date("Y-m-d\TH:i:s\Z",$link['created_time'])  . ')</p>');
			$i++;
		}*/
		
		
		
		//print_r($links);
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
			'query' => 'SELECT title,created_time,url,summary,image_urls FROM link WHERE owner = ' . $id . ' LIMIT 1'
			//'query' => 'SELECT title,created_time FROM link WHERE owner = ' . $id . ' AND now()-created_time < 604800'
		));
		
		//if(!empty($response['error'])) print_r($response['error']);
		
		if(empty($response['error']))
		{
			//echo($name);
			//print_r($response);
			if(!empty($response)) {
				$link = $response[0];
				//echo(': ' . $link['title'] . ' / ');
				
			} 
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
	
	//return true if the string contains a link
	private function containsLink($s) {
		preg_match('/[a-zA-Z]+:\/\/[0-9a-zA-Z;.\/?:@=_#&%~,+$]+/', $s, $matches);
		return !empty($matches);
	}
	
	public function getSortedLinks() {
		return $this->sortByDate($this->links);
	}
	
	//sort links from earliest > latest
	private function sortByDate($links) {

		uasort($links, function($a, $b){			
			if($a['created_time'] == $b['created_time'])
			{
				return;
			}

			return $a['created_time'] > $b['created_time'] ? -1 : 1;
		});

		return $links;
	}
	
	/**
	 * A helper method to make Graph requests in batches.
	 * The result array preservers original keys.
	 */
	private function batch($requests)
	{
		foreach(array_chunk($requests, 50, TRUE) as $chunk)
		{
			$batch		= array();

			foreach($chunk as $request)
			{
				if(is_array($request))
				{
					$batch[]	= array('method' => 'GET') + $request;
				}
				else
				{
					$batch[]	= array('method' => 'GET', 'relative_url' => $request);
				}
			}

			$original_keys	= array_keys($chunk);

			$response		= $this->fb->api(NULL, 'POST', array('batch' => $batch));

			foreach($response as $i => $data)
			{				
				$result[$original_keys[$i]]	= json_decode($data['body'], TRUE);
			}
		}

		return $result;
	}
}

?>