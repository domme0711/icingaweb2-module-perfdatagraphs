<?php

namespace Tests\Icinga\Module\Perfdatagraphs;

use Icinga\Module\Perfdatagraphs\Common\CustomVarsHelper;

use PHPUnit\Framework\TestCase;

final class CustomVarsHelperTest extends TestCase
{
    public function test_mergeCustomVars_without_customvars()
    {
        $perfdata = [
            'data' => [
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
            ]
        ];

        $expected = [
            'data' => [
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
            ]
        ];


        $cvh = new CustomVarsHelper();
        $actual = $cvh->mergeCustomVars($perfdata, []);

        $this->assertEquals($expected, $actual);
    }

    public function test_mergeCustomVars_with_customvars()
    {
        $perfdata = [
            'data' => [
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
            ]
        ];

        $customvars = [
            'unload' => [
                'unit' => 'load',
                'fill' => 'rgba(1, 1, 1, 1)',
                'stroke' => 'rgba(2, 2, 2, 2)',
            ]
        ];

        $expected = [
            'data' => [
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
            ]
        ];

        $cvh = new CustomVarsHelper();
        $actual = $cvh->mergeCustomVars($perfdata, $customvars);

        $this->assertEquals($expected, $actual);
    }
}
