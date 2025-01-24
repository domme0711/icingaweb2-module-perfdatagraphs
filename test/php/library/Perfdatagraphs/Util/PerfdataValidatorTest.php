<?php

namespace Tests\Icinga\Module\Perfdatagraphs;

use Icinga\Module\Perfdatagraphs\Util\PerfdataValidator;

use PHPUnit\Framework\TestCase;

use SplFixedArray;

final class PerfdataValidatorTest extends TestCase
{
    public function test_validateData_withvalid()
    {
        $ts = new SplFixedArray(1);
        $ts[0] = 0;

        $testCases = [
            [
                'input' => [
                    'data' => [
                        [
                            'title' => 'title',
                            'timestamps' => $ts,
                            'series' => [
                                [
                                    'name' => 'ok',
                                    'data' => [1,2]
                                ]
                            ]
                        ]
                    ]
                ],
                'expected' => null,
            ],
            [
                'input' => [
                    'data' => [
                        [
                            'title' => 'title',
                            'timestamps' => [1,2],
                            'series' => [
                                [
                                    'name' => 'ok',
                                    'data' => [1,2]
                                ]
                            ]
                        ]
                    ]
                ],
                'expected' => null,
            ],
        ];

        foreach ($testCases as $testCase) {
            $actual = PerfdataValidator::validate($testCase['input']);
            $this->assertEquals($testCase['expected'], $actual);
        }
    }

    public function test_validateData_withinvalid()
    {
        $ts = new SplFixedArray(1);
        $ts[0] = 0;

        $tsEmpty = new SplFixedArray(0);

        $testCases = [
            [
                'input' => [],
                'expected' => ['message' => 'does not contain data key'],
            ],
            [
                'input' => ['foobar'],
                'expected' => ['message' => 'does not contain data key'],
            ],
            [
                'input' => ['data' => ''],
                'expected' => ['message' => 'does not contain iterable data'],
            ],
            [
                'input' => ['data' => ['what']],
                'expected' => ['message' => 'does not contain iterable dataset'],
            ],
            [
                'input' => ['data' => []],
                'expected' => ['message' => 'does not contain datasets'],
            ],
            [
                'input' => ['data' => [['title' => 'Just Empty', 'timestamps' => $tsEmpty, 'series' => []]]],
                'expected' => ['message' => 'dataset does not contain any timestamps'],
            ],
            [
                'input' => ['data' => [['title' => 'title']]],
                'expected' => ['message' => 'dataset does not contain timestamp key'],
            ],
            [
                'input' => ['data' => [['title' => 'title', 'timestamps' => $tsEmpty]]],
                'expected' => ['message' => 'dataset does not contain any timestamps'],
            ],
            [
                'input' => ['data' => [['title' => 'title', 'timestamps' => '', 'series' => []]]],
                'expected' => ['message' => 'dataset does not contain iterable timestamps'],
            ],
            [
                'input' => ['data' => [['title' => 'title', 'timestamps' => $ts, 'series' => [['name' => 'sowrong', 'data' => []]]]]],
                'expected' => ['message' => 'data series does not contain any data'],
            ]
        ];

        foreach ($testCases as $testCase) {
            $actual = PerfdataValidator::validate($testCase['input']);
            $this->assertEquals($testCase['expected'], $actual);
        }
    }
}
