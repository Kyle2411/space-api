<?php
    namespace Vanier\Api\Helpers;

use Exception;

    class Calculator
    {
            private float $weight = 0;

        
            private $conversion_units = array(
                // meter - base unit for distance
                "lb" => array("conversion" => 1),
                // kilometer: 1km = 1000 meters
                "g" => array("conversion" => 0.001),
                // feet: 1ft = 0.3048 meters
                "kg" => array("conversion" => 0.453592),
                // yard: 1yd = 0.9144 meters
                "stones" => array("conversion" => 0.0714286),
            );

           

            
            public function calculate( $allPlanets,  $planetName,  $weight, $gravity): mixed {
                $earthGravity = 9.8;
                // Loop through the array of planets to find the specified planet's gravity
                foreach ($allPlanets as $planet) {
                    if (strtolower($planet) == strtolower($planetName)) {
                        $newGrav = $gravity / $earthGravity;
                        $calculatedWeight = $weight * $newGrav;
                        $this->weight = $calculatedWeight;
                        return $this;
                    } 
                }
                return $this;
            }
            //throw new Exception("The requested planet $planetName wasn't possible: planet is either invalid or unsupported.");



            public function to(string $unit, ? int $decimals = null, bool $round = true)
            {
                $result = 0;
                if ($this->weight == 0) {
                    return 0;
                }
                if (!$this->unitExists($unit)) {
                    //
                }
                            
                $conversion = $this->getConversion(strtolower($unit));
                if (is_numeric($conversion)) {
                    $result =  $this->weight / $conversion;
                    if ($this->isPrecisionValid($decimals)) {
                        $result =  $this->round($result, $decimals, $round);
                    }
                }
                return $result;
            }


            public function toMany(array $units = [], ?int $decimals = null, $round = true)
            {
                $results = array();
                foreach ($units as $unit) {
                    $results[$unit] = $this->to($unit, $decimals, $round);
                }
                return $results;
            }


            public function toAll(?int $decimals = null, bool $round = true): array
            {
                if ($this->weight == 0) {
                    //  No conversion to be preformed: the distance hasn't been computed yet.            
                    return [];
                }
                // Apply the conversion to all defined length units.
                return $this->toMany(array_keys($this->conversion_units), $decimals, $round);
            }


            private function isPrecisionValid(?int $decimals): bool
            {
                return !is_null($decimals) && $decimals <= 9 && $decimals > 0;
            }
           

            private function getConversion(string $unit): float
            {
                return $this->conversion_units[$unit]['conversion'];
            }
            

            private function unitExists(string $unit): bool
            {
                return array_key_exists($unit, $this->conversion_units);
            }

            
            private function round(float $value, int $decimals, bool $round): float
            {
                $mode = $round ? PHP_ROUND_HALF_UP : PHP_ROUND_HALF_DOWN;
                return round($value, $decimals, $mode);
            }
           

        

            // public function is_planet(mixed $planetName)
            // {
                

            //     foreach ($planets as $planet){
            //         if ($planet == strtolower($planetName)) {
            //             return true;
            //         }
            //     }
            //     return !is_array($planetName) && preg_match($planets, $planetName);
            // }
           

            public function is_weight(mixed $weight)
            {
                return !is_array($weight) && preg_match('/^\d+$/', $weight);
            }


        }
