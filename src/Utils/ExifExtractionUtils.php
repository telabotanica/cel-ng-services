<?php

namespace App\Utils;

/**
 * Helper class to extract exif information from images.
 */
class ExifExtractionUtils {

    private $exif;
    private $exifIfdzero;

    function __construct($imgPath) {
        $this->exif = @exif_read_data($imgPath);
        $this->exifIfdzero = @exif_read_data($imgPath, 'IFD0');
    }   

   private function toDecimal($deg, $min, $sec, $ref) {
   
      $float = function($v) {
          return (count($v = explode('/', $v)) > 1) ? $v[0] / $v[1] : $v[0];
      };

      $d = $float($deg) + (($float($min) / 60) + ($float($sec) / 3600));
      return ($ref == 'S' || $ref == 'W') ? $d *= -1 : $d;
   }

   public function getLatitude() {

      return (isset($this->exif['GPSLatitude'])) ? 
         floatval(sprintf('%.6f', $this->toDecimal($this->exif['GPSLatitude'][0], $this->exif['GPSLatitude'][1], $this->exif['GPSLatitude'][2], $this->exif['GPSLatitudeRef']))) : null;
   }

   public function getLongitude() {

      return (isset($this->exif['GPSLatitude'])) ? 
         floatval(sprintf('%.6f', $this->toDecimal($this->exif['GPSLongitude'][0], $this->exif['GPSLongitude'][1], $this->exif['GPSLongitude'][2], $this->exif['GPSLongitudeRef']))) : null;
   }

   public function getCoordinateArray()  {

      return (isset($exif['GPSLatitude'], $exif['GPSLongitude'])) ? implode(',', array(
          'latitude' => floatval(sprintf('%.6f', $this->toDecimal($exif['GPSLatitude'][0], $exif['GPSLatitude'][1], $exif['GPSLatitude'][2], $exif['GPSLatitudeRef']))),
          'longitude' => floatval(sprintf('%.6f', $this->toDecimal($exif['GPSLongitude'][0], $exif['GPSLongitude'][1], $exif['GPSLongitude'][2], $exif['GPSLongitudeRef'])))
      )) : null;

   }

   public function getShootingDate() {
      return new \DateTime($this->exifIfdzero['DateTime']);
   }


}