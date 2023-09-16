# TeamPortal

## Beschrijving
TeamPortal ondersteunt de TeamTakenCoordinator van SKC bij het maken van planningen voor wedstrijddagen. SKC-leden worden ingepland op wedstrijddagen om BHV'er te zijn, scheidsrechter, teller of voor het op- en afbouwen van de zaal. Om te zorgen dat leden beschikbaar zijn kunnen leden zelf inloggen in TeamPortal om hun beschikbaarheid door te geven. 

De planningen worden wekelijks verzonden via een Cronjob. 

TeamPortal gebruikt de Nevobo RSS feed om de voor SKC relevante wedstrijden op te halen. Verder gebruikt het de Joomla 3 interne database van de SKC website om teams en leden te koppelen. 

## Systeemeisen:
- PHP 8.0.11
- Node.js even release (16, 18)
- Angular v15

# Installation instructions
This installation assumes you have set-up your webserver and WordPress. If you haven't done this yet, go to: https://github.com/skcvolleybal/starthier 

## Required: 
1. Install Node.js: https://nodejs.org/en/download/. Make sure it's an even release such as 16, 18. 
2. Install Composer: https://getcomposer.org/download/. Use Laragons PHP version, likely installed in C:\laragon\bin\php\php-8.2.x-Win32-vs16-x64
3. Clone the team-portal repository in the root of your webserver in a team-portal directory, likely C:\laragon\www\team-portal. Ensure you emptied the directory first.  

## Database
4. Create a new database for Team-Portal using [PhpMyAdmin](http://localhost/phpmyadmin) (or similar application such as DBeaver)
5. In the database, create the Team-Portal tables. Copy the SQL commands as found in team-portal\php\create_tables.sql and paste and execute them in PHPMyAdmin.
6. The database should now have the following tables: barcie_days, barcie_availability, barcie_schedule_map, teamportal_aanwezigheden, teamportal_email, teamportal_fluitbeschikbaarheid, teamportal_wedstrijden, teamportal_zaalwacht

## Angular
4. Navigate to the cloned repository directory. Likely C:\laragon\www\team-portal. Run `npm i -f` to (force) install all required Angular packages.
5. Next, run `ng serve` (or `npm run ng serve`) to run the Angular service. Navigate to http://localhost:4200/ in your webbrowser to view the app.
  
As long as `ng serve` is running, the app will automatically reload if you change any of the source files.

## PHP
7. Navigate to the team-portal\php directory
8. Duplicate .env.example and call it .env 
9. Open .env and ensure the following:
 - Set ACCESSCONTROLALLOWORIGIN to http://localhost:4200
 - Set the DBHOSTNAME ("localhost"), DBNAME (the database you created earlier), DBUSERNAME and DBPASSWORD. This connects Team-Portal to its database. 
 - Set the WORDPRESS_PATH to where WordPress is installed, which should be your webservers root directory (C:\laragon\www). This ensures that Team-Portal can use WordPress' authentication classes. 
 - Set the WPDBHOSTNAME, WPDBNAME, WPDBUSERNAME, DBPASSWORD the same as your WordPress configuration. If you don't remember those, look at C:\laragon\www\wp-config.php.
 - JPATHBASE is obsolete and can probably be removed

10. Run `composer install` to install all required PHP packages. 

Troubleshooting: if things aren't working, assuming Angular has compiled and been set up properly, step through the PHP code using XDebug to find out the problem. 
