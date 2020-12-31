<?php
namespace rohsyl\BookingSync\OAuth2\Client;


use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericResourceOwner;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

/**
 * Class BookingSyncProvider
 * @package rohsyl\BookingSync\OAuth2\Client
 * @author rohs
 */
class BookingSyncProvider extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * @var string
     */
    private $responseResourceOwnerId = 'id';

    /**
     * @var string
     */
    public $version = 'v3';

    public function getBaseAuthorizationUrl()
    {
        return 'https://www.bookingsync.com/oauth/authorize';
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://www.bookingsync.com/oauth/token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return 'https://www.bookingsync.com/api/'.$this->version.'/accounts';
    }

    protected function getDefaultScopes()
    {
        return ['public', 'bookings_write_owned', 'bookings_read', 'bookings_write',
            'clients_read', 'clients_write',
            'inquiries_read', 'inquiries_write',
            'payments_read', 'payments_write',
            'rates_read', 'rates_write',
            'rentals_read', 'rentals_write',
            'reviews_write'];
    }

    protected function getScopeSeparator()
    {
        return ' ';
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (!empty($data['error'])) {
            $error = $data['error'];
            if (!empty($data['error_description'])) {
                $error .= ' : ' . $data['error_description'];
            }
            throw new IdentityProviderException($error, 1, $data);
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new GenericResourceOwner($response, $this->responseResourceOwnerId);
    }
}