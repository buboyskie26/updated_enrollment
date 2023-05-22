<?php
    ob_start(); //Turns on output buffering 
    session_start();
    date_default_timezone_set("Asia/Manila");

    define('DB_HOST', 'localhost');
    define('DB_PORT', 3307); // Update with your specific port number
    define('DB_USERNAME', 'root');
    define('DB_PASSWORD', '');
    define('DB_DATABASE', 'dcbt');

    try {

        // string that specifies the details of the database connection, including the database driver
        $data_source = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_DATABASE;
        $con = new PDO($data_source, DB_USERNAME, DB_PASSWORD);
        $con = new PDO('mysql:host=localhost;port=3307;dbname=dcbt', 'root', '');
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

        $this_file = str_replace('\\', '/', __File__) ;
        $doc_root = $_SERVER['DOCUMENT_ROOT'];

        $web_root =  str_replace (array($doc_root, "include/config.php") , '' , $this_file);
        $server_root = str_replace ('config/config.php' ,'', $this_file);
        
        define ('web_root' , $web_root);

        $base_url = (isset($_SERVER['HTTPS']) 
                        && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);

        define ('base_url' , $base_url);

        $currentURL = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
        $currentURL .= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        // Extract the directory path from the current URL
        $directoryPath = dirname($currentURL);


        // FIX THE URL. WILL REDIRECT PROPERLY ESPECIALLY IN THE PRODUCTION
        if ($_SERVER['SERVER_NAME'] === 'localhost') {
            // $base_url2 = 'http://localhost/elms/admin';
            // define ('base_url2' , $base_url2);

            define('directoryPath', $directoryPath . '/');
            define('ROOT_DIR', basename(__DIR__));

        } else {
            $base_url2 = 'http://www.example.com/elms/admin';
            define ('base_url2' , $base_url2);
            define('directoryPath', $directoryPath . '/');
            define('ROOT_DIR', basename(__DIR__));

        }
    }
    catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
    
?>