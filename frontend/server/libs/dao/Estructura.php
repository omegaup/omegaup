<?php
		/** Table Data Access Object.
       *	 
		  * Esta clase abstracta comprende metodos comunes para todas las clases DAO que mapean una tabla
		  * @author alanboy
		  * @access private
		  * @abstract
		  * @package docs
		  */
		abstract class DAO
		{

		protected static $isTrans = false;
		protected static $transCount = 0;
		
		public static function transBegin (){
			
			self::$transCount ++;
			
            // @TODO Reactivate this
            Logger::log("Iniciando transaccion (".self::$transCount.")");
			
			if(self::$isTrans){
				Logger::log("Transaccion ya ha sido iniciada antes.");
				return;
			}
			
			global $conn;
			$conn->StartTrans();
			self::$isTrans = true;
			
		}

		public static function transEnd (  ){
			
			if(!self::$isTrans){
				Logger::log("Transaccion commit pero no hay transaccion activa !!.");
				return;
			}
			
			self::$transCount --;
			Logger::log("Terminando transaccion (".self::$transCount.")");
			
			if(self::$transCount > 0){
				return;
			}
			global $conn;
			$conn->CompleteTrans();
			Logger::log("Transaccion commit !!");
			self::$isTrans = false;
		}
		public static function transRollback (  ){
			if(!self::$isTrans){
				Logger::log("Transaccion rollback pero no hay transaccion activa !!.");
				return;
			}
			
			self::$transCount = 0;
			global $conn;
			$conn->FailTrans();
			Logger::log("Transaccion rollback !");
			self::$isTrans = false;
		}
		}
		/** Value Object.
		  * 
		  * Esta clase abstracta comprende metodos comunes para todas los objetos VO
		  * @author alanboy
		  * @access private
		  * @package docs
		  * 
		  */
		abstract class VO
		{

	        /**
	          *	Obtener una representacion en forma de arreglo.
	          *	
	          * Este metodo transforma todas las propiedades este objeto en un arreglo asociativo.
	          *	
	          * @returns Array Un arreglo asociativo que describe a este objeto.
	          **/
			function asArray(){
				return get_object_vars($this);
			}
                  
                  /**
	          *	Obtener una representacion en forma de arreglo sin mostrar los campos NULL.
	          *	
	          * Este metodo transforma todas las propiedades este objeto en un arreglo asociativo
                  * sin mostrar los campos NULL.
	          *	
	          * @returns Array Un arreglo asociativo que describe a este objeto escondiendo los NULL.
	          **/   
                        function asArrayWithoutNulls()
                        {
                            // Get the complete representation of the array
                            $completeArray = get_object_vars($this);
                            
                            $returnArray = array();
                            
                            foreach( $completeArray as $key => $value )
                                if(!is_null($value))
                                {
                                    $returnArray[$key] = $value;
                                    
                                }
                            
                            return $returnArray;
                        
                        }
                        
                        
                        
                /**
	          *	Obtener una representacion en forma de arreglo sólo con los campos 
                  *     definidos en $filter 
	          *	
	          * Este metodo transforma todas las propiedades este objeto en un arreglo asociativo
                  * sólo mostrando los campos definidos por filters
	          *	
	          * @returns Array Un arreglo filtrado asociativo que describe a este objeto.
	          **/   
                        function asFilteredArray($filters)
                        {
                            // Get the complete representation of the array
                            $completeArray = get_object_vars($this);
                            
                            // Declare an empty array to return
                            $returnArray = array();
                            
                            foreach( $filters as $filter )
                            {                                
                                // Only return properties included in $filters array
                                if (isset ($completeArray[$filter]))
                                {
                                    $returnArray[$filter] = $completeArray[$filter];                                
                                }
                                else
                                {
                                    $returnArray[$filter] = NULL;
                                }
                            }
                            
                            return $returnArray;
                        
                        }


			/**
			  *
			  *
			  **/
			protected function toUnixTime( Array $fields ){
				foreach( $fields as $f ){
					$this->$f = strtotime( $this->$f );
				}
			}

		}
