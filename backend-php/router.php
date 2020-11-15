<?php

    // Note: all headers should be declared before any code execution
    // Important: Becareful how CORS (cross-origin-resources) are set. This can lead to a major security vulnerability if received by an untrusted resources
    header("Access-Control-Allow-Origin: *");

    // Important: This header is key to sending only json content back
    header("Content-Type: application/json");

    function featureToClassName($string)
    {
        $class_name = "";
        $class_name_array = explode("-", $string);
        foreach($class_name_array as $key => $value)
        {
            $class_name .= ucfirst($value);
        }
        return $class_name;
    }

    // Store root in global to be accessed everywhere throughout the application
    $GLOBALS['root'] = "../api/";

    $feature = "";
    $method = "";
    $data = "";

    // ---------------- Start URI Parsing -------------------

    // encodes characters 
    $uri = htmlspecialchars(strtok($_SERVER["REQUEST_URI"], '?'), ENT_QUOTES | ENT_HTML5, "UTF-8", false);

    // removes white-spaces
    $uri = preg_replace("/\s+/", "", $uri);

    $path = explode("/", $uri);

    $path_array = array_filter($path);

    // Remove all illegal entity encodings
    $path_array = preg_replace("/%\d[a-zA-Z0-9]/mi", "", $path_array);

    // ----------------- End URI Parsing -------------------

    // ------------- Start Parameter Parsing ---------------

    // combine get parameters
    $string_parameters = "";
    foreach($_GET as $key => $value)
    {
        $string_parameters .= "$key=$value&";
    }

    // remove first ?
    $string_parameters = ltrim($string_parameters, "?");

    // remove last &
    $string_parameters = rtrim($string_parameters, "&");

    // & is a special character and will be turned into an entity code, therefore explode must exist before htmlspecialchars
    $parameter_elements = explode("&", $string_parameters);

    // initialize associative array for parameters
    $parameters = array();

    if(count($parameters) > 0)
    {
        foreach($parameter_elements as $pair => $key_value)
        {
            $key_value = htmlspecialchars($string_parameters, ENT_QUOTES | ENT_HTML5, "UTF-8", false);

            // remove white spaces
            $parameters = preg_replace("/\s+/", "", $parameters);

            $parameters = preg_replace("/%\d[a-zA-Z0-9]/mi", "", $parameters);

            $pair = explode("=", $key_value);

            // $pair[0] is key
            // $pair[1] is value
            $parameters[$pair[0]] = $pair[1];
        }
    }

    // --------------- End Parameter Parsing ---------------

    // ----------------- Start Method data -----------------

    // check for web method post / patch / delete
    switch(true)
    {
        case !empty($_POST):

        break;
        case !empty($_PUT):

        break;
        case !empty($_PATCH):

        break;
        default:
        
    }

    // ----------------- End Method data -------------------

    // define variables as empty for now
    $sub_feature = "";
    $sub_sub_feature = "";

    if(count($path_array) == 0)
    {
        $feature = "landing-page";
        $method = "Index";
    }
    else
    {

        switch(true)
        {
            case count($path_array) == 1:
                $feature = $path_array[1];
                $method = "Index";
            break;
            case count($path_array) == 2:
                $feature = $path_array[1];
                $method = $path_array[2];
            break;
            case count($path_array) == 3:
                $feature = $path_array[1];
                $sub_feature = $path_array[2];
                $method = $path_array[3];
            break;
            case count($path_array) == 4:
                $feature = $path_array[1];
                $sub_feature = $path_array[2];
                $sub_sub_feature = $path_array[3];
                $method = $path_array[4];
            break;
            default:
                http_response_code(404);
                $controller = "errors";
                $method = "NotFound";
        }
    }

    // Convert feature name to class names
    $class_name = featureToClassName($feature);
    $class_name = !empty($sub_feature) ? featureToClassName($sub_feature) : $class_name;
    $class_name = !empty($sub_sub_feature) ? featureToClassName($sub_sub_feature) : $class_name;
    $class_name .= "Controller";
    $method = strtolower($method);

    $request_file = "../api/features/$feature$sub_feature$sub_sub_feature/controller.php";

    if(file_exists($request_file))
    {
        include($request_file);

        // Catch invalid class creation or method execution
        try
        {
            if(class_exists($class_name))
            {
                $controller = new $class_name($root);
            }
            else
            {
                throw new \Exception();
            }

            if(method_exists($controller, $method))
            {
                $controller->$method($data, $parameters);
            }
            else
            {
                throw new \Exception();
            }
        }
        catch(\Exception $e)
        {
            $feature = "errors";
            $class_name = featureToClassName("Errors")."Controller";
            $method = "NotFound";

            include("../api/features/$feature/controller.php");

            $controller = new $class_name($root);

            $controller->$method($data, $parameters);
        }
    }
    else
    {
        $feature = "errors";
        $class_name = featureToClassName("Errors")."Controller";
        $method = "NotFound";

        include("../api/features/$feature/controller.php");

        $controller = new $class_name($root);

        $controller->$method($data, $parameters);
    }

?>