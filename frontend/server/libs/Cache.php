<?php

/**
 * Maneja el acceso al cache (usando apc user cache)
 *   
 */
class Cache
{    
    const CONTESTANT_SCOREBOARD_PREFIX = "scoreboard-";
    const CONTESTANT_SCOREBOARD_EVENTS_PREFIX = "scoreboard-events-";
    const ADMIN_SCOREBOARD_PREFIX = "scoreboard-admin-";
    const PROBLEM_STATEMENT = "statement-";
    const CONTEST_INFO = "contest-info-";
	const PROBLEM_STATS = "problem-stats-";
    
    
    private $enabled;
    protected $key;
    
    /**
     * Inicializa el cache para el key dado 
     * @param string $key el id del cache
     */
    public function __construct($prefix, $id){
        
        $this->key = $prefix.$id;
        $this->enabled = (defined('APC_USER_CACHE_ENABLED') && APC_USER_CACHE_ENABLED === true);
        
        if($this->enabled){
            Logger::log("Cache enabled for " . $this->key);
        }
        else{
            Logger::log("Cache DISABLED for " . $this->key);
        }       
        
    }

    /**
     * set
     *  
     * Si el cache está prendido, guarda value en key con el timeout dado
     *      
     * @param string $value
     * @param int $timeout   
     * @return boolean
     */
    public function set($value, $timeout){
        
        if($this->enabled === true){
            if (apc_store($this->key, $value, $timeout) === true){                
                return true;
            }
            else{
                Logger::log("apc_store failed for key: " . $this->key);
                return false;
            }
        }
        
        return false;
        
    }

    /**
     * delete
     * 
     * Si el cache esta prendido, invalida el key del cache
     *      
     * @return boolean
     */
    public function delete()
    {
        if($this->enabled === true){
            if (apc_delete($this->key) === true){
                return true;                
            }
            else{
                Logger::log("Failed to invalidate cache for key: " . $this->key);
                return false;
            }
        }
        
        return false;
    }

    /**
     * get
     * 
     * Si el cache está prendido y la clave está en el cache, regresa el valor. Si no está, regresa null
     * 
     * @return mixed 
     */
    public function get()
    {        
        if($this->enabled === true){
            if($result = apc_fetch($this->key)){
                return $result;
            }
            else{
                Logger::log("Cache miss for key: " . $this->key);
            }
                
        }
        
        return null;
    }
}
