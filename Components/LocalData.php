<?php
	namespace Components;
	
	class LocalData
	{
		public function init()
		{
			if(!isset($_SESSION)){
				session_start();
			}
		}
		
		public function get($attr)
		{
			if(isset($_SESSION[$attr]))
				return $_SESSION[$attr];
			
			return false;
		}
		
		public function set($attr,$val)
		{
			$_SESSION[$attr] = $val;
		}
		
		public function remove($attr)
		{
			unset($_SESSION[$attr]);
		}
		
		public function endSession()
		{
			session_destroy();
		}
		
		
	}