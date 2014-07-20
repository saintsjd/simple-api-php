<?php

class UserModel extends BaseModel {
    
    public function __construct() {

        parent::__construct( \Base::instance()->get('DB'), 'users.json' );

    }


    public function beforeSave( $self ) {

        // auto update timestamps
        if( !$self->id ) {
            $self->created = (new DateTime())->format(DateTime::ISO8601);            
        }
        $self->updated = (new DateTime())->format(DateTime::ISO8601);

    }


    public function jsonSerialize() {

        $output = parent::jsonSerialize();

        // never return the password
        unset($output['password']);

        return $output;
    }


    function validate() {

        $errors = [];

        $audit = \Audit::instance();

        if( isset($this->email) ) {

            $validEmail = $audit->email( $this->email, FALSE );
            if( !$validEmail ) {
                $errors[] = array(
                    'field' => 'email',
                    'text' => 'invalid email',
                );                             
            }

            // don't allow two accounts with the same email
            if( empty($this->id) ){
                $test = $this->find( array('(isset(@email) && preg_match(?,@email))','/'.$this->email.'/') );
            } else {
                $test = $this->find( array('(isset(@email) && preg_match(?,@email)) && @_id!=?','/'.$this->email.'/', $this->id) );
            }

            if( $test ) {
                $errors[] = array(
                    'field' => 'email',
                    'text' => 'user with email ' . $this->email . ' already exists',
                );                           
            }                


        }

        if( isset($this->name) ) {

            if( strlen($this->name) > 256 ) {
                $errors[] = array(
                    'field' => 'name',
                    'text' => 'name cannot be longer than 256 characters',
                );                              
            }   
        }


        if( !empty($this->email) && empty($this->password) ) {
            $errors[] = array(
                'field' => 'password',
                'text' => 'password cannot be blank for registered users',
            );                            
        }

        if( isset($this->password) ) {
            
            if( $this->id && empty($this->password) ){
                $errors[] = array(
                    'field' => 'password',
                    'text' => 'password cannot be blank',
                );                            
            }

            if( strlen($this->password) < 8 ){
                $errors[] = array(
                    'field' => 'password',
                    'text' => 'password cannot be less than 8 characters',
                );                            
            }

        } 

        if( isset($this->userName) ) {
         
            if( strlen($this->userName) > 256 ) {
                $errors[] = array(
                    'field' => 'userName',
                    'text' => 'userName cannot be longer than 256 characters',
                );                              
            }   

        }

        if( count($errors) > 0 ){
            \Base::instance()->set('ERROR_LIST', $errors );
        }else {
            \Base::instance()->set('ERROR_LIST', null );
        }

        return $errors;
    }


    public function isAdmin() {
        if( isset($this->roles) and in_array('admin', $this->roles) ){
            return true;
        } else {
            return false;
        }
    }
}