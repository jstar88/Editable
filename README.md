Editable
========

This class can add functions and variables dynamically to objects.  
It can add new public and private variables to an object that extends the editable class. The variables may be set to a given initial value.   
It can also add or replace public and private functions with a new function code.  
The class can also intercept calls to functions by calling the code of a given function before calling the actual function being intercepted.  

## Feaures

* add public and private variables
* add public and private functions
* override public and private functions
* handle functions call registering interceptors with a callback
* handle dinamically generated public/private variables changes

## Usage

#### Installation
**Require PHP >= 5.4**   
First of all download **Editable.php** and **pathwork.phar** in the same directory .  
Now choose the classes that should be editable by this script adding the word `extends Editable`:
```php
    class MyClass extends Editable
```
Then include in your main script **Editable.php** and use your class with new features!
```php
    require "Editable.php";
    $f = new MyClass(); // instantiate your class extending Editable
    $f-> ...
```


#### Managment of variables

###### Adding a variable
```php
    //private
    $f->addPrivateVariable("var1","Hello World",$handler,$handlerArgs);
    //or public
    $f->addPublicVariable("var2","Hello World 2",$handler,$handlerArgs);
```
* The first argument represent the variable name, the second its value.
* You can't assign the same variable both private and public.
* If the variable already exist an exception will be thrown.
* ```$handler``` is a callable function that will be called on variable changes.(optional)
* ```$handlerArgs``` is an array of arguments of the above function.(optional)
* Variable handling will work only on dinamically generated public/private variables.

#### Managment of functions

###### Adding a function
```php
    //private
    $f->addPrivateFunction("sayHello",$callback);
    //or public
    $f->addPublicFunction("sayHello2",$callback);
```
* The first argument represent the function name, the second its definition.
* `$callback` must be  [callable](http://php.net/manual/en/language.types.callable.php) or a [closure](http://php.net/manual/en/functions.anonymous.php).
* You can't assign the same function both private and public.
* If the function already exist an exception will be thrown.

###### Rewritting a function
```php
    $f->replaceFunction($callbackOld,$callbackNew);
```
* The first argument rappresent the callback of function to rewrite , the second the callback(or [closure](http://php.net/manual/en/functions.anonymous.php)) for its new definition.
* If the function doesn't exist an exception will be thrown.

###### Intercepting a function
```php
    $f->interceptFunction($callbackFunction,$callbackInterceptor);
```
* The first argument rappresent the callback of function to handle , the second the callback(or [closure](http://php.net/manual/en/functions.anonymous.php)) for the interceptor definition.
* `$callbackInterceptor` is called **before** the target function.
