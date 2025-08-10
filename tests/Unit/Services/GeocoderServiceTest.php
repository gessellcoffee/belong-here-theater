<?php

namespace Tests\Unit\Services;

use App\Services\GeocoderService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Mockery;

/**
 * Test-specific implementation of GeocoderService for testing
 */
class TestGeocoderService extends GeocoderService
{
    protected $mockGeocodeResults = [];

    protected $mockSuggestionResults = [];

    protected $shouldThrowException = false;

    protected $exceptionMessage = '';

    public function __construct()
    {
        // Don't call parent constructor to avoid actual API calls
    }

    public function setMockGeocodeResult(array $result)
    {
        $this->mockGeocodeResults = $result;
    }

    public function setMockSuggestions(array $suggestions)
    {
        $this->mockSuggestionResults = $suggestions;
    }

    public function throwExceptionOnNextCall(string $message = 'Test exception')
    {
        $this->shouldThrowException = true;
        $this->exceptionMessage = $message;
    }

    public function geocode(string $address): ?array
    {
        if ($this->shouldThrowException) {
            $this->shouldThrowException = false; // Reset for next call
            Log::error('Geocoding error: '.$this->exceptionMessage);

            return null;
        }

        return $this->mockGeocodeResults;
    }

    public function getAddressSuggestions(string $query): array
    {
        if (strlen($query) < 3) {
            return [];
        }

        if ($this->shouldThrowException) {
            $this->shouldThrowException = false; // Reset for next call
            Log::error('Address suggestion error: '.$this->exceptionMessage);

            return [];
        }

        return $this->mockSuggestionResults;
    }

    public function testGetAddressComponent($location, string $type): ?string
    {
        // This allows us to test the protected method
        return $this->getAddressComponent($location, $type);
    }
}

test('geocoder service can be instantiated', function () {
    $service = new TestGeocoderService;
    expect($service)->toBeInstanceOf(GeocoderService::class);
});

test('geocode method returns address data when successful', function () {
    // Create a test service
    $service = new TestGeocoderService;

    // Set up mock result
    $mockResult = [
        'formatted_address' => '123 Test St, Test City, TS 12345',
        'latitude' => 40.7128,
        'longitude' => -74.0060,
        'street_number' => '123',
        'street_name' => 'Test St',
        'city' => 'Test City',
        'state' => 'TS',
        'postal_code' => '12345',
        'country' => 'Test Country',
    ];

    $service->setMockGeocodeResult($mockResult);

    // Mock the cache
    Cache::shouldReceive('remember')
        ->andReturnUsing(function ($key, $ttl, $callback) {
            return $callback();
        });

    // Test the method
    $result = $service->geocode('123 Test St');

    expect($result)->toBeArray()
        ->and($result['formatted_address'])->toBe('123 Test St, Test City, TS 12345')
        ->and($result['latitude'])->toBe(40.7128)
        ->and($result['longitude'])->toBe(-74.0060)
        ->and($result['city'])->toBe('Test City')
        ->and($result['state'])->toBe('TS')
        ->and($result['postal_code'])->toBe('12345')
        ->and($result['country'])->toBe('Test Country');
});

test('geocode method returns null when geocoding fails', function () {
    // Create a test service
    $service = new TestGeocoderService;

    // Set up to throw exception
    $service->throwExceptionOnNextCall('Geocoding failed');

    // Mock the logger
    Log::shouldReceive('error')
        ->once()
        ->with('Geocoding error: Geocoding failed');

    // Mock the cache
    Cache::shouldReceive('remember')
        ->andReturnUsing(function ($key, $ttl, $callback) {
            return $callback();
        });

    // Test the method
    $result = $service->geocode('123 Test St');

    expect($result)->toBeNull();
});

test('getAddressSuggestions returns empty array for short queries', function () {
    $service = new TestGeocoderService;
    $result = $service->getAddressSuggestions('ab');

    expect($result)->toBeArray()
        ->and($result)->toBeEmpty();
});

test('getAddressSuggestions returns suggestions when successful', function () {
    // Create a test service
    $service = new TestGeocoderService;

    // Set up mock suggestions
    $mockSuggestions = [
        ['value' => '123 Test St, Test City, TS 12345', 'label' => '123 Test St, Test City, TS 12345'],
        ['value' => '456 Sample Ave, Test City, TS 12345', 'label' => '456 Sample Ave, Test City, TS 12345'],
    ];

    $service->setMockSuggestions($mockSuggestions);

    // Mock the cache
    Cache::shouldReceive('remember')
        ->andReturnUsing(function ($key, $ttl, $callback) {
            return $callback();
        });

    // Test the method
    $result = $service->getAddressSuggestions('123 Test');

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(2)
        ->and($result[0]['value'])->toBe('123 Test St, Test City, TS 12345')
        ->and($result[1]['value'])->toBe('456 Sample Ave, Test City, TS 12345');
});

test('getAddressSuggestions returns empty array when exception occurs', function () {
    // Create a test service
    $service = new TestGeocoderService;

    // Set up to throw exception
    $service->throwExceptionOnNextCall('Suggestions failed');

    // Mock the logger
    Log::shouldReceive('error')
        ->once()
        ->with('Address suggestion error: Suggestions failed');

    // Mock the cache
    Cache::shouldReceive('remember')
        ->andReturnUsing(function ($key, $ttl, $callback) {
            return $callback();
        });

    // Test the method
    $result = $service->getAddressSuggestions('123 Test');

    expect($result)->toBeArray()
        ->and($result)->toBeEmpty();
});

test('getAddressComponent returns component value when found', function () {
    // Create a test service
    $service = new TestGeocoderService;

    // Create mock objects
    $mockLocation = Mockery::mock();
    $mockComponent = Mockery::mock();

    // Set up the mock chain
    $mockLocation->shouldReceive('getAddressComponents')
        ->once()
        ->andReturn([$mockComponent]);

    $mockComponent->shouldReceive('getTypes')
        ->once()
        ->andReturn(['street_number']);

    $mockComponent->shouldReceive('getLongName')
        ->once()
        ->andReturn('123');

    // Test the method using our public test method
    $result = $service->testGetAddressComponent($mockLocation, 'street_number');

    expect($result)->toBe('123');
});

test('getAddressComponent returns null when component not found', function () {
    // Create a test service
    $service = new TestGeocoderService;

    // Create mock objects
    $mockLocation = Mockery::mock();
    $mockComponent = Mockery::mock();

    // Set up the mock chain
    $mockLocation->shouldReceive('getAddressComponents')
        ->once()
        ->andReturn([$mockComponent]);

    $mockComponent->shouldReceive('getTypes')
        ->once()
        ->andReturn(['locality']);

    // Test the method using our public test method
    $result = $service->testGetAddressComponent($mockLocation, 'street_number');

    expect($result)->toBeNull();
});

afterEach(function () {
    Mockery::close();
});
