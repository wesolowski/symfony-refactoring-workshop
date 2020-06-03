# Workshop: Symfony and refactoring


###### Technical Requirements

* Install PHP 7.2 or higher and these PHP extensions (which are installed and enabled by default in most PHP 7 installations): Ctype, iconv, JSON, PCRE, Session, SimpleXML, and Tokenizer;
* Install Composer, which is used to install PHP packages;

###### Setup


1.Clone repo:

```
git clone https://github.com/wesolowski/symfony-refactoring-workshop.git
cd symfony-refactoring-workshop
``` 

2.Start docker (MySQL-Server)

```
docker-compose up -d
```

3.Install/config symfony

```
composer install
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

4. Start PIM-Import

```
php bin/console pim:im
```

5.Symfony Web-browser

Download and install Symfony-CLI: <https://symfony.com/download>

```
symfony serve:start
```

Now you can see the shop:

Start: <https://localhost:8080/>  

