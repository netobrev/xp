<?php namespace webservices\rest\doc\unittest;

use text\json\Json;
use text\json\FileInput;
use webservices\rest\doc\JsonSchema;

class UberTest extends \unittest\TestCase {
  private $input;

  /**
   * Creates a new test
   *
   * @param  string $name
   * @param  string $input An uber.json input file
   */
  public function __construct($name, $input) {
    parent::__construct($name);
    $this->input= $input;
  }

  /** @return php.Iterator */
  private function file() {
    $input= Json::read(new FileInput($this->input));
    foreach ($input['resources'] as $resource) {
      $body= isset($resource['body']) ? new JsonSchema(Json::read($resource['body']['schema'])) : null;
      $returns= isset($resource['returns']) ? new JsonSchema(Json::read($resource['returns']['schema'])) : null;
      foreach ($resource['examples'] as $example) {
        yield [$example, $body, $returns];
      }
    }
  }

  /**
   * Assertion helper: Validate JSON schema against payload
   *
   * @param  webservices.rest.doc.JsonSchema $schema
   * @param  [:var] $exchange
   * @throws unittest.AssertionFailedError
   */
  private function assertValidates($schema, $exchange) {
    if (isset($exchange['payload'])) {
      $schema->validate(Json::read($exchange['payload']));
    }
  }

  #[@test, @values(source= 'file')]
  public function verify_request($example, $body, $returns) {
    $this->assertValidates($body, $example['request']);
  }

  #[@test, @values(source= 'file')]
  public function verify_successful_response($example, $body, $returns) {
    if ($example['response']['code'] < 300) {
      $this->assertValidates($returns, $example['response']);
    }
  }
}