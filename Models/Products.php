<?php
	namespace Models;
	use \Components\JR;
	
	class Products
	{		
		public function __get($attr)
		{
			return $this->$attr;
		}
		
		public function __set($attr,$val){
			$this->$attr = $val;
		}
		
		private $datafile, $data_path;
		
		public function __construct($datafile = null)
		{
			$this->data_path = JR::app()->basePath.'/Data/';
			$this->datafile = (is_null($datafile)) ? JR::app()->productsPath : $datafile;
		}
		
		/*Funcion para devolver todos los productos en data/products.json*/
		public function getProducts()
		{
			$path = $this->data_path.$this->datafile;
			if(is_readable($path))
				$json = json_decode(file_get_contents($path));
			else
				throw new \Exception('Data file: '.$path.' wasn\'t located');
			
			if(empty($json))
				$json = json_decode(json_encode([]));

			return $json;
		}
		
		/*Función para obtener un solo producto desde el archivo .json*/
		public function getProductById($id) 
		{
			foreach ($this->getProducts() as $k => $product) {
				if ($product->id === $id){
					$product->indexKey = $k;
					return $product;
				}
			 }

			return false;
			
		}
		
		/*Obtener el mayor ID registrado en el archivo .json. Esto para llevar un control incremental en los productos almacenados*/
		public function getLastId()
		{
			$arr = $this->getProducts();
			$max = 0;
			foreach($arr as $obj)
			{
				if($obj->id > $max)
				{
					$max = $obj->id;
				}
			}
			
			return intval($max);
		}
		
		/*Función validadora para el campo "DiscountRule" que pide que se añada de manera dinamica un operador para saber bajo cual regla aplicar un descuento */
		private function checkIfIsValidOperator($haystack, $needles=array())
		{
			return in_array($haystack,$needles);
		}
		
		/*Funcion hecha para evitar repetir la llamada de la ultima funciones cada vez que hace falta con todo y condicionales y permitir cambiarlas en caso de que en algún momento haga falta */
		private function checkDiscountRules($rule, $allow = ['>=','<=','==','>','<'])
		{
			if(!($this->checkIfIsValidOperator(str_replace(['1','2','3','4','5','6','7','8','9','0',' '], '', $rule), $allow)))
				return true;
			
			return false;
		}
		
		
		/*Funcion final de salvado de datos que recibe un array el cual convierte a formato json y lo almacena en la ruta especificada. Se hace para evitar la repetición de código.*/
		public function saveDataInFile($json)
		{
			foreach($json as $k => $j)
			{
				if(isset($j->indexKey))
					unset($json[$k]->indexKey);
			}
			
			$string = json_encode($json);
			$path = $this->data_path.$this->datafile;
			if(is_readable($path))
			{
				return (is_numeric(file_put_contents($path,$string)));
			}
			
			return false;
		}
		
		/*Función que se comparte para generar el array que se envia a SaveDataInFile, si el array que recibe ya posee una ID identifica que debe "editar", y si el id es nulo, lo crea y almacena el nuevo registro en el archivo .json */
		public function saveProduct($arr)
		{
			$products = $this->getProducts();
			
			if(!is_object($arr))
			{
				$arr['id'] = ($this->getLastId() + 1);
				array_push($products,$arr);
			}
			else{
				$indexVal = $this->getProductById($arr->id)->indexKey;
				$products[$indexVal] = $arr;
			}
			
			return $this->saveDataInFile($products);
		}
		
		/* Funtión básica de validar productos. Resuelve todas las validaciones necesarias para añadir un producto correctamente.
		ASPECTOS A TOMAR EN CUENTA:
		Tipos de discountType: 
		- none (No tiene descuento el producto) 
		- price (El descuento es sencillo, tiene un precio viejo pero ahora se quiere utilizar es el de descuento sin mas) 
		- quantity (El mas complejo, aplica reglas para determinar a partir de cual cantidad se aplica cual descuento, permite varias reglas y cada una debe tener un precio, en este caso, dichas variables se rellenan como Arrays y deben haber tantos precios como reglas)
		
		price - discountPrice son valores flotantes que reciben integrales también. Pueden recibir valores numericos en strings a menos que en la string hayan letras, en ese caso arrojará una excepción. Estos valores deben ser ingresados sin impuesto agregado, ya que el sistema agrega el impuesto automaticamente luego
		
		Si discountType es "none" y se añade un discountPrice, este sera ignorado.
		*/
		public function validateProduct($name,$price,$quantity,$discountType = "none",$discountPrice = null,$discountRule = "none")
		{
			if(empty(trim($name)) || !is_numeric($price) || !is_int($quantity)){
				throw new \Exception('Fields to add an product are wrong');
			}
			
			if(strtolower($discountType) <> 'none')
			{
				$message = '';
				if(!in_array($discountType,['quantity','none','price']))
					$message = 'The discount type that you have selected is wrong';
				
				if($discountType == 'quantity')
				{
					if(!is_array($discountRule)){
						if($this->checkDiscountRules($discountRule))
							$message = 'You are using an invalid operator or you\'re not specifying some rule';
					}else{
						foreach($discountRule as $k => $rule)
						{
							if($this->checkDiscountRules($rule))
								$message = 'The rule number '.($k+1).' is using an invalid operator';
						}
						
						if(count($discountRule) <> count($discountPrice))
							$message = 'You have to specify a price for every discount rule';
					}
				}else{
					if(is_array($discountPrice))
						$message = 'Yuo cannot use more than one discount price unless your discountType is by quantity';
				}
				
				if((is_null($discountPrice) || !is_numeric($discountPrice)) && !is_array($discountPrice))
					$message = 'You have to specify some valid discount for this product';
				
				if(!empty($message))
					throw new \Exception($message);
			}
			
			return true;
		}
		
		public static function model($className=__CLASS__)
		{
			return new $className;
		}
	}