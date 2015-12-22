<?php namespace webservices\rest\doc\unittest;

use text\json\Json;
use text\json\StreamInput;
use webservices\rest\doc\JsonSchema;
use io\collections\FileCollection;
use io\collections\FileElement;
use io\collections\iterate\FilteredIOCollectionIterator;
use io\collections\iterate\NameEqualsFilter;

class UberTest extends \unittest\TestCase {
  private $input;

  /**
   * Creates a new test
   *
   * @param  string $name
   * @param  string $input A path or a file
   */
  public function __construct($name, $input) {
    parent::__construct($name);
    if (is_file($input)) {
      $this->input= [new FileElement($input)];
    } else {
      $this->input= new FilteredIOCollectionIterator(new FileCollection($input), new NameEqualsFilter('uber.json'), true);
    }
  }

  /** @return php.Iterator */
  private function input() {
    foreach ($this->input as $file) {
      $input= Json::read(new StreamInput($file->in()));
      foreach ($input['resources'] as $resource) {
        $body= isset($resource['body']) ? new JsonSchema(Json::read($resource['body']['schema'])) : null;
        $returns= isset($resource['returns']) ? new JsonSchema(Json::read($resource['returns']['schema'])) : null;
        foreach ($resource['examples'] as $example) {
          yield [$example, $body, $returns];
        }
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

  #[@test, @values(source= 'input')]
  public function verify_request($example, $body, $returns) {
    $this->assertValidates($body, $example['request']);
  }

  #[@test, @values(source= 'input')]
  public function verify_successful_response($example, $body, $returns) {
    if ($example['response']['code'] < 300) {
      $this->assertValidates($returns, $example['response']);
    }
  }
}