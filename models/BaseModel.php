<?php

abstract class BaseModel extends \DB\Jig\Mapper implements JsonSerializable {
    
    public function __construct(\DB\Jig $db, $file ) {

        parent::__construct( $db, $file );

        $this->beforeinsert(array($this,'beforeSave'));
        $this->beforeupdate(array($this,'beforeSave'));
        $this->onload(array($this,'afterLoad'));

    }


    //
    // Turn DB records into nice usable PHP object format 
    //
    public function afterLoad( $self ) {
        // parse the json properties database field and save each key as highlevel key on the object
        // we trust all fields in the database because we whitelist which ones may enter
        // in beforeSave function
        if( isset($self->properties) ) {
            $json_properties = json_decode($self->properties, true );
            if( $json_properties ) {
                foreach( $json_properties as $key => $value ) {
                    $self->adhoc[$key]['value']=$value;
                }           
            }            
        }
    }

    public function hydrate( $object, $attr_accessible=Null ) {
        foreach( $object as $key => $value ) {
            if( !isset($attr_accessible) || in_array($key, $attr_accessible) ) {
                $this->$key = $value;
            }
        }
    }

    public function jsonSerialize() {

        // turn the DB mapper object keys and values into a PHP array
        // this gives us a copy of the object keys and values to mess with 
        // leaving the hydrated database mapper object untouched in case it needs to be used after serialization
        $output = $this->cast();

        if( isset($output['created']) ) {
            $output['created'] = (new DateTime($output['created']))->format(DateTime::ISO8601);
        }

        if( isset($output['updated']) ) {
            $output['updated'] = (new DateTime($output['updated']))->format(DateTime::ISO8601);
        }

        return $output;

    }


    abstract function validate();
}