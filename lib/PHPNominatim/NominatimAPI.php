<?php

namespace PHPNominatim;
require "NominatimException.php";

/**
 * NominatimAPI is a thin wrapper of Nominatim api,
 * @see https://wiki.openstreetmap.org/wiki/Nominatim
 *
 * User: asafdav
 * Date: 7/18/12
 * Time: 10:27 AM
 */
class NominatimAPI
{
  /** @var array configuration container*/
  protected $config = array(
    'api_uri' => 'http://nominatim.openstreetmap.org/',
    'search_endpoint' => 'search',
    'reverse_endpoint' => 'reverse',
    'response_formats' => array('html', 'json', 'xml'),
    'curl_default_config' => array (
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_FOLLOWLOCATION => 0,
      CURLOPT_TIMEOUT => '10',
      CURLOPT_FAILONERROR => false,
      CURLOPT_HEADER => false,
      CURLOPT_USERAGENT => 'php-nominatim-client'
    )
  );

  /**
   * Creates a new instance of NominatimAPI
   *
   * @param array $config
   */
  function __construct($config = array())
  {
    // Allow override of the default configuration
    $this->config = array_merge($this->config, $config);
  }

  /**
   * Returns the value of the wanted variable
   * @param $name The name of the wanted variable
   * @param $default Default value the would be returned in case the wanted variable doesn't exists
   * @return The value or the default
   */
  public function getVariable($name, $default = null) {
    $returnValue = $default;
    if (isset($this->config[$name])) {
      $returnValue = $this->config[$name];
    }

    return $returnValue;
  }

  /**
   * Sends a search query to Nominatim API server
   *
   * @param string $q The wanted address to search, Please note that search terms are processed left to right.
   * @param string $format The wanted response format
   * @param array $optionalParams A key-value array of optional parameters to send
   * @return mixed|object|string
   *
   * @see https://wiki.openstreetmap.org/wiki/Nominatim#Parameters
   */
  public function search($q, $format = "json", $optionalParams = array()) {
    // Build the request
    $params = array('q' => $q, 'format' => $format);
    $params = array_merge($params, $optionalParams);

    // Execute it
    $queryUri = $this->getVariable('api_uri') . $this->getVariable('search_endpoint');
    $response = $this->get($queryUri, $params);

    return $this->parseResponse($response, $format);
  }


  /**
   * Sends a reverse query to Nominatim API server,
   * This service generates address from lat and long.
   *
   * @param string $lat The wanted lat
   * @param string $lon The wanted lon
   * @param string $format The wanted response format
   * @param array $optionalParams A key-value array of optional parameters to send
   * @return mixed|object|string
   *
   * @see https://wiki.openstreetmap.org/wiki/Nominatim#Parameters_2
   */
  public function reverse($lat, $lon, $format = "json", $optionalParams = array()) {
    // Build the request
    $params = array('lat' => $lat, 'lon' => $lon, 'format' => $format);
    $params = array_merge($params, $optionalParams);

    // Execute it
    $queryUri = $this->getVariable('api_uri') . $this->getVariable('reverse_endpoint');
    $response = $this->get($queryUri, $params);

    return $this->parseResponse($response, $format);
  }

  /**
   * Parses a response based on its format
   *
   * @param $response
   * @param $format
   * @return mixed|object|string
   */
  protected function parseResponse($response, $format) {
    $returnValue = "";
    switch ($format) {
      case "json":
        $returnValue = json_decode($response);
        break;
      case "xml":
        $returnValue = @simplexml_load_string($response);
        break;
      default:
        $returnValue = $response;
    }

    if (!$returnValue) {
      $returnValue = null;
    }

    return $returnValue;
  }

  /**
   * sends a POST request to Nominatim api
   * @param $uri
   * @param $parameters
   * @return string The response
   */
  protected function post($uri, $parameters) {
    $ch = curl_init($uri);
    curl_setopt_array($ch, $this->getVariable('curl_default_config'));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
  }

  /**
   * sends a GET request to Nominatim api
   * @param $uri
   * @param $parameters
   * @return string The response
   */
  protected function get($uri, $parameters = array()) {
    $ch = curl_init();

    $queryString = http_build_query($parameters);
    curl_setopt_array($ch, $this->getVariable('curl_default_config'));
    curl_setopt($ch, CURLOPT_URL, $uri ."?$queryString");
    $response = curl_exec($ch);
    curl_close($ch);

    // No response from the server, throw exception
    if ($response === false) {
      throw new NominatimException();
    }
    return $response;
  }

}
