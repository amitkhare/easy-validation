<?php
    use AmitKhare\EasyValidation; // use namespace.
    // autoload via composer
    require __DIR__.'/../vendor/autoload.php';
    // OR
    // require("PATH-TO/"."validbit.php"); // only need to include if installed manually.
    
    $v = new EasyValidation(); // instantiate EasyValidation;
	//  OR with database for unique field check
    $v = new EasyValidation($host,$username,$password,$dbname); // instantiate EasyValidation With Database features;
    $v->setSource($_POST); // set data source array;
    
    $v->check("mobile","required|numeric|min:10|max:15");
    $v->check("username","required|alphanum|unique:users.username|min:4|max:20");
    $v->check("email","required|email|unique:users.email|min:4|max:100");
    
    $v->match("password","password_confirm","required|min:6|max:25");
    
    if(!$v->isValid()){
    	print_r($v->getStatus());
    }
