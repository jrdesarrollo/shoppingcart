<?php
	namespace Controllers;
	use \Models\Products;
	
	class ProductsController
	{
		/*Funcion de añadir productos, recibe los mismos parametros y crea el array que se le pasa a SaveProduct*/
		public function addProduct($name,$price,$quantity,$discountType = "none",$discountPrice = null,$discountRule = "none")
		{
			
			if(Products::model()->validateProduct($name,$price,$quantity,$discountType,$discountPrice,$discountRule)){
			
				$obj = [
					'id'			=>	null,
					'name'			=>	$name,
					'price'			=>	$price,
					'quantity'		=>	$quantity,
					'discountType'	=>	strtolower($discountType),
					'discountRule'	=>	$discountRule,
					'discountPrice'	=>	$discountPrice,
				];
				
				return Products::model()->saveProduct($obj);
			}
		}
		
		/*Funcion para editar un producto, se recibe el id del mismo y un array con los valores cambiados. No hace falta especificarlos todo, solo un array con los valores a editar. Ej: ['name'=>'nuevo nombre']. El ID no se puede cambiar */
		public function editProduct($id, $changes = array())
		{
			if(is_array($changes))
			{
				if(isset($changes['id']))
					unset($changes['id']);
				
				$arr = Products::model()->getProductById($id);
				foreach($changes as $k => $val)
				{
					$arr->$k =$val;
				}
				
				if(Products::model()->validateProduct(
					$arr->name,
					$arr->price,
					$arr->quantity,
					$arr->discountType,
					$arr->discountPrice,
					$arr->discountRule
				))
					return Products::model()->saveProduct($arr);
				
			}else{ throw new \Exception('The object $arr has received some incorrect value'); }
		}
		
		/* Funcion que elimina un producto del listado, solo requiere del ID. Cualquier parametro diferente de un numerico hará que la funcion falle y devuelva false. Lo mismo pasará si se provee un ID que no está almacenado */
		public function removeProduct($id)
		{
			$products = Products::model()->getProducts();
			
			if($indexVal = Products::model()->getProductById($id))
			{
				$indexVal = $indexVal->indexKey;
				array_splice($products,$indexVal,1);			
				return Products::model()->saveDataInFile($products);
			}
			
			return false;
		}
		
		public function showCatalog()
		{
			$products = Products::model()->getProducts();
			foreach($products as $k => $product)
			{
				$price = ($product->discountType == 'price') ? '<del>'.$product->price.'</del> <span style="color:red">'.$product->discountPrice.'</span>' : $product->price;
				echo '#'.$product->id.' '.$product->name.' | Stock: '.$product->quantity.' | Price: ' .$price.'<br>';
			}
		}
	}
	