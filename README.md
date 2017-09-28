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
    Extract it, require "PATH-TO/"."EasyValidation.php" where you want to use it.

## Minimal Usage:
```sh
<?php

require("PATH-TO/"."EasyValidation.php"); // only need to include if installed manually.

$v = new AmitKhare\EasyValidation\EasyValidation(); // instantiate EasyValidation;

$v->setSource($_POST); // set data source array;

$v->setLocale("en-IN","PATH/TO/LOCALES/DIRECTORY/"); 
    
$v->check("email","required|email|unique:users.email|min:4|max:100");
$v->match("password","password_confirm","required|min:6|max:25");

if(!$v->isValid()){
    print_r($v->getStatus());
}

```

## Usage:
```sh
<?php
use AmitKhare\EasyValidation; // use namespace.

require("PATH-TO/"."EasyValidation.php"); // only need to include if installed manually.

$v = new EasyValidation(); // instantiate EasyValidation;

//  OR with database for unique field check
$v = new EasyValidation($host,$username,$password,$dbname); // instantiate EasyValidation With Database features;

$v->setSource($_POST); // set data source array;


// check bottom of this file for sample en-IN.lang file
$v->setLocale("en-IN","PATH/TO/LOCALES/DIRECTORY/"); 
    
$v->check("mobile","required|numeric|min:10|max:15");
$v->check("username","required|alphanum|unique:users.username|min:4|max:20");
$v->check("email","required|email|unique:users.email|min:4|max:100");

$v->match("password","password_confirm","required|min:6|max:25");

if(!$v->isValid()){
    print_r($v->getStatus());
}
```
## Available Methods:
    > $v->setSource(array["FIELD"=>"VALUE"]);
    > $v->setLocale("en-IN",PATH); // Path is optional
    > $v->check("FIELD","RULES");
    > $v->match("FIELD1","FIELD2","RULES");
    > $v->isValid();  // returns true/false
    > $v->getStatus(); // get error messages

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

## Sample en-IN.lang file  [[ JSON FORMAT ]]
```sh
    {

    "FIELDS_DONT_MATCH" : "The `%s` dont match with `%s`.",
    "FIELD_REQUIRED" : "The `%s` is required.",
    "FIELD_NOT_SET" : "The `%s` field is not set.",

    "USERNAME":"Username",
    "FIRSTNAME":"First Name",
    "LASTNAME":"Last Name",
    "MIDDLENAME":"Middle Name",
    "EMAIL":"Email",
    "PASSWORD":"Password",
    "MOBILE":"Mobile",
    "PASSWORD_CONFIRM":"Password Confirm"

    }
```

## TODO:
    > Support for single field check like setField() in addition with setSource()
