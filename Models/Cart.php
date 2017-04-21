<?php
	namespace Models;
	use \Components\JR;
	
	class Cart
	{
		private $session,$cart;
		
		public function __get($attr)
		{
			return $this->$attr;
		}
		
		public function __set($attr,$val){
			$this->$attr = $val;
		}
		
		/*	Para manejo mas facil se utiliza la variable $this->session para manejar JR::app()->session
			Se llama a la variable de session cartInfo, de no existis, se especifica que $this->cart sea un array */
		public function __construct()
		{
			$this->session = JR::app()->session;
			
			if($this->session->get('cartInfo'))
				$this->cart = $this->session->get('cartInfo');
			else
				$this->cart = [];
		}
		
		
		public function findProductInCart($idProduct)
		{
			foreach ($this->cart as $k => $product) {
				if ($product->idProduct === $idProduct){
					$product->indexKey = $k;
					$product->Info = Products::model()->getProductById($product->idProduct);
					return $product;
				}
			 }

			return false;
		}
		
		/*
			Este metodo se encarga de buscar la informacion completa del producto que se le está pasando generando una propiedad ->Info sobre el objeto base que contiene lo que retorna findProductInCart() 
			
			Evalúa si la cantidad de inventario solicitado al añadir el producto al carrito es mayor a la existente en stock y devuelve una excepción si lo es.
			
			Analiza si el producto que intenta añadirse al carrito ya existe, y de existir, suma la cantidad requerida a la última conocida. De no existir, lo agrega como uno nuevo. En caso de que se esté añadiendo un producto que ya existía y la suma de las cantidades sea mayor a la del inventario, agrega la cantidad total existente en stock.
		*/
		public function saveData($obj)
		{
			if(!property_exists($obj,"idProduct") || !property_exists($obj,"quantity"))
				throw new \Exception("You are passing an invalid object");
			
			$actualQuantity = Products::model()->getProductById($obj->idProduct)->quantity;
			if($actualQuantity < $obj->quantity)
				throw new \Exception('You are trying to buy more inventory than is available');
			
			if($cartinfo = $this->findProductInCart($obj->idProduct))
			{
				$obj->quantity = ($cartinfo->quantity + $obj->quantity);
				if($actualQuantity < $obj->quantity)
				{
					$obj->quantity = $actualQuantity;
				}
				$obj->Info = $cartinfo->Info;
				$index = $cartinfo->indexKey;
				$this->cart[$index] = $obj;
			}else{
				$cart = $this->cart;
				$obj->Info = Products::model()->getProductById($obj->idProduct);
				$cart[] = $obj;
				$this->cart = $cart;
			}
			
			$this->session->set('cartInfo',$this->cart);
			
			return true;
		}
		
		public static function model($className=__CLASS__)
		{
			return new $className;
		}
	}