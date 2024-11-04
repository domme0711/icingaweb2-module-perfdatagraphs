<?php

namespace Tests\Icinga\Module\Perfdatagraphs;

use Icinga\Module\Perfdatagraphs\Common\PerfdataSource;

use PHPUnit\Framework\TestCase;

final class PerfdataSourceTest extends TestCase
{
    use PerfdataSource;

    public function test_fetchDataViaHook_with_no_hook()
    {
        $data = $this->fetchDataViaHook('host', 'service', 'checkcommand', 'P1Y', []);

        $this->assertEquals([], $data);
    }

    public function test_mergeCustomVars_without_customvars()
    {
        $perfdata = [
            [
                'title' => 'load',
                'timestamps' => [1731407439, 1731407618, 1731407797],
                'unit' => 'cpu',
                'series' =>
                    [
                        [
                            'name' => 'load1',
                            'data' => [1,2,3],
                        ],
                        [
                            'name' => 'load5',
                            'data' => [1,2,3],
                        ],
                        [
                            'name' => 'warn',
                            'data' => [6,6,6],
                        ],
                    ]
            ],
            [
                'title' => 'unload',
                'timestamps' => [1731407439, 1731407618, 1731407797],
                'series' =>
                    [
                        [
                            'name' => 'unload1',
                            'data' => [3,2,1],
                        ],
                        [
                            'name' => 'unload5',
                            'data' => [3,2,1],
                        ],
                        [
                            'name' => 'crit',
                            'data' => [9,9,9],
                        ],
                    ]
            ]
        ];

        $expected = [
            [
                'title' => 'load',
                'timestamps' => [1731407439, 1731407618, 1731407797],
                'unit' => 'cpu',
                'series' =>
                    [
                        [
                            'name' => 'load1',
                            'data' => [1,2,3],
                        ],
                        [
                            'name' => 'load5',
                            'data' => [1,2,3],
                        ],
                        [
                            'name' => 'warn',
                            'data' => [6,6,6],
                        ],
                    ]
            ],
            [
                'title' => 'unload',
                'timestamps' => [1731407439, 1731407618, 1731407797],
                'series' =>
                    [
                        [
                            'name' => 'unload1',
                            'data' => [3,2,1],
                        ],
                        [
                            'name' => 'unload5',
                            'data' => [3,2,1],
                        ],
                        [
                            'name' => 'crit',
                            'data' => [9,9,9],
                        ],
                    ]
            ]
        ];


        $actual = $this->mergeCustomVars($perfdata, []);

        $this->assertEquals($expected, $actual);
    }

    public function test_mergeCustomVars_with_customvars()
    {
        $perfdata = [
            [
                'title' => 'load',
                'timestamps' => [1731407439, 1731407618, 1731407797],
                'unit' => 'cpu',
                'series' =>
                    [
                        [
                            'name' => 'load1',
                            'data' => [1,2,3],
                        ],
                    ]
            ],
            [
                'title' => 'unload',
                'timestamps' => [1731407439, 1731407618, 1731407797],
                'series' =>
                    [
                        [
                            'name' => 'unload1',
                            'data' => [3,2,1],
                        ],
                        [
                            'name' => 'unload5',
                            'data' => [3,2,1],
                        ],
                        [
                            'name' => 'crit',
                            'data' => [9,9,9],
                        ],
                    ],
                'stroke' => 'rgb(1,2,3)',
            ]
        ];

        $customvars = [
            'graphs' => [
                'unload' => [
                    'unit' => 'load',
                    'fill' => 'rgba(1, 1, 1, 1)',
                    'stroke' => 'rgba(2, 2, 2, 2)',
                ]
            ]
        ];

        $expected = [
            [
                'title' => 'load',
                'timestamps' => [1731407439, 1731407618, 1731407797],
                'unit' => 'cpu',
                'series' =>
                    [
                        [
                            'name' => 'load1',
                            'data' => [1,2,3],
                        ],
                    ],
            ],
            [
                'title' => 'unload',
                'unit' => 'load',
                'timestamps' => [1731407439, 1731407618, 1731407797],
                'series' =>
                    [
                        [
                            'name' => 'unload1',
                            'data' => [3,2,1],
                        ],
                        [
                            'name' => 'unload5',
                            'data' => [3,2,1],
                        ],
                        [
                            'name' => 'crit',
                            'data' => [9,9,9],
                        ],
                    ],
                'stroke' => 'rgba(2, 2, 2, 2)',
                'fill' => 'rgba(1, 1, 1, 1)',
            ]
        ];

        $actual = $this->mergeCustomVars($perfdata, $customvars);

        $this->assertEquals($expected, $actual);
    }
}
