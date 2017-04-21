<?php 
	use \Models\Products;
	use \PHPUnit\Framework\TestCase;
	
	class ProductsTest extends TestCase
	{
		
		public function testExceptionDataFile($nombre = 'products2.json')
		{
			$this->expectException(\Exception::class);
			
			$obj = new Products($nombre);
			$obj->getProducts();
		}
		
		public function testDataFileReturnedArray()
		{
			$pro = new Products();
			$result = is_array($pro->getProducts());
			$this->assertTrue($result);
		}
		
		public function testPutNumberInStringDataFile()
		{
			$this->testExceptionDataFile(45.54);
		}

		public function testDontGetAnyProduct($id = 4500)
		{
			$obj = new Products();
			$this->assertFalse($obj->getProductById($id));
		}
		
		public function testPutStringAsIdWhenLookForAProduct()
		{
			$this->testDontGetAnyProduct('1');
		}
		
		public function testSingleProductReturnAnObject()
		{
			$pro = new Products();
			$result = is_object($pro->getProductById($pro->getLastId()));
			$this->assertTrue($result);
		}

	}