<?php declare(strict_types=1);

namespace marc\flattree;

class UnfoldTest extends \PHPUnit_Framework_TestCase
{
    public function testUnfold()
    {
        $data = $this->fixture(
            ['id', 'class', 'animal', 'breed', 'size'],
            [
                [1, 'mammal', 'dog', 'Dalmatian', 'big'],
                [2, 'mammal', 'dog', 'Bulldog', 'small'],
                [3, 'mammal', 'dog', 'Lhasa Apso', 'small'],
                [4, 'mammal', 'cat', 'Persian', 'small'],
            ]
        );

        $tree = unfold($data, ['class', 'animal']);

        $this->assertSame(
            [
              'mammal' => [
                '>' => [
                  'dog' => [
                    '>' => [
                      [
                        'id' => 1,
                        'class' => 'mammal',
                        'animal' => 'dog',
                        'breed' => 'Dalmatian',
                        'size' => 'big',
                      ],
                      [
                        'id' => 2,
                        'class' => 'mammal',
                        'animal' => 'dog',
                        'breed' => 'Bulldog',
                        'size' => 'small',
                      ],
                      [
                        'id' => 3,
                        'class' => 'mammal',
                        'animal' => 'dog',
                        'breed' => 'Lhasa Apso',
                        'size' => 'small',
                      ],
                    ],
                  ],
                  'cat' => [
                    '>' => [
                      [
                        'id' => 4,
                        'class' => 'mammal',
                        'animal' => 'cat',
                        'breed' => 'Persian',
                        'size' => 'small',
                      ],
                    ],
                  ],
                ],
              ],
            ],
            $tree
        );

        $this->assertTreeDump(
            "
            ├─ mammal
            │  ├─ dog
            │  │  └─ Dalmatian
            │  │  └─ Bulldog
            │  │  └─ Lhasa Apso
            │  ├─ cat
            │  │  └─ Persian
            ",
            debug($tree, ['{:level}', '{:level}', '{breed}'])
        );

        // same data as above but with another level

        $tree = unfold($data, ['class', 'animal', 'size']);

        $this->assertSame(
            [
              'mammal' => [
                '>' => [
                  'dog' => [
                    '>' => [
                      'big' => [
                        '>' => [
                          [
                            'id' => 1,
                            'class' => 'mammal',
                            'animal' => 'dog',
                            'breed' => 'Dalmatian',
                            'size' => 'big',
                          ],
                        ],
                      ],
                      'small' => [
                        '>' => [
                          [
                            'id' => 2,
                            'class' => 'mammal',
                            'animal' => 'dog',
                            'breed' => 'Bulldog',
                            'size' => 'small',
                          ],
                          [
                            'id' => 3,
                            'class' => 'mammal',
                            'animal' => 'dog',
                            'breed' => 'Lhasa Apso',
                            'size' => 'small',
                          ],
                        ],
                      ],
                    ],
                  ],
                  'cat' => [
                    '>' => [
                      'small' => [
                        '>' => [
                          [
                            'id' => 4,
                            'class' => 'mammal',
                            'animal' => 'cat',
                            'breed' => 'Persian',
                            'size' => 'small',
                          ],
                        ],
                      ],
                    ],
                  ],
                ],
              ],
            ],
            $tree
        );

        $this->assertTreeDump(
            "
            ├─ mammal
            │  ├─ dog
            │  │  ├─ big
            │  │  │  └─ Dalmatian
            │  │  ├─ small
            │  │  │  └─ Bulldog
            │  │  │  └─ Lhasa Apso
            │  ├─ cat
            │  │  ├─ small
            │  │  │  └─ Persian
            ",
            debug($tree, ['{:level}', '{:level}', '{:level}', '{breed}'])
        );

        // a different data set just for guarantee

        $data = $this->fixture(
            ['id', 'category', 'class', 'type', 'product', 'delivery'],
            [
                [1,'ELECTRONICS', 'TELEVISIONS', 'TUBE', 'PRODUCT #835423', 5],
                [2,'ELECTRONICS', 'TELEVISIONS', 'LCD', 'PRODUCT #378429', 20],
                [3,'ELECTRONICS', 'TELEVISIONS', 'PLASMA', 'PRODUCT #394941', 12],
                [4,'ELECTRONICS', 'PORTABLE ELECTRONICS', 'MP3 PLAYERS', 'PRODUCT #X73672', 18],
                [5,'ELECTRONICS', 'PORTABLE ELECTRONICS', 'CD PLAYERS', 'PRODUCT #947153', 45],
                [6,'ELECTRONICS', 'PORTABLE ELECTRONICS', '2 WAY RADIOS', 'PRODUCT #394728', 52],
            ]
        );

        $tree = unfold($data, ['category', 'class', 'type']);

        $this->assertTreeDump(
            "
            ├─ ELECTRONICS
            │  ├─ TELEVISIONS
            │  │  ├─ TUBE
            │  │  │  └─ PRODUCT #835423 (available in 5 days)
            │  │  ├─ LCD
            │  │  │  └─ PRODUCT #378429 (available in 20 days)
            │  │  ├─ PLASMA
            │  │  │  └─ PRODUCT #394941 (available in 12 days)
            │  ├─ PORTABLE ELECTRONICS
            │  │  ├─ MP3 PLAYERS
            │  │  │  └─ PRODUCT #X73672 (available in 18 days)
            │  │  ├─ CD PLAYERS
            │  │  │  └─ PRODUCT #947153 (available in 45 days)
            │  │  ├─ 2 WAY RADIOS
            │  │  │  └─ PRODUCT #394728 (available in 52 days)
            ",
            debug($tree, ['{:level}', '{:level}', '{:level}', '{product} (available in {delivery} days)'])
        );

    }

    public function testUnfoldRecursive()
    {
        $data = $this->fixture(
            ['name', 'boss', 'salary', 'profession', 'sex'],
            [
                ['Anthony', null, '21,000.00', 'Executive', 'male',],
                ['Billy', 'Anthony', '19,000.00', 'Assistant', 'male',],
                ['Mary', null, '20,000.00', 'Executive', 'female',],
                ['Bernardo', 'Mary', '14,000.00', 'Architect', 'male',],
                ['Chuck', 'Mary', '16,000.00', 'Architect', 'male',],
                ['Chuck', 'Mary', '16,000.00', 'Engineer', 'male',],
                ['Leila', 'Chuck', '11,000.00', 'Engineer', 'female',],
                ['Donna', 'Chuck', '10,000.00', 'Engineer', 'female',],
                ['Eddie', 'Chuck', '11,000.00', 'Engineer', 'male',],
                ['Fred', 'Eddie', '4,000.00', 'Intern', 'male',],
            ]
        );

        $tree = unfold_recursive($data, 'boss', 'name');

        $this->assertTreeDump(
            "
            ├─ <null>: <null>
            │  ├─ Executive: Anthony
            │  │  └─ Assistant: Billy
            │  ├─ Executive: Mary
            │  │  └─ Architect: Bernardo
            │  │  ├─ Engineer: Chuck
            │  │  │  └─ Engineer: Leila
            │  │  │  └─ Engineer: Donna
            │  │  │  ├─ Engineer: Eddie
            │  │  │  │  └─ Intern: Fred
            ",
            debug($tree, '{profession}: {name}')
        );

        $this->assertSame(
            [
              '' => [
                '>' => [
                  'Anthony' => [
                    'name' => 'Anthony',
                    'boss' => NULL,
                    'salary' => '21,000.00',
                    'profession' => 'Executive',
                    'sex' => 'male',
                    '>' => [
                      'Billy' => [
                        'name' => 'Billy',
                        'boss' => 'Anthony',
                        'salary' => '19,000.00',
                        'profession' => 'Assistant',
                        'sex' => 'male',
                      ],
                    ],
                  ],
                  'Mary' => [
                    'name' => 'Mary',
                    'boss' => NULL,
                    'salary' => '20,000.00',
                    'profession' => 'Executive',
                    'sex' => 'female',
                    '>' => [
                      'Bernardo' => [
                        'name' => 'Bernardo',
                        'boss' => 'Mary',
                        'salary' => '14,000.00',
                        'profession' => 'Architect',
                        'sex' => 'male',
                      ],
                      'Chuck' => [
                        'name' => 'Chuck',
                        'boss' => 'Mary',
                        'salary' => '16,000.00',
                        'profession' => 'Engineer',
                        'sex' => 'male',
                        '>' => [
                          'Leila' => [
                            'name' => 'Leila',
                            'boss' => 'Chuck',
                            'salary' => '11,000.00',
                            'profession' => 'Engineer',
                            'sex' => 'female',
                          ],
                          'Donna' => [
                            'name' => 'Donna',
                            'boss' => 'Chuck',
                            'salary' => '10,000.00',
                            'profession' => 'Engineer',
                            'sex' => 'female',
                          ],
                          'Eddie' => [
                            'name' => 'Eddie',
                            'boss' => 'Chuck',
                            'salary' => '11,000.00',
                            'profession' => 'Engineer',
                            'sex' => 'male',
                            '>' => [
                              'Fred' => [
                                'name' => 'Fred',
                                'boss' => 'Eddie',
                                'salary' => '4,000.00',
                                'profession' => 'Intern',
                                'sex' => 'male',
                              ],
                            ],
                          ],
                        ],
                      ],
                    ],
                  ],
                ],
              ],
            ],
            $tree
        );

        //

        $data = $this->fixture(
            ['employee_id', 'parent_id', 'job_title', 'first_name'],
            [
              [1, NULL, 'Managing Director', 'Bill'],
              [2, 1, 'Customer Services', 'Angela'],
              [3, 1, 'Development Manager', 'Ben'],
              [4, 2, 'Assistant 1', 'Henry'],
              [5, 2, 'Assistant 2', 'Nicola'],
              [6, 3, 'Snr Developer', 'Kerry'],
              [7, 3, 'Assistant', 'James'],
              [8, 6, 'Jrn Developer', 'Tim'],
            ]
        );

        $tree = unfold_recursive($data, 'parent_id', 'employee_id');

        $this->assertTreeDump(
            "
            ├─ <null>: <null>
            │  ├─ Managing Director: Bill
            │  │  ├─ Customer Services: Angela
            │  │  │  └─ Assistant 1: Henry
            │  │  │  └─ Assistant 2: Nicola
            │  │  ├─ Development Manager: Ben
            │  │  │  ├─ Snr Developer: Kerry
            │  │  │  │  └─ Jrn Developer: Tim
            │  │  │  └─ Assistant: James
            ",
            debug($tree, "{job_title}: {first_name}")
        );

        $this->assertTreeDump(
            "
            ├─ Managing Director: Bill
            │  ├─ Customer Services: Angela
            │  │  └─ Assistant 1: Henry
            │  │  └─ Assistant 2: Nicola
            │  ├─ Development Manager: Ben
            │  │  ├─ Snr Developer: Kerry
            │  │  │  └─ Jrn Developer: Tim
            │  │  └─ Assistant: James
            ",
            debug($tree[null]['>'], "{job_title}: {first_name}")
        );

        $this->assertSame(
            [
              '' => [
                '>' => [
                  1 => [
                    'employee_id' => 1,
                    'parent_id' => NULL,
                    'job_title' => 'Managing Director',
                    'first_name' => 'Bill',
                    '>' => [
                      2 => [
                        'employee_id' => 2,
                        'parent_id' => 1,
                        'job_title' => 'Customer Services',
                        'first_name' => 'Angela',
                        '>' => [
                          4 => [
                            'employee_id' => 4,
                            'parent_id' => 2,
                            'job_title' => 'Assistant 1',
                            'first_name' => 'Henry',
                          ],
                          5 => [
                            'employee_id' => 5,
                            'parent_id' => 2,
                            'job_title' => 'Assistant 2',
                            'first_name' => 'Nicola',
                          ],
                        ],
                      ],
                      3 => [
                        'employee_id' => 3,
                        'parent_id' => 1,
                        'job_title' => 'Development Manager',
                        'first_name' => 'Ben',
                        '>' => [
                          6 => [
                            'employee_id' => 6,
                            'parent_id' => 3,
                            'job_title' => 'Snr Developer',
                            'first_name' => 'Kerry',
                            '>' => [
                              8 => [
                                'employee_id' => 8,
                                'parent_id' => 6,
                                'job_title' => 'Jrn Developer',
                                'first_name' => 'Tim',
                              ],
                            ],
                          ],
                          7 => [
                            'employee_id' => 7,
                            'parent_id' => 3,
                            'job_title' => 'Assistant',
                            'first_name' => 'James',
                          ],
                        ],
                      ],
                    ],
                  ],
                ],
              ],
            ],
            $tree
        );
    }

    private function fixture($headers, $data) : array
    {
        foreach ($data as $i => $row)
            $data[$i] = array_combine($headers, $row);

        return $data;
    }

    private function assertTreeDump(string $expected, string $dump)
    {
        $expected = implode(PHP_EOL, array_map(\ltrim::class, explode(PHP_EOL, trim($expected))));
        $this->assertEquals($expected, trim($dump));
    }
}
