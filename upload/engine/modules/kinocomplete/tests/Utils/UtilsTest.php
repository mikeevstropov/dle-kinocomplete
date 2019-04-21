<?php

namespace Kinocomplete\Test\User;

use Kinocomplete\Utils\Utils;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;

class UtilsTest extends TestCase
{
  /**
   * Testing "convertToBytes" method.
   */
  public function testCanConvertToBytes()
  {
    $testCases = [
      "13",
      "13B",
      "13KB",
      "10.5KB",
      "123Mi"
    ];

    $expected = [
      13,
      13,
      13312,
      10752,
      null
    ];

    $result = array_map(
      [Utils::class, 'convertToBytes'],
      $testCases
    );

    Assert::eq(
      $result,
      $expected
    );
  }

  public function testCanBytesToHuman()
  {
    $testCases = [
      13,
      13312,
      10752,
      0
    ];

    $expected = [
      "13 bytes",
      "13.00 KB",
      "10.50 KB",
      "0 bytes"
    ];

    $result = array_map(
      [Utils::class, 'bytesToHuman'],
      $testCases
    );

    Assert::eq(
      $result,
      $expected
    );
  }
}
