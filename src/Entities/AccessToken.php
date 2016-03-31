<?php

namespace LucaDegasperi\OAuth2Server\Entities;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Illuminate\Database\Eloquent\Model;
use League\OAuth2\Server\Entities\Interfaces\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\Interfaces\ClientEntityInterface;
use League\OAuth2\Server\Entities\Interfaces\ScopeEntityInterface;
use Carbon\Carbon;

/**
 * @property mixed client
 * @property string client_id
 * @property int|string user_id
 * @property Carbon expires_at
 * @property mixed id
 * @property string token
 * @property mixed scopes
 */
class AccessToken extends Model implements AccessTokenEntityInterface
{

    protected $table = 'oauth_access_tokens';

    protected $dates = ['expires_at'];

    /**
     * Generate a JWT from the access token
     *
     * @param string $privateKeyPath
     *
     * @return string
     */
    public function convertToJWT($privateKeyPath)
    {
        return (new Builder())
            ->setAudience($this->getClient()->getIdentifier())
            ->setId($this->getIdentifier(), true)
            ->setIssuedAt(time())
            ->setNotBefore(time())
            ->setExpiration($this->getExpiryDateTime()->getTimestamp())
            ->setSubject($this->getUserIdentifier())
            ->set('scopes', $this->getScopes())
            ->sign(new Sha256(), new Key($privateKeyPath))
            ->getToken();
    }

    /**
     * Get the token's identifier.
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->token;
    }

    /**
     * Set the token's identifier.
     *
     * @param $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->token = $identifier;
    }

    /**
     * Get the token's expiry date time.
     *
     * @return \DateTime
     */
    public function getExpiryDateTime()
    {
        return $this->expires_at;
    }

    /**
     * Set the date time when the token expires.
     *
     * @param \DateTime $dateTime
     */
    public function setExpiryDateTime(\DateTime $dateTime)
    {
        $this->expires_at = Carbon::instance($dateTime);
    }

    /**
     * Set the identifier of the user associated with the token.
     *
     * @param string|int $identifier The identifier of the user
     */
    public function setUserIdentifier($identifier)
    {
        $this->user_id = $identifier;
    }

    /**
     * Get the token user's identifier.
     *
     * @return string|int
     */
    public function getUserIdentifier()
    {
        return $this->user_id;
    }

    /**
     * Get the client that the token was issued to.
     *
     * @return ClientEntityInterface
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set the client that the token was issued to.
     *
     * @param \League\OAuth2\Server\Entities\Interfaces\ClientEntityInterface $client
     */
    public function setClient(ClientEntityInterface $client)
    {
        $this->client()->save($client);
    }

    /**
     * Associate a scope with the token.
     *
     * @param \League\OAuth2\Server\Entities\Interfaces\ScopeEntityInterface $scope
     */
    public function addScope(ScopeEntityInterface $scope)
    {
        $this->scopes()->attach($scope);
    }

    /**
     * Return an array of scopes associated with the token.
     *
     * @return ScopeEntityInterface[]
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * Has the token expired?
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->expires_at->lt(new Carbon());
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function scopes()
    {
        return $this->belongsToMany(Scope::class, 'oauth_access_token_scopes');
    }

    public function refreshToken()
    {
        return $this->hasOne(RefreshToken::class);
    }
}