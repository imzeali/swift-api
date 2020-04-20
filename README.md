# swift-api
A laravel restful api tool
```
composer require imzeali/swift-api
```  
Then run these commands to publish assets and config：
```
php artisan vendor:publish --provider="SwiftApi\SwiftApiServiceProvider"
```
After run command you can find config file in config/api.php, in this file you can change the install directory,db connection or table names.
```
php artisan swift-api:install
```
Quick create api
```
php artisan swift-api:create-api test/tests  --route --request --model
```
Start serve
```
php artisan serve
```
Then access http://localhost:8000/api/test/tests
# Thank
The project was modified through [laravel-admin](https://github.com/z-song/laravel-admin)
