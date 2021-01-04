<?php
namespace rohsyl\BookingSync\OAuth2\Client\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use rohsyl\BookingSync\OAuth2\Client\BookingSyncProvider;

/**
 * Class BookingSyncProviderTest
 * @package ${NAMESPACE}
 * @author rohs
 */
class BookingSyncProviderTest extends TestCase
{

    protected $provider;

    protected function setUp(): void
    {
        $this->provider = new BookingSyncProvider([
            'clientId'          => 'mock_id',
            'clientSecret'      => 'mock_secret',
            'redirectUri'       => 'none',
        ]);
    }


    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('approval_prompt', $query);
        $this->assertNotNull($this->provider->getState());
    }

    public function testUrlAuthorize()
    {
        $url = $this->provider->getBaseAuthorizationUrl();
        $uri = parse_url($url);

        $this->assertEquals('/oauth/authorize', $uri['path']);
    }

    public function testUrlAccessToken()
    {
        $url = $this->provider->getBaseAccessTokenUrl([]);
        $uri = parse_url($url);

        $this->assertEquals('/oauth/token', $uri['path']);
    }

    public function testGetAccessToken()
    {

        $handler = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], '{"access_token": "mock_access_token", "expires_in": 3600, "refresh_token": "mock_refresh_token", "uid": 1}')
        ]);
        $handlerStack = HandlerStack::create($handler);

        $httpClient = new Client([
            'handler' => $handlerStack,
        ]);
        $this->provider->setHttpClient($httpClient);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertLessThanOrEqual(time() + 3600, $token->getExpires());
        $this->assertGreaterThanOrEqual(time(), $token->getExpires());
        $this->assertEquals('mock_refresh_token', $token->getRefreshToken());
    }
}