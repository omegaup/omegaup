<?php


/**
 * Description of controller
 *
 * @author joemmanuel
 */
class Controller
{
    protected $validator;
    protected $current_user_id;
    protected $current_user_obj;
    
    private static $_sessionManager;
                    
    /**
     * Regresa el SessionManager que abstrae las funciones nativas de sesión
     * 
     * @return SessionManager
     */
    public static function getSessionManagerInstance(){
        
        // Si no existe, crearlo
        if (is_null( self::$_sessionManager)){
            self::$_sessionManager = new SessionManager();
        }        
        return self::$_sessionManager;
    }

}
?>
