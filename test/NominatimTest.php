<?php
use PHPNominatim\NominatimAPI;

/**
 * Created by JetBrains PhpStorm.
 * User: asafdav
 * Date: 7/18/12
 * Time: 11:16 AM
 * To change this template use File | Settings | File Templates.
 */
class NominatimTest extends PHPUnit_Framework_TestCase
{
  /** @var NominatimAPI */
  protected $nominatimApi;

  protected function setUp()
  {
    $this->nominatimApi = new NominatimAPI();
  }

  public function testGetVariable() {
    $this->assertEquals($this->nominatimApi->getVariable('api_uri'), 'http://nominatim.openstreetmap.org/', '->getVariable returns the value of the wanted variable');
    $this->assertEquals($this->nominatimApi->getVariable('api_uri', 'default'), 'http://nominatim.openstreetmap.org/', '->getVariable ignores default if the wanted key exists');
    $this->assertEquals($this->nominatimApi->getVariable('no_var', 'default'), 'default', '->getVariable returns default');
  }

  public function testSearch() {
    // One search result
    $result = $this->nominatimApi->search("Empire state building");
    $this->assertEquals(1, count($result), '->search() returns one result');
    $this->assertEquals('40.7484213157135', $result[0]->lat, '->search() returns the empire state building lat');
    $this->assertEquals('-73.9856735331156', $result[0]->lon, '->search() returns the empire state building lon');
    $this->assertFalse(isset($result[0]->address), '->search() returns only the basic details');

    // Multiple results
    $result = $this->nominatimApi->search("New york");
    $this->assertGreaterThan(1, count($result), '->search() returns multiple results');

    // No result
    $result = $this->nominatimApi->search("New york New york New york New york");
    $this->assertNull($result, '->search() returns null if the query has returned no result');

    // Optional parameters
    $result = $this->nominatimApi->search("Empire state building", 'json', array('addressdetails' => true));
    $this->assertEquals(1, count($result), '->search() returns one result');
    $this->assertEquals('40.7484213157135', $result[0]->lat, '->search() returns the empire state building lat');
    $this->assertEquals('-73.9856735331156', $result[0]->lon, '->search() returns the empire state building lon');
    $this->assertTrue(isset($result[0]->address), '->search() returns address details');
    $this->assertEquals('Empire State Building', $result[0]->address->public_building);
  }

  public function testReverse() {
    $result = $this->nominatimApi->reverse('40.7484213157135', '-73.9856735331156');
    $this->assertEquals(1, count($result), '->search() returns one result');
    $this->assertTrue(isset($result->address), '->search() returns address details');
    $this->assertEquals('Empire State Building', $result->address->public_building);
  }


  /**
   * @expectedException PHPNominatim\NominatimException
   */
  public function testUnavailableServer() {
    $api = new NominatimAPI(array('api_uri' => 'http://www.imunavailablenominatimserver.com'));
    $obj = $api->search('Test');
  }
}
