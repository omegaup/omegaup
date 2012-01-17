<?php

class Cache
{
	protected $prefix;
	protected $memcache;
	
	public function __construct($prefix = '')
	{
		$this->prefix = $prefix;
		
		if( defined('OMEGAUP_MEMCACHE_DIABLED') )
		{
			$this->memcache = null;	
		}
		else
		{
			$this->memcache = new Memcache;
			if( !$this->memcache->connect(OMEGAUP_MEMCACHE_HOST, OMEGAUP_MEMCACHE_PORT) )
			{
				$this->memcache = null;
				/// @todo Log failure to connect to memcache server
			}
		}
	}
	
	public function set($key, $value, $timeout, $prefix = null)
	{
		if( $this->memcache != null )
		{
			$this->memcache->add($this->getKey($key, $prefix), $value, 0, $timeout);
		}
	}
	
	public function delete($key, $prefix = null)
	{
		if( $this->memcache != null )
		{
			$this->memcache->delete($this->getKey($key, $prefix), 0);
		}
	}
	
	public function get($key, $prefix = null)
	{
		$result = null;
		if( $this->memcache != null )
		{
			$result = $this->memcache->get($this->getKey($key, $prefix));
		}
		
		return $result;
	}
	
	private function getKey($key, $prefix)
	{
		if( $prefix == null )
			$prefix = $this->prefix;
		return "$prefix:$key";
	}
}
