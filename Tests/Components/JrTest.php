<?php 
	use \Components\JR;
	use \PHPUnit\Framework\TestCase;
	
	class JrTest extends TestCase
	{
		public function testIfMainPHPDoesntExists()
		{			
			$this->expectException(\Exception::class);
			$obj = new JR('main2.php');
		}
	}