<?php 
	use \Controllers\ProductsController as ProductsCont;
	use \Models\Products;
	use \PHPUnit\Framework\TestCase;
	
	class ProductsControllerTest extends TestCase
	{
		public function testAddProductsContuccessfully()
		{
			$pro = new ProductsCont();
			$this->assertTrue($pro->addProduct('nombre','45.51',40,'price','34.25'));
			
		}
		
		private function addProduct($name,$price,$quantity,$discountType = "none",$discountPrice = null,$discountRule = "none")
		{
			$this->expectException(\Exception::class);
			$pro = new ProductsCont();
			$pro->addProduct($name,$price,$quantity,$discountType,$discountPrice,$discountRule);
		}
		
		public function testSendInvalidTypeOfDiscount()
		{
			$this->addProduct('nombre',45.50,50,'some type');
		}
		
		public function testSendIncorrectPriceForProduct()
		{
			$this->addProduct('nombre','Precio',45.50,'Descuento');
		}
		
		public function testSendQuantityWithFloat()
		{
			$this->addProduct('nombre',45.50,44.10);
		}
		
		public function testSendMoreDiscountRulesThanDiscountPrices()
		{
			$this->addProduct('nombre',45.50,10,'quantity',['25.50'],['>=2','<=3']);
		}
		
		public function testSendIncorrectArgumentsForDiscountRules()
		{
			$this->addProduct('nombre',45.50,10,'quantity',['25.50',14.74],['<>2','<=3']);
		}
		
		public function testEditSomeProduct()
		{
			$pro = new ProductsCont();
			$this->assertTrue($pro->editProduct(Products::model()->getLastId(),['name'=>'Nombre Editado','price'=>2555.50]));
		}
		
		public function testProvideFalseIdToRemove()
		{
			$pro = new ProductsCont();
			$this->assertFalse($pro->removeProduct((Products::model()->getLastId()+100)));
		}
		
		public function testProvideStringInsteadIdToRemove()
		{
			$pro = new ProductsCont();
			$this->assertFalse($pro->removeProduct(('some product')));
		}

	}