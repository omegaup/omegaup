<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

		/** Table Data Access Object.
		  * 
		  * Esta clase abstracta comprende metodos comunes para todas las clases DAO que mapean una tabla
		  * @access private
		  * @abstract
		  */
		abstract class DAO
		{
			protected static $isTrans = false;
			protected static $transCount = 0;
			protected static $redisConection = NULL;
			protected static function log ($m = null) {
				// Your logging call here.
			}
			public static function transBegin() {
				self::$transCount ++;
				self::log("Iniciando transaccion (".self::$transCount.")");
				if(self::$isTrans){
					//self::log("Transaccion ya ha sido iniciada antes.");
					return;
				}

			global $conn;
			$conn->StartTrans();
			self::$isTrans = true;

		}
		public static function transEnd (  ){
			
			if(!self::$isTrans){
				self::log("Transaccion commit pero no hay transaccion activa !!.");
				return;
			}

			self::$transCount --;
			self::log("Terminando transaccion (".self::$transCount.")");

			if(self::$transCount > 0){
				return;
			}
			global $conn;
			$conn->CompleteTrans();
			self::log("Transaccion commit !!");
			self::$isTrans = false;
		}
		public static function transRollback (  ){
			if(!self::$isTrans){
				self::log("Transaccion rollback pero no hay transaccion activa !!.");
				return;
			}
			
			self::$transCount = 0;
			global $conn;
			$conn->FailTrans();
			self::log("Transaccion rollback !");
			self::$isTrans = false;
		}
		}
		/** Value Object.
		  * 
		  * Esta clase abstracta comprende metodos comunes para todas los objetos VO
		  * @access private
		  * @package docs
		  * 
		  */
		abstract class VO
		{

			function asArray(){
				return get_object_vars($this);
			}

			protected static function object_to_array($mixed) {
				if(is_object($mixed)) $mixed = (array) $mixed;
				if(is_array($mixed)) {
				    $new = array();
				    foreach($mixed as $key => $val) {
				        $key = preg_replace("/^\\0(.*)\\0/","",$key);
				        $new[$key] = object_to_array($val);
				    }
				} 
				else $new = $mixed;
				return $new; 
			}

			function __call($method, $params) {
				 $var = substr($method, 3);
				 $var = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $var)); 

				 if (strncasecmp($method, "get", 3)==0) {
					 return $this->$var;
				 } else if (strncasecmp($method, "set", 3)==0) {
					 $this->$var = $params[0];
				 } else {
					 throw new BadMethodCallException($method);
				 }
			}

		public function asFilteredArray($filters)
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

		protected function toUnixTime(Array $fields) {
			foreach ($fields as $f) {
				$this->$f = strtotime($this->$f);
			}
		}
		}
