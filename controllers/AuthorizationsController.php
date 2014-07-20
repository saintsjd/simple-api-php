<?php
class AuthorizationsController {

    function create($f3) {

        $request_json = json_decode( $f3->get('BODY'), true );
        $raw_data = $request_json['authorizations'][0];

        $user = new UserModel();
        $user->load( array("@email = ?", $raw_data['email'] ) );

        if( $user->dry() ){
            $f3->error( 403 );            
        }

        $verified = password_verify ( $raw_data['password'] , $user->password );

        if( !$verified ) {
            $f3->error( 403 );               
        }

        $key = pack('H*', getenv("SECRET_KEY") );
        $plaintext = $f3->serialize(array($user->id, time() ));

        # create a random IV to use with CBC encoding
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $plaintext, MCRYPT_MODE_CBC, $iv);

        # prepend the IV for it to be available for decryption
        $ciphertext = $iv . $ciphertext;
        $ciphertext_base64 = base64_encode($ciphertext);

        $token = $ciphertext_base64;

        $authorization = array( "token" => $token );

        echo JsonApi::response( 'authorizations', $authorization );
    }



}
