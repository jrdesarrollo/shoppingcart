<?php
	use \Models\Cart;
	use \PHPUnit\Framework\TestCase;
	
	class CartTest extends TestCase
	{
		public function testFailedGetProductInCart()
		{
			$this->assertFalse(Cart::model()->findProductInCart('Hola'));
		}
		
		public function testFailedGetProductInCartWithArray()
		{
			$this->assertFalse(Cart::model()->findProductInCart([1]));
		}
		
		public function testTryToSaveAProductInCartWithInvalidObject()
		{
			$obj = new stdClass;
			$obj->id = 1;
			$obj->name = 'Hola';
			$this->expectException(\Exception::class);
			
			Cart::model()->saveData($obj);
		}
		
		public function testTryToAddMoreInventoryThanIsAvailable()
		{
			$obj = new stdClass;
			$obj->idProduct = 1;
			$obj->quantity = 20000;
			$this->expectException(\Exception::class);
			
			Cart::model()->saveData($obj);
		}
		
	}