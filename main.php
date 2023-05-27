<?php





$myVillages = explode(",", "508|469,506|473,510|469,526|468,526|466,520|464,519|463,524|465,526|464,528|472,527|474,528|475,506|469,530|471,510|459");
$myDestination = explode(",", "502|442,500|441,497|439,503|443,537|446,537|445,536|446,538|446,539|446,539|445,535|443,536|444,535|441,535|440,531|531,532|532");
$min_arrivalTime = "2023/05/29 13:00:00";
$max_arrivalTime = "2023/05/29 15:00:00";





















class UnitTravel {
    public $name;
    public $timeInSec;
    public $timeParsed;
    public function __construct($name, $time) {
        $this->name = $name;
        $this->timeInSec = $time;
        $this->timeParsed = gmdate("i:s", $time); // Format MM:SS
    }

    public static function getFormattedTime($time) {
        $hours = floor(intval($time) / 3600);
        $minutes = floor((intval($time) % 3600) / 60);
        $seconds = intval($time) % 60;

        return sprintf('%02dH %02dmin %02dsec', $hours, $minutes, $seconds);
    }

}

class TravelManager {

    public $travels = [];
    public $shortestID = -1;
    public $shortestTimeID = -1;
    public $shortestFullTimeID = -1;
    public $longestID = -1;
    public $longestTimeID = -1;
    public $longestFullTimeID = -1;
    public $medianID = -1;
    public $medianTimeID = -1;
    public $medianFullTimeID = -1;
    public function getAllTravelTimes($sourceVillages, $destinationVillages, $min_arrivalTime, $max_arrivalTime, $unit_travel) {

        foreach ($sourceVillages as $source) {
            $tmp_travels = [];
            foreach ($destinationVillages as $destination) {
                $distance = Village::calculateDistance($source, $destination);
                $time = $distance * $unit_travel->timeInSec;
                $first_throw_at = strtotime($min_arrivalTime) - $time;
                $last_throw_at = strtotime($max_arrivalTime) - $time;

                // Créer une instance de Travel et l'ajouter au tableau des trajets
                $travel = new Travel($source, $destination, $distance, $time, gmdate("Y/m/d H:i:s", intval($first_throw_at)), gmdate("Y/m/d H:i:s", intval($last_throw_at)));
                $tmp_travels[] = $travel;
            }
            array_push($this->travels, $tmp_travels);
        }

    }

    public function median($values) {
        sort($values);
        $count = count($values);
        $middle = floor(($count - 1) / 2);
        if ($count % 2) {
            return $values[$middle];
        } else {
            return ($values[$middle] + $values[$middle + 1]) / 2;
        }
    }

    public function foundIdOfRowWhereTheTravelTimeMedianIsShortest() {
        $shortest = 99999999999999999999999;
        $shortestID = -1;
        foreach ($this->travels as $key => $travels) {
            $time = 0;
            foreach ($travels as $travel) {
                $time += $travel->travelTime;
            }
            if ($shortestID == -1) {
                $shortestID = $key;
                $shortest = $time;
            }
            if ($time < $shortest) {
                $shortestID = $key;
                $shortest = $time;
            }
        }
        $this->shortestID = $shortestID;
        $this->shortestTimeID = $shortest;
        $this->shortestFullTimeID = $shortest;
    }

    public function foundIdOfRowWhereTheTravelTimeMedianIsLongest() {
        $longest = -1;
        $longestID = -1;
        foreach ($this->travels as $key => $travels) {
            $time = 0;
            foreach ($travels as $travel) {
                $time += $travel->travelTime;
            }
            if ($longestID == -1) {
                $longestID = $key;
                $longest = $time;
            }
            if ($time > $longest) {
                $longestID = $key;
                $longest = $time;
            }
        }
        $this->longestID = $longestID;
        $this->longestTimeID = $longest;
        $this->longestFullTimeID = $longest;
    }

    public function foundIdOfRowWhereTheTravelTimeMedianIsAverage() {
        $medians = [];
        foreach ($this->travels as $key => $travels) {
            $time = 0;
            foreach ($travels as $travel) {
                $time += $travel->travelTime;
            }
            $medians[$key] = $time;
        }
        $med = array_sum($medians) / count($medians);
        $nearestId = -1;
        $nearestTime = -1;
        $nearestTimeV = -1;
        foreach($medians as $key => $time) {
            $calc = $med - $time;
            if ($calc < -1)
                $calc = $calc * -1;

            if ($nearestId < 0) {
                $nearestTime = $calc;
                $nearestId = $key;
                $nearestTimeV = $time;
            } else {
                if ($calc < $nearestTime) {
                    $nearestTime = $calc;
                    $nearestId = $key;
                    $nearestTimeV = $time;
                }
            }

            
        }
        $this->medianID = $nearestId;
        $this->medianTimeID = $nearestTimeV;
        $this->medianFullTimeID = $nearestTimeV;
    }

    public function displayShortest() {
        echo "\n\n ===== Shortest Way (".$this->shortestID." => ".UnitTravel::getFormattedTime($this->shortestTimeID)." => ".UnitTravel::getFormattedTime($this->shortestTimeID / count($this->travels[0])).") ======\n\n";
        foreach ($this->travels[$this->shortestID] as $travel)
            $travel->display();
        echo "\n\n ===== Shortest Way (".$this->shortestID." => ".UnitTravel::getFormattedTime($this->shortestTimeID)." => ".UnitTravel::getFormattedTime($this->shortestTimeID / count($this->travels[0])).") ======\n\n";
    }
    public function displayLongest() {
        echo "\n\n ===== Longest Way (".$this->longestID." => ".UnitTravel::getFormattedTime($this->longestTimeID)." => ".UnitTravel::getFormattedTime($this->longestTimeID / count($this->travels[0])).") ======\n\n";
        foreach ($this->travels[$this->longestID] as $travel)
            $travel->display();
        echo "\n\n ===== Longest Way (".$this->longestID." => ".UnitTravel::getFormattedTime($this->longestTimeID)." => ".UnitTravel::getFormattedTime($this->longestTimeID / count($this->travels[0])).") ======\n\n";
    }
    public function displayMedian() {
        echo "\n\n ===== Median Way (".$this->medianID." => ".UnitTravel::getFormattedTime($this->medianTimeID)." => ".UnitTravel::getFormattedTime($this->medianTimeID / count($this->travels[0])).") ======\n\n";
        foreach ($this->travels[$this->medianID] as $travel)
            $travel->display();
        echo "\n\n ===== Median Way (".$this->medianID." => ".UnitTravel::getFormattedTime($this->medianTimeID)." => ".UnitTravel::getFormattedTime($this->medianTimeID / count($this->travels[0])).") ======\n\n";
    }
    public function displayAll() {
        $this->displayMedian();
        $this->displayLongest();
        $this->displayShortest();
    }
}

class Travel {
    public $source;
    public $destination;
    public $distance;
    public $travelTime;
    public $firstThrowAt;
    public $lastThrowAt;

    public function __construct($source, $destination, $distance, $travelTime, $firstThrowAt, $lastThrowAt) {
        $this->source = $source;
        $this->destination = $destination;
        $this->distance = $distance;
        $this->travelTime = $travelTime;
        $this->firstThrowAt = $firstThrowAt;
        $this->lastThrowAt = $lastThrowAt;
    }

    public function display() {
        echo "Source: (X: " . $this->source->posX . ", Y: " . $this->source->posY . ")\n";
        echo "Destination: (X: " . $this->destination->posX . ", Y: " . $this->destination->posY . ")\n";
        echo "Distance: " . $this->distance . "\n";
        echo "Travel Time: " . UnitTravel::getFormattedTime($this->travelTime) . "\n";
        echo "First Throw At: " . $this->firstThrowAt . "\n";
        echo "Last Throw At: " . $this->lastThrowAt . "\n";
        echo "-------------------------\n";
    }
}


class Village {
    public $posX;
    public $posY;
    public $distance;
    public $target_village;

    public function __construct($posX, $posY) {
        $this->posX = $posX;
        $this->posY = $posY;
        $this->distance = array();
        $this->target_village = null;
    }

    public static function calculateDistance($village1, $village2) {
        return sqrt(pow($village2->posX - $village1->posX, 2) + pow($village2->posY - $village1->posY, 2));
    }
}

$travelManager = new TravelManager();


$belier = new UnitTravel("belier", 20*60);

// Tableaux pour stocker les instances de la classe Village
$sourceVillages = array();
$destinationVillages = array();

// Création des instances de la classe Village pour les villages sources
$sourceCoords = $myVillages;
foreach ($sourceCoords as $source) {
    list($posX, $posY) = explode("|", $source);
    $sourceVillages[] = new Village($posX, $posY);
}


// Création des instances de la classe Village pour les villages cibles
$destinationCoords = $myDestination;
foreach ($destinationCoords as $destination) {
    list($posX, $posY) = explode("|", $destination);
    $destinationVillages[] = new Village($posX, $posY);
}


$travelManager->getAllTravelTimes($sourceVillages, $destinationVillages, $min_arrivalTime, $max_arrivalTime, $belier);

$travelManager->foundIdOfRowWhereTheTravelTimeMedianIsShortest();
$travelManager->foundIdOfRowWhereTheTravelTimeMedianIsLongest();
$travelManager->foundIdOfRowWhereTheTravelTimeMedianIsAverage();

$travelManager->displayAll();

