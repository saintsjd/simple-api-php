<?php
require 'vendor/autoload.php';

$f3 = require('vendor/bcosca/fatfree/lib/base.php');

$f3->set('DEBUG', getenv("SECRET_KEY"));
$f3->set('AUTOLOAD','controllers/; models/; lib/');
$f3->set('DB', new DB\Jig('data/',DB\Jig::FORMAT_JSON) );

// USERS
$f3->route( 'GET /users',           'UsersController->index');
$f3->route( 'GET /users/@id',       'UsersController->show');
$f3->route( 'POST /users',          'UsersController->create');
$f3->route( 'PATCH /users/@id',     'UsersController->update');
$f3->route( 'PUT /users/@id',       'UsersController->update');
$f3->route( 'DELETE /users/@id',    'UsersController->destroy');

// AUTH
$f3->route( 'POST /authorizations',          'AuthorizationsController->create');


class JsonApi {


    // JSONAPI.org format for reponses
    public static function response( $resourceName = null, $resources = null, $links = null, $linked = null ) {

        header('Content-Type: application/json', true);
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, HEAD');  

        // 204 No Content responses for deletes, etc.
        if( empty( $resourceName ) ) {
            http_response_code(204);
            return "";
        }

        $response = array();
        if( is_array($resources) ) {
            $response[$resourceName] = $resources;
        }else {
            $response[$resourceName] = array( $resources );            
        }
        return json_encode( $response );
    }

}

//JSONAPI.org format for error messages
$f3->set('ONERROR',
    function($f3) {

        $errors = array();

        if( $f3->exists('ERROR_LIST') && is_array($f3->get('ERROR_LIST')) && count($f3->get('ERROR_LIST')) > 0 ) {

            foreach( $f3->get('ERROR_LIST') as $error ) {
                $errors[] = array(
                        'code' => $f3->get('ERROR.code'),
                        'status' => $f3->get('ERROR.status'),
                        'title' => $error['field'],
                        'text' => $error['text'],
                        'trace' => $f3->get('ERROR.trace'),
                    );

            }

        }else {
            $errors[] = array(
                    'code' => $f3->get('ERROR.code'),
                    'status' => $f3->get('ERROR.status'),
                    'title' => $f3->get('ERROR.title'),
                    'text' => $f3->get('ERROR.text'),
                    'trace' => $f3->get('ERROR.trace'),
                );
        }

        echo JsonApi::response('errors', $errors );
    }
);

if( empty( getenv("SECRET_KEY")) ){
    echo "secret key is undefined";
    die();    
}

$f3->run();