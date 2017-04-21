<?php
	use \Controllers\CartController as CartCont;
	use \Models\Cart;
	use \Models\Products;
	use \PHPUnit\Framework\TestCase;
	
	class CartControllerTest extends TestCase
	{
		public function testTryToAddMoreInventoryThanIsAvailable()
		{
			$this->expectException(\Exception::class);
			$obj = new CartCont;
			$obj->addToCart(Products::model()->getLastId(),200000);
		}
		
		public function testAddingAnInvalidItem()
		{
			$this->expectException(\Exception::class);
			$obj = new CartCont;
			$obj->addToCart(Products::model()->getLastId()+1000,1);
		}
		
		public function testTryingToRemoveAnNonExistentItem()
		{
			$this->expectException(\Exception::class);
			$obj = new CartCont;
			$obj->removeToCart(Products::model()->getLastId()+10000);
		}
		
		public function testPassingInvalidIdProductToGetDiscount()
		{
			$this->expectException(\Exception::class);
			$obj = new CartCont;
			$obj->getDiscount(Products::model()->getLastId()+1000);
		}
		
		public function testPassingInvalidProductToGetTax()
		{
			$this->expectException(\Exception::class);
			$obj = new CartCont;
			$obj->getTax(Products::model()->getLastId()+1000);			
		}
		
		public function testAssumingEmptyCartWhenRequestDetailsOfCurrentCart()
		{
			$empty = $this->createMock(CartCont::class);
			$empty->method('getItems')->willReturn([]);

			$this->assertEmpty($empty->getDetailOfCurrentCart());
		}
		
		public function testAssumingEmptyCartWhenRequestDetailOfTotalToPay()
		{
			$empty = $this->createMock(CartCont::class);
			$empty->method('getItems')->willReturn([]);
			
			$this->assertEquals(0,$empty->getTotalAmounts(false));
		}
		
		public function testPayForTheProductsInCartWhenIsEmpty()
		{			
			$obj = new CartCont;
			$this->assertFalse($obj->payForTheCart());
		}
		
		public function testCheckIfYouCanFindThisProductWithThisExceptionMessage()
		{
			$this->expectException(\Exception::class);
			$obj = new CartCont;
			$obj->loadProductInCart(Products::model()->getLastId(),['Message1','Message2']);
		}
		
		
	}