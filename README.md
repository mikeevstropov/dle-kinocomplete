# DLE Kinocomplete

![kinocomplete_logo](http://cdn.onpublic.ru/kinocomplete/assets/logo-card-small.png)

Module for the Movie Websites based on CMS DataLife Engine.

## Build

At first you need to clone a package by following command.
```$xslt
git clone git@github.com:mikeevstropov/dle-kinocomplete.git
```
Resolve server dependencies.
```$xslt
cd dle-kinocomplete/upload/engine/modules/kinocomplete/
composer install
```
Resolve client dependencies.
```$xslt
cd web/ && yarn
```
Go back and build.
```$xslt
cd ../ && composer build
```
Congrats! Now you can get an archive from `dle-kinocomplete/dist/kinocomplete.zip`.
