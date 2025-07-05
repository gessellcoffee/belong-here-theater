<?php

namespace App\Services;

use Geocoder\Provider\GoogleMaps\GoogleMaps;
use Geocoder\Query\GeocodeQuery;
use Geocoder\StatefulGeocoder;
use Http\Adapter\Guzzle7\Client;
use Illuminate\Support\Facades\Cache;

class GeocoderService
{
    protected $geocoder;
    
    public function __construct()
    {
        $httpClient = new Client();
        $provider = new GoogleMaps($httpClient, null, config('services.google_maps.api_key'));
        $this->geocoder = new StatefulGeocoder($provider, 'en');
    }
    
    /**
     * Geocode an address string
     *
     * @param string $address
     * @return array|null
     */
    public function geocode(string $address): ?array
    {
        // Use cache to avoid unnecessary API calls
        $cacheKey = 'geocode_' . md5($address);
        
        return Cache::remember($cacheKey, now()->addDays(30), function () use ($address) {
            try {
                $result = $this->geocoder->geocodeQuery(GeocodeQuery::create($address));
                
                if (!$result->isEmpty()) {
                    $location = $result->first();
                    
                    return [
                        'formatted_address' => $location->getFormattedAddress(),
                        'latitude' => $location->getCoordinates()->getLatitude(),
                        'longitude' => $location->getCoordinates()->getLongitude(),
                        'street_number' => $this->getAddressComponent($location, 'street_number'),
                        'street_name' => $this->getAddressComponent($location, 'route'),
                        'city' => $this->getAddressComponent($location, 'locality'),
                        'state' => $this->getAddressComponent($location, 'administrative_area_level_1'),
                        'postal_code' => $this->getAddressComponent($location, 'postal_code'),
                        'country' => $this->getAddressComponent($location, 'country'),
                    ];
                }
            } catch (\Exception $e) {
                // Log the error but don't throw it to the user
                \Log::error('Geocoding error: ' . $e->getMessage());
            }
            
            return null;
        });
    }
    
    /**
     * Get suggestions for address autocomplete
     *
     * @param string $query
     * @return array
     */
    public function getAddressSuggestions(string $query): array
    {
        if (strlen($query) < 3) {
            return [];
        }
        
        // For autocomplete, we don't want to cache as heavily
        $cacheKey = 'address_suggestions_' . md5($query);
        
        return Cache::remember($cacheKey, now()->addHours(1), function () use ($query) {
            try {
                $result = $this->geocoder->geocodeQuery(GeocodeQuery::create($query));
                
                $suggestions = [];
                foreach ($result as $location) {
                    $suggestions[] = [
                        'value' => $location->getFormattedAddress(),
                        'label' => $location->getFormattedAddress(),
                    ];
                }
                
                return $suggestions;
            } catch (\Exception $e) {
                \Log::error('Address suggestion error: ' . $e->getMessage());
                return [];
            }
        });
    }
    
    /**
     * Extract address component from geocoding result
     *
     * @param \Geocoder\Location $location
     * @param string $type
     * @return string|null
     */
    protected function getAddressComponent($location, string $type): ?string
    {
        $addressComponents = $location->getAddressComponents();
        
        foreach ($addressComponents as $component) {
            if (in_array($type, $component->getTypes())) {
                return $component->getLongName();
            }
        }
        
        return null;
    }
}
