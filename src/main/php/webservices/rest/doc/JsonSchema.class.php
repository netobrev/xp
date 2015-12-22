<?php namespace webservices\rest\doc;

use lang\FormatException;
use lang\IllegalStateException;
use util\Objects;

/**
 * See http://json-schema.org/latest/json-schema-core.html
 */
class JsonSchema implements \lang\Value {
  private $definition;

  /**
   * Creates a new schema instance
   *
   * @param  [:var] $defintion Definitions as parsed from JSON schema file
   */
  public function __construct($definition) {
    $this->definition= $definition;
  }

  private function fail($context, $value, $message) {
    throw new FormatException(Objects::stringOf($value).' '.$message.' at '.$context);
  }

  private function test($pointer, $value, $context) {
    if (isset($pointer['enum'])) {
      if (!in_array($value, $pointer['enum'])) {
        $this->fail($context, $value, 'must be one of ['.implode(', ', $pointer['enum']).']');
      }
    } else if ('object' === $pointer['type']) {
      foreach ($pointer['properties'] as $name => $definition) {
        if (isset($value[$name])) {
          $this->test($definition, $value[$name], $context.' > Property '.$name);
        } else {
          $this->fail($context, $value, 'has no property named `'.$name.'`');
        }
      }
    } else if ('array' === $pointer['type']) {
      foreach ($value as $i => $item) {
        $this->test($pointer['items'], $item, $context.' > Item #'.($i + 1));
      }
    } else if ('string' === $pointer['type']) {
      if (!is_string($value)) {
        $this->fail($context, $value, 'must be an string');
      }
    } else if ('integer' === $pointer['type']) {
      if (!is_int($value)) {
        $this->fail($context, $value, 'must be an integer');
      }
    } else if ('number' === $pointer['type']) {
      if (!is_numeric($value)) {
        $this->fail($context, $value, 'must be a number');
      }
    } else if ('null' === $pointer['type']) {
      if (null !== $value) {
        $this->fail($context, $value, 'must be null');
      }
    } else {
      throw new IllegalStateException('Cannot validate unknown schema type `'.$pointer['type'].'`');
    }
  }

  /**
   * Validate a value
   *
   * @param  var $value
   * @return void
   * @throws lang.FormatException
   */
  public function validate($value) {
    $this->test($this->definition, $value, isset($this->definition['title']) ? $this->definition['title'] : 'Root');
  }

  /** @return string */
  public function hashCode() { return Objects::hashOf($this->definition); }

  /** @return string */
  public function toString() { return nameof($this).'@'.Objects::stringOf($this->definition); }

  /**
   * Compare to another schema
   *
   * @param  var $value
   * @return int
   */
  public function compareTo($value) {
    return $value instanceof self ? Objects::compare($this->definition, $value->definition) : 1;
  }
}