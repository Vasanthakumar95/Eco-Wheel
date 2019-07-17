<?php
    //connection constants for localhost
    define('DB_HOST' , 'localhost');
    define('DB_USER' , 'root');
    define('DB_PASSWORD' , '');
    define('DB_NAME' , 'ecowheel');
    //user create constants
    define('USER_CREATED', 101);
    define('USER_EXISTS', 102);
    define('USER_FAILURE', 103);

    //trip create constants
    define('TRIP_CREATED', 401);
    define('TRIP_FOUND', 401);
    define('TRIP_NOT_FOUND', 402);
    define('TRIP_ERROR', 404);
    
    //working status
    define('WORKING', 401);
    define('NOT_WORKING', 402);

    //destination
    define('DESTINATION_FOUND', 401);
    define('DESTINATION_NOT_FOUND', 402);

    //authentication constants
    define('USER_AUTHENTICATED', 201);
    define('USER_NOT_FOUND', 202);
    define('USER_PASSWORD_DO_NOT_MATCH', 203);
    
    //password constants
    define('PASSWORD_CHANGED', 301);
    define('PASSWORD_DO_NOT_MATCH', 302);
    define('PASSWORD_NOT_CHANGED', 303);


    
    
   