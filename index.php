<?php
	require_once 'Autoloader.php';
	use \Controllers\CartController as CartCon;
	use \Controllers\ProductsController as ProdCon;
	use \Models\Products as Pro;
	use \Components\JR;
	
	/*Se crea una nueva instancia de producto y se añaden algunos de ejemplo */
	$product = new ProdCon;
	$product->addProduct('Product 1',45.50,100,'quantity',['40.40','35.10',30.5],['>=10','>=25','>50']);
	$product->addProduct('Product 2',100.2,20);
	$product->addProduct('Product 3',15,1000,'quantity',11,'>=60');
	$product->addProduct('Product 4',37.50,200,'price','47.12');
	$product->addProduct('Product 5',37.50,200,'price','47.12');
	$product->removeProduct(4);
	$product->editProduct(5,['name'=>'Product 4 (E)']);
	$product->showCatalog();
	
	$cart = new CartCon();
	$cart->addToCart(1,12);
	$cart->addToCart(2,5);
	$cart->addToCart(3,12);
	$cart->addToCart(5,7);
	$cart->removeToCart(2);
	echo $cart->getDetailOfCurrentCart();
	echo $cart->getTotalAmounts() . '<br>';
	$cart->payForTheCart();
	
	echo '<br><strong>Productos luego de comprarlos:</strong><br>';
	$product->showCatalog();
	
	/*Se cierra la sesion lo cual borra todo del carrito */
	JR::app()->session->endSession();