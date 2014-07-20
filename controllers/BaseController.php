<?php
class BaseController{

    function beforeRoute($f3) {

        // if a authentication token comes in through basic auth, try to load the corresponding user object from the database
        if( isset($f3->get('SERVER')['PHP_AUTH_USER']) ) {
            $key = pack('H*', getenv("SECRET_KEY") );
            $ciphertext_base64 = $f3->get('SERVER')['PHP_AUTH_USER'];
            $ciphertext_dec = base64_decode($ciphertext_base64);
            $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
            $iv_dec = substr($ciphertext_dec, 0, $iv_size);
            $ciphertext_dec = substr($ciphertext_dec, $iv_size);
            $plaintext_dec = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);
            $token_data = $f3->unserialize($plaintext_dec);

            $user_id = $token_data[0];
            $timestamp = $token_data[1];
            //$password_hash = $token_data[2];

            $user = new UserModel();
            $user->load( array("@_id = ?", $user_id) );

            if( $user->dry() ){
                $f3->error( 403 );            
            }

            // invalidate tokens created with old passwords
            // if( $user->password != $password_hash ){
            //     $f3->error( 403 );                            
            // }

            $f3->set('authenticated_user', $user );
        }

    }


}
