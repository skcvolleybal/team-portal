# TeamPortal

This project was generated with [Angular CLI](https://github.com/angular/angular-cli) version 6.2.3.

# Installation instructions

## Required: 
1. Install Node.js: https://nodejs.org/en/download/. Make sure it's an even release! Such as 16, 18. 
3. Install a webserver such as Xampp: https://www.apachefriends.org/download.html
4. Install Composer: https://getcomposer.org/download/. Use Xampps PHP version, likely installed in C:\xampp\php (Windows)
5. Clone the team-portal repository to your machine into Xampps htdocs directory, likely C:\xampp\htdocs. Ensure you emptied the directory first.  

Team-Portal consists of 2 parts, an Angular frontend and PHP backend. However, it also requires a running Joomla 3 website and database using the correct schema. 

## Angular

6. Navigate to the cloned repository directory. Likely C:\xampp\htdocs\team-portal. Run `npm i -f` to (force) install all required Angular packages.
7. Next, run `ng serve` (or `npm run ng serve` if ng serve doesn't work) to create a dev server. Navigate to http://localhost:4200/ in your webbrowser to view the app. As long as `ng serve` is running, the app will automatically reload if you change any of the source files.
8. In team-portal\src\environments\environment.ts, make sure your baseUrl is set properly to match the URL where Team-Portal API is available. For example: http://localhost/team-portal/api/

The Angular frontend is now working, but can't communicate with the PHP backend yet. We need to set some configuration variables and use Composer to install all required packages. 

## PHP
9. Navigate to the php directory in your cloned repository
10. Rename configuration_example.php to configuration.php
11. Open configuration.php, ensure $JpathBase and $AccessControlAllowOrigin are set correctly, and that the database host, name, username and password are correct. The $JpathBase should point to the root of your website on disk (for example: "C:\xampp\htdocs"). Make sure $AccessControlAllowOrigin is set to the URL that Team-Portal Angular runs on. For development: http://localhost:4200.
12. Remove the composer.lock file
13. Run `composer install` to install all required PHP packages. If you get PHP version errors, change the composer.json file to require "php": "^8.1". 

## Joomla
14. In Xampp, make sure Apache and MySQL are running. 
15. Install Joomla 3.x in the htdocs directory. 
16. Export the live SKC Joomla database (http://www.skcvolleybal.nl/phpmyadmin).
17. Using http://localhost/phpmyadmin, import the exported SKC database SQL file to recreate the SKC database. 

Troubleshooting: if things aren't working, assuming Angular has compiled and been set up properly, step through the PHP code using XDebug to find out the problem. 

# Angular documentation

## Code scaffolding

Run `ng generate component component-name` to generate a new component. You can also use `ng generate directive|pipe|service|class|guard|interface|enum|module`.

## Build

Run `ng build` to build the project. The build artifacts will be stored in the `dist/` directory. Use the `--prod` flag for a production build.

## Running unit tests

Run `ng test` to execute the unit tests via [Karma](https://karma-runner.github.io).

## Running end-to-end tests

Run `ng e2e` to execute the end-to-end tests via [Protractor](http://www.protractortest.org/).

## Further help

To get more help on the Angular CLI use `ng help` or go check out the [Angular CLI README](https://github.com/angular/angular-cli/blob/master/README.md).
