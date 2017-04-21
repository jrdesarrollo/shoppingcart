<?php
	class Autoloader
	{
		public static function init()
		{
			spl_autoload_register(function($class){
				$path = str_replace('\\','/',$class).'.php';
				if(is_readable($path))
					include $path;
				else
					echo 'The file: '.$path.' wasn\'t located';
			}, true, false);
			
			\Components\JR::app()->session->init();
		}
	}
	
	Autoloader::init();