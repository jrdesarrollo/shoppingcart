<?php
	namespace Components;
	
	class JR
	{
		
		public $mainfile;
		
		public function __get($attr)
		{
			return $this->attr;
		}
		
		public function __set($attr, $val)
		{
			$this->$attr = $val;
		}
		
		public function __construct($mainfile = 'main.php')
		{
			$this->mainfile = $mainfile;
			$this->getConfig();
		}
		
		/* Esta función convierte todos los parametros del archivo main.php en variables utilizables en el entorno de JR::app(), así que se podría usar la variable "TAX" que está en main.php al usarse el metodo estático app. JR::app()->tax;*/
		public function getConfig()
		{
			$path = dirname(__FILE__).'/'.$this->mainfile;
			if(file_exists($path)){
				$vars = require($path);
				foreach($vars as $attr => $val)
				{
					$this->__set($attr,$val);
				}
				
				$this->__set('session',new LocalData());
			}else{throw new \Exception('The file main.php was not found');}
		}
		
		public static function app()
		{
			$run = new JR();
			return $run;
		}
		
		
	}