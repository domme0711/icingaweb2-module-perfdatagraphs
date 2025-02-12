<?php

namespace Tests\Icinga\Module\Perfdatagraphs;

use Icinga\Module\Perfdatagraphs\Model\PerfdataResponse;
use Icinga\Module\Perfdatagraphs\Model\PerfdataSet;
use Icinga\Module\Perfdatagraphs\Model\PerfdataSeries;

use PHPUnit\Framework\TestCase;

final class PerfdataResponseTest extends TestCase
{
    public function test_perfdataresponse()
    {
        $pfr = new PerfdataResponse();

        $this->assertTrue($pfr->isEmpty());

        $ds = new PerfdataSet('myset', 'theunit');

        $s1 = new PerfdataSeries('foo', [1,2]);
        $s2 = new PerfdataSeries('bar', [3,4]);

        $ds->addSeries($s1);
        $ds->addSeries($s2);

        $pfr->addDataset($ds);

        $this->assertEquals($ds, $pfr->getDataset('myset'));

        $pfr->addError('WRONG!');

        $expected = '{"errors":["WRONG!"],"data":[{"title":"myset","unit":"theunit","timestamps":[],"series":[{"name":"foo","values":[1,2]},{"name":"bar","values":[3,4]}]}]}';
        $actual = json_encode($pfr);

        $this->assertFalse($pfr->isValid());
        $this->assertFalse($pfr->isEmpty());

        $this->assertEquals($expected, $actual);
    }

    public function test_perfdata_merge_without_data()
    {
        $pfr = new PerfdataResponse();

        $ds = new PerfdataSet('myset', 'theunit');

        $s1 = new PerfdataSeries('foo', [1,2]);
        $s2 = new PerfdataSeries('bar', [3,4]);

        $ds->addSeries($s1);
        $ds->addSeries($s2);

        $pfr->addDataset($ds);

        $pfr->mergeCustomVars([]);

        $expected = '{"errors":[],"data":[{"title":"myset","unit":"theunit","timestamps":[],"series":[{"name":"foo","values":[1,2]},{"name":"bar","values":[3,4]}]}]}';
        $actual = json_encode($pfr);

        $this->assertFalse($pfr->isValid());

        $this->assertEquals($expected, $actual);
    }

    public function test_perfdata_merge_with_data()
    {
        $pfr = new PerfdataResponse();

        $ds = new PerfdataSet('myset', 'theunit');

        $s1 = new PerfdataSeries('foo', [1,2]);
        $s2 = new PerfdataSeries('bar', [3,4]);

        $ds->addSeries($s1);
        $ds->addSeries($s2);

        $pfr->addDataset($ds);

        $customvars = [
            'myset' => [
                'unit' => 'load',
                'fill' => 'rgba(1, 1, 1, 1)',
                'stroke' => 'rgba(2, 2, 2, 2)',
            ]
        ];

        $pfr->mergeCustomVars($customvars);

        $expected = '{"errors":[],"data":[{"title":"myset","unit":"load","fill":"rgba(1, 1, 1, 1)","stroke":"rgba(2, 2, 2, 2)","timestamps":[],"series":[{"name":"foo","values":[1,2]},{"name":"bar","values":[3,4]}]}]}';
        $actual = json_encode($pfr);

        $this->assertFalse($pfr->isValid());

        $this->assertEquals($expected, $actual);
    }

    public function test_perfdata_isvalid()
    {
        $pfr = new PerfdataResponse();

        $ds = new PerfdataSet('myset', 'theunit');
        $ds->setTimestamps([1,2]);

        $s1 = new PerfdataSeries('foo', [1,2]);
        $s2 = new PerfdataSeries('bar', [3,4]);

        $ds->addSeries($s1);
        $ds->addSeries($s2);

        $pfr->addDataset($ds);

        $this->assertTrue($pfr->isValid());
    }
}
