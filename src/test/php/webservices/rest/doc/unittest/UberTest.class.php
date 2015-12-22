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

  /**
   * Returns schema from the uber.json files
   *
   * @param  string $named
   * @return php.Iterator
   */
  private function schema($named) {
    foreach ($this->input as $file) {
      $input= Json::read(new StreamInput($file->in()));
      foreach ($input['resources'] as $resource) {
        if (isset($resource[$named])) {
          $schema= new JsonSchema(Json::read($resource[$named]['schema']));
          foreach ($resource['examples'] as $example) {
            yield [$schema, $example];
          }
        }
      }
    }
  }

  private function statuscodes() {
    foreach ($this->input as $file) {
      $input= Json::read(new StreamInput($file->in()));
      foreach ($input['resources'] as $resource) {
        $statuscodes= $resource['status_codes']['table'];
        foreach ($resource['examples'] as $example) {
          yield [$statuscodes, $example];
        }
      }
    }
  }

  #[@test, @values(source= 'schema', args= ['body'])]
  public function verify_request($schema, $example) {
    $schema->validate(Json::read($example['request']['payload']));
  }

  #[@test, @values(source= 'schema', args= ['returns'])]
  public function verify_successful_response($schema, $example) {
    if ($example['response']['code'] < 300) {
      $schema->validate(Json::read($example['response']['payload']));
    }
  }

  #[@test, @values(source= 'statuscodes')]
  public function verify_all_examples_statuscodes_are_documented($statuscodes, $example) {
    foreach ($statuscodes as $status) {
      if ($status['code'] === $example['response']['code']) return;
    }
    $this->fail('No example for statuscode '.$example['response']['code'], null, 'Documented');
  }
}