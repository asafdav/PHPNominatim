PHPNominatim
============

##  DESCRIPTION
PHPNominatim is a thin wrapper of openstreetmap Nominatim API.

## Usage
* Search request

        $api = new NominatimAPI();
        $empireState = $api->search("Empire state building");

* Optional parameters

        $api = new NominatimAPI();
        $empireState = $api->search("Empire state building", "json", array("addressdetails" => true, "polygon" => true));

* Reverse search

        $api = new NominatimAPI();
        $empireState = $api->reverse('40.7484213157135', '-73.9856735331156');
