<?php
/**
 * DateHelper - Centralized date handling for SmartAd system
 * Auto-detects system locale and supports multiple date formats
 * 
 * @package SmartAd\Helpers
 * @version 2.0
 * @date 2025-01-08
 */

class DateHelper
{
    /**
     * Database date format (always yyyy-mm-dd for consistency)
     */
    const DATABASE_FORMAT = 'Y-m-d';
    
    /**
     * Common date formats to try (in order of likelihood)
     */
    const COMMON_FORMATS = [
        'd/m/Y',    // 25/12/2024 (European)
        'm/d/Y',    // 12/25/2024 (US)
        'Y-m-d',    // 2024-12-25 (ISO)
        'd-m-Y',    // 25-12-2024 (European with dashes)
        'm-d-Y',    // 12-25-2024 (US with dashes)
        'd.m.Y',    // 25.12.2024 (European with dots)
        'Y/m/d',    // 2024/12/25 (ISO with slashes)
    ];
    
    /**
     * System date format (detected from locale)
     */
    private static $systemFormat = null;
    
    /**
     * Get system date format from locale
     * 
     * @return string Detected system date format
     */
    public static function getSystemFormat()
    {
        if (self::$systemFormat === null) {
            // Try to detect from system locale
            $locale = setlocale(LC_TIME, 0);
            
            // Check common patterns based on locale
            if (strpos($locale, 'en_US') !== false || strpos($locale, 'en-US') !== false) {
                self::$systemFormat = 'm/d/Y'; // US format
            } elseif (strpos($locale, 'en_GB') !== false || strpos($locale, 'en-GB') !== false) {
                self::$systemFormat = 'd/m/Y'; // UK format
            } elseif (strpos($locale, 'de_') !== false || strpos($locale, 'fr_') !== false || 
                     strpos($locale, 'es_') !== false || strpos($locale, 'it_') !== false) {
                self::$systemFormat = 'd/m/Y'; // European format
            } else {
                // Default to European format (most common globally)
                self::$systemFormat = 'd/m/Y';
            }
        }
        
        return self::$systemFormat;
    }
    
    /**
     * Auto-detect date format from input string
     * 
     * @param string $date Date string to analyze
     * @return string|null Detected format or null if not recognized
     */
    public static function detectDateFormat($date)
    {
        if (empty($date)) {
            return null;
        }
        
        // Try each format to see which one works
        foreach (self::COMMON_FORMATS as $format) {
            $dateTime = DateTime::createFromFormat($format, $date);
            if ($dateTime && $dateTime->format($format) === $date) {
                return $format;
            }
        }
        
        return null;
    }
    
    /**
     * Validate date in any supported format
     * 
     * @param string $date Date string to validate
     * @return bool True if valid date in any supported format
     */
    public static function isValidDate($date)
    {
        return self::detectDateFormat($date) !== null;
    }
    
    /**
     * Convert any supported date format to database format (yyyy-mm-dd)
     * 
     * @param string $date Date in any supported format
     * @return string|null Date in yyyy-mm-dd format or null if invalid
     */
    public static function toDatabase($date)
    {
        if (empty($date)) {
            return null;
        }
        
        $format = self::detectDateFormat($date);
        if (!$format) {
            return null;
        }
        
        $dateTime = DateTime::createFromFormat($format, $date);
        if (!$dateTime) {
            return null;
        }
        
        return $dateTime->format(self::DATABASE_FORMAT);
    }
    
    /**
     * Convert database format (yyyy-mm-dd) to system format
     * 
     * @param string $date Date in yyyy-mm-dd format
     * @return string|null Date in system format or null if invalid
     */
    public static function fromDatabase($date)
    {
        if (empty($date)) {
            return null;
        }
        
        $dateTime = DateTime::createFromFormat(self::DATABASE_FORMAT, $date);
        if (!$dateTime) {
            return null;
        }
        
        return $dateTime->format(self::getSystemFormat());
    }
    
    /**
     * Convert any supported date format to system format
     * 
     * @param string $date Date in any supported format
     * @return string|null Date in system format or null if invalid
     */
    public static function toSystem($date)
    {
        if (empty($date)) {
            return null;
        }
        
        $format = self::detectDateFormat($date);
        if (!$format) {
            return null;
        }
        
        $dateTime = DateTime::createFromFormat($format, $date);
        if (!$dateTime) {
            return null;
        }
        
        return $dateTime->format(self::getSystemFormat());
    }
    
    /**
     * Get current date in system format
     * 
     * @return string Current date in system format
     */
    public static function getCurrentSystemDate()
    {
        return date(self::getSystemFormat());
    }
    
    /**
     * Get current date in database format
     * 
     * @return string Current date in database format
     */
    public static function getCurrentDatabaseDate()
    {
        return date(self::DATABASE_FORMAT);
    }
    
    /**
     * Format date for display with custom format
     * 
     * @param string $date Date in any supported format
     * @param string $format Output format (default: system format)
     * @return string|null Formatted date or null if invalid
     */
    public static function formatDate($date, $format = null)
    {
        if (empty($date)) {
            return null;
        }
        
        $inputFormat = self::detectDateFormat($date);
        if (!$inputFormat) {
            return null;
        }
        
        $dateTime = DateTime::createFromFormat($inputFormat, $date);
        if (!$dateTime) {
            return null;
        }
        
        $outputFormat = $format ?: self::getSystemFormat();
        return $dateTime->format($outputFormat);
    }
    
    /**
     * Add days to a date
     * 
     * @param string $date Date in any supported format
     * @param int $days Number of days to add (can be negative)
     * @return string|null New date in system format or null if invalid
     */
    public static function addDays($date, $days)
    {
        $format = self::detectDateFormat($date);
        if (!$format) {
            return null;
        }
        
        $dateTime = DateTime::createFromFormat($format, $date);
        if (!$dateTime) {
            return null;
        }
        
        $dateTime->add(new DateInterval("P{$days}D"));
        return $dateTime->format(self::getSystemFormat());
    }
    
    /**
     * Calculate difference between two dates in days
     * 
     * @param string $date1 First date in any supported format
     * @param string $date2 Second date in any supported format
     * @return int|null Difference in days or null if invalid dates
     */
    public static function getDaysDifference($date1, $date2)
    {
        $format1 = self::detectDateFormat($date1);
        $format2 = self::detectDateFormat($date2);
        
        if (!$format1 || !$format2) {
            return null;
        }
        
        $dateTime1 = DateTime::createFromFormat($format1, $date1);
        $dateTime2 = DateTime::createFromFormat($format2, $date2);
        
        if (!$dateTime1 || !$dateTime2) {
            return null;
        }
        
        $diff = $dateTime1->diff($dateTime2);
        return $diff->days;
    }
    
    /**
     * Check if date is in the past
     * 
     * @param string $date Date in any supported format
     * @return bool True if date is in the past
     */
    public static function isPastDate($date)
    {
        $format = self::detectDateFormat($date);
        if (!$format) {
            return false;
        }
        
        $dateTime = DateTime::createFromFormat($format, $date);
        if (!$dateTime) {
            return false;
        }
        
        $today = new DateTime();
        return $dateTime < $today;
    }
    
    /**
     * Check if date is in the future
     * 
     * @param string $date Date in any supported format
     * @return bool True if date is in the future
     */
    public static function isFutureDate($date)
    {
        $format = self::detectDateFormat($date);
        if (!$format) {
            return false;
        }
        
        $dateTime = DateTime::createFromFormat($format, $date);
        if (!$dateTime) {
            return false;
        }
        
        $today = new DateTime();
        return $dateTime > $today;
    }
    
    /**
     * Get supported date formats for user guidance
     * 
     * @return array Array of supported formats with examples
     */
    public static function getSupportedFormats()
    {
        return [
            'd/m/Y' => '25/12/2024 (European)',
            'm/d/Y' => '12/25/2024 (US)',
            'Y-m-d' => '2024-12-25 (ISO)',
            'd-m-Y' => '25-12-2024 (European with dashes)',
            'm-d-Y' => '12-25-2024 (US with dashes)',
            'd.m.Y' => '25.12.2024 (European with dots)',
            'Y/m/d' => '2024/12/25 (ISO with slashes)',
        ];
    }
    
    /**
     * Get system format description for user guidance
     * 
     * @return string Description of detected system format
     */
    public static function getSystemFormatDescription()
    {
        $format = self::getSystemFormat();
        $formats = self::getSupportedFormats();
        return $formats[$format] ?? 'Unknown format';
    }
}
