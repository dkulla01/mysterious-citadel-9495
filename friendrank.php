<?php
/**
Copyright (c) 2012, Anuary (http://anuary.com/)
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:
    * Redistributions of source code must retain the above copyright
      notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright
      notice, this list of conditions and the following disclaimer in the
      documentation and/or other materials provided with the distribution.
    * Neither the name of the Anuary (http://anuary.com/) nor the
      names of its contributors may be used to endorse or promote products
      derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL ANUARY BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/
class AyFbFriendRank
{
	private $criteria					= array
	(
		'feed_like'						=> .2,
		'feed_comment'					=> .5,
		'feed_addressed'				=> .5,
		'photo_tagged_friend_by_user'	=> 1,
		'photo_tagged_user_by_friend'	=> 1,
		'photo_like'					=> .5,
		'photo_comment'					=> .5,
		'friend_mutual'					=> .125,
		'inbox_in_conversation'			=> 1,
		'inbox_chat'					=> .05
	);

	private $fb;
	private $me;
	private $permissions				= array();
	private $friends					= array();
	private $not_friends				= array();

	public function __construct(Facebook $fb)
	{
		$this->fb	= $fb;
	}

	public function setCriteriaWeight($name, $value)
	{
		if(!array_key_exists($name, $this->criteria))
		{
			throw new AyFbFriendRankException('Invalid criteria.');
		}

		$this->criteria[$name]	= (float) $value;
	}

	/* calls batch() to get friend data
	 *
	 *
	 *
	 *
	 */
	public function getFriends()
	{		
		$response	= $this->batch(array(
			'me'				=> 'fql?q=SELECT uid, birthday_date FROM user WHERE uid=me()',
			'friends'			=> 'fql?q=SELECT uid, name, birthday_date FROM user WHERE uid IN (SELECT uid2 FROM friend WHERE uid1 = me())',
			'mutual_friends'	=> 'fql?q=SELECT uid, mutual_friend_count FROM user WHERE uid IN (SELECT uid2 FROM friend WHERE uid1=me())',
			'feed'				=> 'fql?q=SELECT actor_id, target_id, likes, comments FROM stream WHERE source_id=me() LIMIT 500',
			'albums'			=> 'fql?q=SELECT aid FROM album WHERE owner=me()',
			'inbox'				=> 'fql?q=SELECT recent_authors,message_count FROM thread WHERE folder_id = 0 LIMIT 50',
			'inbox2'				=> 'fql?q=SELECT recent_authors,message_count FROM thread WHERE folder_id = 0 LIMIT 50 OFFSET 51',
			'inbox3'				=> 'fql?q=SELECT recent_authors,message_count FROM thread WHERE folder_id = 0 LIMIT 50 OFFSET 101'					
			//'inbox'				=> 'fql?q=SELECT participants,num_messages FROM unified_thread WHERE folder = "inbox" AND is_group_conversation = 0 LIMIT 100'
		));

		if(!empty($response['me']['error']))
		{
			throw new AyFbFriendRankException($response['me']['error']['type'] . ' thrown when trying to access /me data (' . $response['me']['error']['message'] . ').', $response['me']['error']['code']);
		}

		$this->me	= $response['me']['data'][0];

		// Create a zero-weight array for every criteria
		$weight_template	= array_combine(array_keys($this->criteria), array_fill(0, count($this->criteria), 0));

		foreach($response['friends']['data'] as $friend)
		{
			$this->friends[$friend['uid']]				= $friend;
			$this->friends[$friend['uid']]['weight']	= $weight_template;
		}
	
		//foreach($this->friends as $friend) {
		//	echo($this->friends[$friend['uid']]['name'] . '/');
		//}

		$t = 0;
		if(empty($response['inbox']['error']))
		{

			foreach($response['inbox']['data'] as $thread)
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
			print_r($response['inbox']['error']);
		}
		
		if(empty($response['inbox2']['error']))
		{

			foreach($response['inbox2']['data'] as $thread)
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
			print_r($response['inbox2']['error']);
		}

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
		}
		
		if(empty($response['mutual_friends']['error']))
		{
			foreach($response['mutual_friends']['data'] as $friend)
			{			
				$this->giveCriteriaScore($friend['uid'], 'friend_mutual', $friend['mutual_friend_count']);
			}
		}

		// handle photos
		$batch			= array();

		$batch[]		= 'me/photos?fields=from,tags,likes,comments';

		if(!empty($response['albums']['data']))
		{
			foreach($response['albums']['data'] as $album)
			{
				$batch[]	= $album['aid'] . '/photos?fields=from,tags,likes,comments';
			}
		}

		$photos			= array();

		foreach($this->batch($batch) as $album_photos)
		{
			if(!empty($album_photos['error']))
			{
				continue;
			}

			// avoid photo duplicates
			foreach($album_photos['data'] as $photo)
			{
				$photos[$photo['id']]	= $photo;
			}
		}

		foreach($photos as $photo)
		{
			$this->handlePhoto($photo);
		}

		// Regardles of whether `read_stream` permission is available we still have
		// access to user's feed.
		foreach($response['feed']['data'] as $feed)
		{
			$this->handleFeed($feed);
		}

		return $this->sortFriends();
	}

	//return friends 0-50
	public function getCloseFriends() {
		
		$close = array();
		$keys = array_keys($this->friends);
		//print_r($this->friends);
		//print_r($keys);
		for($i = 0; $i < 45; $i++) {
			//echo()
			$close[$i] = $this->friends[$i];
			//echo($i+1 . '. ' . $close[$i]['name'] );
		}
		
		return $close;
	}
	
	//return friends 50-150
	public function getMedFriends() {
		
		$med = array();
		for($i = 50; $i < 150; $i++) {
			$med[$i] = $this->friends[$i];
		}
		
		return $med;		
	}
	
	
	//return everyone else
	public function getFarFriends() {
	
		$far = array();
		for($i = 150; $i < count($this->friends); $i++) {
			//echo()
			$far[$i] = $this->friends[$i];
			echo($i+1 . '. ' . $far[$i]['name'] );
		}
		
		//return $far;		
	}

	public function sortFriends()
	{
		foreach($this->friends as &$friend)
		{
			$friend['score']	= 0;

			foreach($friend['weight'] as $k => $v)
			{
				$friend['score']	+= $this->criteria[$k]*$v;
			}
		}

		unset($friend);

		usort($this->friends, function($a, $b){			
			if($a['score'] == $b['score'])
			{
				return;
			}

			return $a['score'] > $b['score'] ? -1 : 1;
		});

		return $this->friends;
	}

	private function handleFeed($feed)
	{
		// Ignore comments and likes of posts made not by the user
		if($feed['actor_id'] == $this->me['uid'])
		{
			// Friends who liked my feed
			if(!empty($feed['likes']['friends']))
			{
				foreach($feed['likes']['friends'] as $friend_id)
				{
					$this->giveCriteriaScore($friend_id, 'feed_like');
				}
			}

			// Friends who commented on my feed
			if(!empty($feed['comments']['comment_list']))
			{
				foreach($feed['comments']['comment_list'] as $comment)
				{
					$this->giveCriteriaScore($comment['fromid'], 'feed_comment');
				}
			}
		}
		elseif($feed['target_id'] == $this->me['uid'])
		{
			$this->giveCriteriaScore($feed['actor_id'], 'feed_addressed');
		}
	}

	private function handlePhoto($photo)
	{
		if(!empty($photo['tags']))
		{
			foreach($photo['tags'] as $tag)
			{
				if(!empty($tag['id']))
				{
					$this->giveCriteriaScore($tag['id'], $photo['from']['id'] == $this->me['uid'] ? 'photo_tagged_friend_by_user' : 'photo_tagged_user_by_friend');
				}
			}
		}

		if(!empty($photo['likes']['data']))
		{
			foreach($photo['likes']['data'] as $like)
			{
				$this->giveCriteriaScore($like['id'], 'photo_like');
			}
		}

		if(!empty($photo['comments']['data']))
		{
			foreach($photo['comments']['data'] as $comment)
			{
				$this->giveCriteriaScore($comment['from']['id'], 'photo_comment');
			}
		}
	}

	private function giveCriteriaScore($friend_id, $action, $score = 1)
	{
		if(isset($this->friends[$friend_id]))
		{
			$this->friends[$friend_id]['weight'][$action]	+= (int) $score;
		}
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

class AyFbFriendRankException extends Exception {}