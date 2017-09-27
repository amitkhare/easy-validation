# amitkhare/easy-validation
Easy Validation is an easy to use PHP validation library

## Install

Run this command from the directory in which you want to install.

### Via Composer:

    php composer.phar require amitkhare/easy-validation

### Via Git:

    git clone https://github.com/amitkhare/easy-validation.git

### Manual Install:

    Download: https://github.com/amitkhare/easy-validation/archive/master.zip
    Extract it, require "PATH-TO/"."validbit.php" where you want to use it.

## Usage:
```sh
<?php
use AmitKhare\EasyValidation; // use namespace.

require("PATH-TO/"."EasyValidation.php"); // only need to include if installed manually.

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
```
## Available Methods:
    > $v->check("FIELD","RULES");
    > $v->match("FIELD1","FIELD2","RULES");

## Available Rules:
    > required
    > email
    > url
    > numeric
    > string
    > float
    > ipv4
    > ipv6
    > bool
    > min
    > max
    > alphanum
    > alphanumUnicode
    > unique (avaiable only if instantiate EasyValidation With Database Details);
