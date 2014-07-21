<?php
class UsersController extends BaseController{

    function index($f3) {

        if( !$f3->exists('authenticated_user') ) {
            $f3->error( 403 );    
        }

        $limit = 10000;

        $user = new UserModel();

        $authenticated_user = $f3->get('authenticated_user');

        // admin users see everything. non admin users can only see themselves.
        if( $authenticated_user->isAdmin() ){
            $users = $user->find(array(),array('order'=>'created desc', 'limit'=> $limit ));
        }else{
            $users = $user->find(array("@_id = ?", $authenticated_user->id),array('order'=>'created desc', 'limit'=> $limit ));
        }
        
        echo JsonApi::response( 'users', $users );
    }


    function show($f3) {

        if( !$f3->exists('authenticated_user') ) {
            $f3->error( 403 );    
        }

        $id = $f3->get('PARAMS.id');

        if( !$id || empty($id) ){
            $f3->error( 404 );            
        }

        // non admin users can only see themselves
        if( !$f3->get('authenticated_user')->isAdmin() && $id != $f3->get('authenticated_user')->id ) {
            $f3->error( 403 );                        
        }

        $user = new UserModel();
        $user->load( array("@_id = ?", $id) );

        if( $user->dry() ){
            $f3->error( 404 );            
        }

        echo JsonApi::response( 'users', $user );
    }



    function create($f3) {

        $request_json = json_decode( $f3->get('BODY'), true );
        $raw_data = $request_json['users'][0];

        $user = new UserModel();
        $attr_accessible = array( 'email','password' );
        $user->hydrate($raw_data, $attr_accessible );

        $errors = $user->validate();
        if( count($errors) > 0 ){
            $f3->error(422, 'Unprocessable Entity' );
        }

        // convert plain text password to salted hash
        if( isset($user->password) ){
            $user->password = password_hash( $user->password, PASSWORD_DEFAULT );            
        }

        $user->save();

        echo JsonApi::response( 'users', $user );

    }

    function update($f3) {

        if( !$f3->exists('authenticated_user') ) {
            $f3->error( 403 );    
        }

        $id = $f3->get('PARAMS.id');

        if( !$id || empty($id) ){
            $f3->error( 404 );            
        }

        // non admin users can edit themselves
        if( !$f3->get('authenticated_user')->isAdmin() && $id != $f3->get('authenticated_user')->id ) {
            $f3->error( 403 );                        
        }

        $user = new UserModel();
        $user->load( array("@_id = ?", $id) );

        if( $user->dry() ){
            $f3->error( 404 );            
        }

        $request_json = json_decode( $f3->get('BODY'), true );
        $raw_data = $request_json['users'][0];

        $attr_accessible = array( 'email','password' );
        if( $f3->get('authenticated_user')->isAdmin() ) {
            $attr_accessible = array( 'email','password','roles' );            
        }
        $user->hydrate($raw_data, $attr_accessible );

        $errors = $user->validate();

        if( count($errors) > 0 ){
            $f3->error(422, 'Unprocessable Entity' );
        }

        if( isset($user->password) ){
            $user->password = password_hash( $user->password, PASSWORD_DEFAULT );            
        }

        $user->save();

        echo JsonApi::response( 'users', $user );

    }

    function destroy($f3) {

        if( !$f3->exists('authenticated_user') ) {
            $f3->error( 403 );    
        }

        $id = $f3->get('PARAMS.id');

        if( !$id || empty($id) ){
            $f3->error( 404 );            
        }

        // only admin users delete a user
        if( !$f3->get('authenticated_user')->isAdmin() ) {
            $f3->error( 403 );                        
        }

        $user = new UserModel();
        $user->load( array("@_id = ?", $id) );

        if( $user->dry() ){
            $f3->error( 404 );            
        }

        $user->erase();

        // send 204 No Content response
        echo JsonApi::response();

    }


}
