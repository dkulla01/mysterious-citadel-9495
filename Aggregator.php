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
	
	public function getMyLinks() {
		$response = $this->fb->api(array(
			'method' => 'fql.query',
			'query' => 'SELECT title,created_time,url,summary,picture FROM link WHERE owner = me()'
			//'query' => 'SELECT title,created_time FROM link WHERE owner = ' . $id . ' AND now()-created_time < 604800'
		));

		if(empty($response['error']))
		{
			if(!empty($response)) {
				//print_r($response);
				return $response;	
			} 
		} else {
			print_r($response['error']);
		}			
	}
	
	//given a sorted list of friends, return the top 30 links, sorted by date and popularity
	public function getLinks($friends, $relationship) {
		
		if(!empty($friends)) {
		
			$max_num = count($friends);
			//if($relationship == 'big') { echo $max_num; }
			//echo($max_num);
			$nums = array_fill(0,$max_num,false);
			$i = 0;
			
			//echo $max_num;
			
			while($i < 15) {		
				$s = rand(0, $max_num);					//random person from list
				if(!$nums[$s]) {						//dont repeat person
					
					$nums[$s] = true;
					$friend = $friends[$s];
					//echo $s . ': ' . $friend['uid'] . ' / ';
					//$i++;
					
					$link = $this->getFriendLinks($friend['uid']);
					
					if(!empty($link) && !$this->containsLink($link['title'])) {				//some people return nothing, randomly
						$link['name'] = $friend['name'];
						$link['class'] = $relationship;
						$this->links[$friend['uid']] = $link;
					 	//echo('<p>' . $i . ': ' . $friend['name'] . '- ' . $links[$friend['uid']]['title'] . '</p>');
						$i++;
					}
				}			
			}
		}  // !empty($friends)
	}
	
	//given a user id, return 1 link
	private function getFriendLinks($id) {
		
		if(!empty($id)) {
			$response = $this->fb->api(array(
				'method' => 'fql.query',
				'query' => 'SELECT title,created_time,url,summary,picture FROM link WHERE owner = ' . $id . ' LIMIT 1'
				//'query' => 'SELECT title,created_time FROM link WHERE owner = ' . $id . ' AND now()-created_time < 604800'
			));
	
			if(empty($response['error']))
			{
				if(!empty($response)) {
					$link = $response[0];
				} 
				return $link;
			} else {
				print_r($response['error']);
			}
	  }		
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