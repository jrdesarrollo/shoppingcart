<?php
	namespace Controllers;
	use \Models\Cart;
	use \Models\Products;
	use \Components\JR;
	
	class CartController
	{
		public $notifications = [];
		
		/*Se añade el id del producto y la cantidad, ambos son pasados al metodo SaveData que se encuentra en el modelo y se encargara de almacenar los datos en una sesion */
		public function addToCart($idProduct,$quantity)
		{
			if(Products::model()->getProductById($idProduct)){	
				$toCart = new \stdClass();
				$toCart->idProduct 	= $idProduct;
				$toCart->quantity 	= $quantity;
				
				Cart::model()->saveData($toCart);
			}else{
				throw new \Exception('The product that you tried to add does not exists.');
			}
			
			return true;
		}
		
		public function removeToCart($idProduct)
		{
			$cartinfo = $this->loadProductInCart($idProduct,"The product that you are trying to remove is not in the cart ({$idProduct})");
			$cart = Cart::model()->cart;
			array_splice($cart,$cartinfo->indexKey,1);
			Cart::model()->session->set('cartInfo',$cart);
			Cart::model()->cart = $cart;
		}
		
		public function getItems()
		{
			return Cart::model()->cart;
		}
		
		
		/* Se obtienen los descuentos totales por cada producto singular. 
		La variable general sirve para especificar si se desea que retorne la suma total del producto con su descuento (de tenerlo) multiplicado por la cantidad de unidades solicitadas. 
		De cambiarse a false, devuelve el precio con descuento (de tenerlo) de una sola unidad */
		public function getDiscount($idProduct,$general = true)
		{
			$obj = $this->loadProductInCart($idProduct);
			$price = 0;
			switch($obj->Info->discountType)
			{		
				case 'none':
					$price = $obj->Info->price;
				break;		
				
				case 'price':
					$price = $obj->Info->discountPrice;
				break;
				
				case 'quantity':
					if(is_array($obj->Info->discountRule))
					{
						$wichRule = [];
					
						foreach($obj->Info->discountRule as $k => $rule)
						{
							if($this->checkOperation($rule,$obj->quantity))
								array_push($wichRule,$obj->Info->discountPrice[$k]); 
						}
						
						$price = (count($wichRule) > 0) ? min($wichRule) : $obj->Info->price;
					}
				break;
			}
			$qt = $obj->quantity;
			return ($general) ? (floatval($price) * intval($obj->quantity)) : $price;
		}
		
		/*Con este metodo de retorna el precio tanto por cada unidad como por general (La multiplicado del precio de cada unidad por la cantidad requerida) incluyendo el impuesto especificado en el archivo main.php en /Folder/Components/ el cual es llamado mediante JR::app() */
		public function getTax($idProduct,$general = true)
		{
			$obj = $this->loadProductInCart($idProduct);
			$amount =	$this->getDiscount($idProduct,$general);
			$tax = (JR::app()->tax/100);
			$totalAmount = ( ($amount * $tax) + $amount );
			return $totalAmount;
		}
		
		/* Imprime cada celda de cada producto agregado al carrito */
		public function getDetailOfCurrentCart()
		{
			$string = '';
			foreach($this->getItems() as $k => $show){

				$string .= 'ID: ' . $show->idProduct . ' | Nombre: ' . $show->Info->name . ' | Cantidad: ' . $show->quantity . ' | Precio unitario: ' . $show->Info->price . JR::app()->currency . ' | Descuento: ' . $show->Info->discountType;
				
				if($show->Info->discountType <> 'none')
					$string .= ' | Precio Descuento: ' . $this->getDiscount($show->idProduct,false) . ' | Precio descuento + Impuesto: ' . $this->getTax($show->idProduct,false);
				
				$string .= ' | Precio total sin Impuesto: ' . $this->getDiscount($show->idProduct) . ' | Precio total con impuesto: ' . $this->getTax($show->idProduct);
				
				$string .= '<br>';
			}
			
			return $string;
		}
		
		/* Imprime la totalidad de los montos que deben pagarse: cantidad de productos - subtotal (Sin IGV) - Total (Con IGV) */
		public function getTotalAmounts($print = true)
		{
			$totals = new \stdClass;
			$totals->subTotal = 0;
			$totals->total = 0;
			$totals->products = 0;
			foreach($this->getItems() as $k => $prod)
			{
				$totals->products++;
				$totals->subTotal = ($totals->subTotal + $this->getDiscount($prod->idProduct));
				$totals->total = ($totals->total + $this->getTax($prod->idProduct));
			}
			
			$string = 'Total Productos: ' . $totals->products . ' | Monto a pagar: ' . $totals->subTotal . JR::app()->currency . ' | Monto total (+ IGV): ' . $totals->total . JR::app()->currency;
			return ($print) ? $string : $totals->total;
		}
		
		/* Este metodo simula la accion de pagar. Descuenta el stock del archivo .json que almacena la informacion de los productos.
			Si se especifica un ID pagará solo ese producto, por lo que el bucle que evalúa cada producto en el carrito saltará al siguiente si detecta que existe un ID especificado y el actual en el bucle no es el indicado. 
			Si la cantidad de items requeridos es mayor a la que existe en .json, el metodo salta al siguiente producto y añade un mensaje de notificacion */
		public function payForTheCart($id = null)
		{
			$string = '';
			$productList = $this->getItems();
			if(count($productList) > 0){
				foreach($productList as $k => $prod)
				{
					if(!is_null($id) && $prod->idProduct <> $id)
						continue;
					
					if($prod->Info->quantity < $prod->quantity)
					{
						$notifications[] = 'The product ID: '.$prod->idProduct.' '.$prod->Info->name.' has not stock for now';
						continue;
					}
					
					$currentStock = ($prod->Info->quantity - $prod->quantity);
					$products = new ProductsController;
					$products->editProduct($prod->idProduct,['quantity'=>$currentStock]);
					$this->removeToCart($prod->idProduct);
				}
			}else{return false;}
			
			if(count($this->notifications) > 0)
			{
				$string = implode('<br>',$this->notifications);
				echo $string;
			}
			
			return true;
		}
	
		/* Este metodo evalúa el id que se le especifica para un producto en el carrito. Si no lo encuentra, arrojará una Excepción con el string que se le especifique */
		public function loadProductInCart($id,$exceptionMessage = 'You have selected an incorrect product')
		{
			if($obj = Cart::model()->findProductInCart($id))
			{
				return $obj;
			}else if(!is_string($exceptionMessage)){
				throw new \Exception('You have to use an string for the exception message');
			}else{ throw new \Exception($exceptionMessage);}
		}
		
		/* Este metodo utiliza eval para evaluar la string de la condicional del descuento. Se le pasa la cantidad de items pedidos (Ej: 10) y un string con la regla (Ej: >=10 lo que significaría que la regla debe aplicarse si el pedido es mayor o igual a 10) entonces queda el string "return (40 >= 10); " que devolverá true o false al utilizarse Eval() */
		private function checkOperation($string,$quantity)
		{
			$operation = "return ({$quantity} {$string});";
			return eval($operation);
		}
	}