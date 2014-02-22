SoMaze
=========

SoMaze is a puzzle game created by [Evil Mouse Studios] for use with cryptocurrencies such as [Dogecoin].  It's kind of a mix between a maze, minesweeper, and Stratego.

Technology
----
* Build with PHP 5
* Laravel 4 as a framework
* HTML5/CSS/Javascript with jQuery for frontend
* Twitter Bootstrap 3
* CouchDB as database

Instructions for deploying
----
* Make sure you have Dogecoind installed
* Make sure CouchDB is running and has tiles installed in misc
* Config/Common needs to point to the right domain (for OpenID sign in)
* Config/App needs to have debugging turned off

Maintenance Mode
----
To enable maintenance mode: 

```php artisan down```

To bring it back up:

```php artisan up```


[Evil Mouse Studios]:http://evilmousestudios.com
[Dogecoin]:http://dogecoin.com/