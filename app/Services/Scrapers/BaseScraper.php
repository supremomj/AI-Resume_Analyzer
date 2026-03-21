<?php

namespace App\Services\Scrapers;

abstract class BaseScraper implements JobScraperInterface
{
    /**
     * Extract location from text (Philippine cities)
     */
    protected function extractLocation(string $text): string
    {
        $philippineCities = [
            'Manila', 'Makati', 'Quezon City', 'Pasig', 'Taguig', 'BGC', 'Ortigas',
            'Mandaluyong', 'San Juan', 'Parañaque', 'Las Piñas', 'Muntinlupa',
            'Cebu City', 'Davao City', 'Iloilo City', 'Bacolod', 'Cagayan de Oro',
            'Zamboanga City', 'Dagupan', 'Baguio', 'Angeles City', 'Batangas City',
            'Lipa', 'Calamba', 'Santa Rosa', 'Antipolo', 'Marikina', 'Valenzuela',
            'Caloocan', 'Malabon', 'Navotas', 'Pateros', 'Pasay', 'Metro Manila',
            'NCR', 'MM', 'National Capital Region'
        ];

        $text = ' ' . $text . ' ';

        foreach ($philippineCities as $city) {
            if (stripos($text, $city) !== false) {
                if (stripos($city, 'City') !== false || stripos($city, 'Metro') !== false) {
                    return $city;
                }
            }
        }

        if (stripos($text, 'metro manila') !== false || stripos($text, 'ncr') !== false) {
            return 'Metro Manila';
        }

        if (stripos($text, 'philippines') !== false || stripos($text, 'ph') !== false) {
            return 'Philippines';
        }

        return 'Philippines';
    }
}
