# Haladrive API

## Architecture

## Custom MVC framework written in PHP for extremly lightweight and rapid application & REST API developement.

### Third party libraries

- GUMP for INPUT VALIDATION
- PHP MAILER
- Any other third party library can be installed via composer.

---

## Configuration

- app/config.php
- All configuration constants are defined in this file & can be in entire application scope.
- database connection
- datetime zone etc
- Base URL

---

## Application structure

- /app
- /app/controllers : (Define controller name with Ctrl prefix and assign in routes with methods)
- /app/modules (Database & common functionality modules to be loaded in controllers)
- app/config (define application configuration )
- app/events.php (adding events )
- index.php : ( loads bootstrap.php at the top, All requests points to it then routed to their corresponding controller methods)

---

### core framework files

- /framework : (All core file resided in /framework folder on the root)
- routes.class.php : (Front controller class for managing routes and dispatch to dedicated controllers and method route is pointing to.)
- auth.class.php : (Session based authentication)
- jwt.class.php : (Token based Authentication for Single page application)
- connection.class.php : (Singleton class for database connection to make sure only one instaces of Database in operation to avoid to many connection overload.)
- class.database.php : (User to interact with database)
- events.class.php : (for events not in used so far)
- helpers.class.php : (for utility helpers)
- model.class.php : (for base model class to be extended)
- view.class.php : (for rendering output can with with the template for JSON output)
- template.class.php : (master templating)
- mvc.class.php : (require all core framework files to be included in)

### Define Database Connection in app/config.php

<code> define('SERVER', 'localhost') </code>

<code> define('USER', 'my-db-user'); </code>

<code> define('DATABASE', 'your-database-name'); </code>

<code> define('PASSWORD', 'my-password'); </code>

### Define Routes

<code> \$route->get('/', 'pagesCtrl@home') </code>
