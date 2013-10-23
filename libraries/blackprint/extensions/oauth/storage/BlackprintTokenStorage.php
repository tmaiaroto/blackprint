<?php
/**
 * Blackprint Token Storage class uses Lithium's Session class
 * to store OAuth tokens in session storage instead of using any
 * of the core classes in OAuth library. In this case, Blackprint
 * is using session storage in MongoDB.
*/
namespace blackprint\extensions\oauth\storage;

use lithium\storage\Session;
use OAuth\Common\Token\TokenInterface;
use OAuth\Common\Storage\Exception\TokenNotFoundException;

/*
 * Stores a token in-memory only (destroyed at end of script execution).
 */
class BlackprintTokenStorage implements \OAuth\Common\Storage\TokenStorageInterface
{
    /**
     * @var string
     */
    protected $sessionVariableName;

    /**
     * @param bool   $startSession        Whether or not to start the session upon construction.
     * @param string $sessionVariableName the variable name
     */
    public function __construct($startSession = true, $sessionVariableName = 'lusitanian_oauth_token')
    {
        $this->sessionVariableName = $sessionVariableName;
    }

    /**
     * {@inheritDoc}
     */
    public function retrieveAccessToken($service)
    {
        if ($this->hasAccessToken($service)) {
            $sessionVar = Session::read($this->sessionVariableName . '.' . $service, array('name' => 'blackprint'));
            $sessionVar = (!empty($sessionVar)) ? unserialize($sessionVar):false;
            return $sessionVar;
        }

        throw new TokenNotFoundException('Token not found in session, are you sure you stored it?');
    }

    /**
     * {@inheritDoc}
     */
    public function storeAccessToken($service, TokenInterface $token)
    {
        $sessionVar = Session::read($this->sessionVariableName);
        if(is_object($sessionVar)) {
            $sessionVar = $sessionVar->data();
        }
        if(!empty($sessionVar) && is_array($sessionVar)) {
            Session::write($this->sessionVariableName . '.' . $service, serialize($token), array('name' => 'blackprint'));
        } else {
            // Data must be serialized for MongoDB because PHP's objects can't be stored. Only arrays...
            // And in this case, we can't loop the class's properties and build an associative array, they are protected.
            Session::write($this->sessionVariableName . '.' . $service, serialize($token), array('name' => 'blackprint'));
        }

        // allow chaining
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function hasAccessToken($service)
    {
        $sessionVar = Session::read($this->sessionVariableName . '.' . $service, array('name' => 'blackprint'));
        $sessionVar = !empty($sessionVar) ? unserialize($sessionVar):false;
        return $sessionVar;
    }

    /**
     * {@inheritDoc}
     */
    public function clearToken($service)
    {
        Session::delete($this->sessionVariableName . '.' . $service, array('name' => 'blackprint'));

        // allow chaining
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function clearAllTokens()
    {
        Session::delete($this->sessionVariableName, array('name' => 'blackprint'));

        // allow chaining
        return $this;
    }

    public function __destruct()
    {
       
    }
}
?>