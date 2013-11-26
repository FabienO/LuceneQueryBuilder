<?php
require_once 'PHPUnit/Autoload.php';
require_once '/var/www/btsync/LuceneQueryBuilder/src/LuceneQueryBuilder/Query.php';
/**
 * Created by PhpStorm.
 * User: fab
 * Date: 26/11/13
 * Time: 17:29
 */

class lucenQueryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->query = new LuceneQueryBuilder\Query();
        $this->query->mayContain('Dr. Dre', 'title')->mustContain('Next Episode', 'body')->mustNotContain('Britney Spears', 'body')->mayHaveInProximity('Snoop Dogg', 'body', 10);
    }

    public function testStoresAssets()
    {
        $this->assertClassHasAttribute('query', 'LuceneQueryBuilder\Query');
        $this->assertClassHasAttribute('appendModifierLink', 'LuceneQueryBuilder\Query');
        $this->assertClassHasAttribute('appendModifier', 'LuceneQueryBuilder\Query');
    }

    public function testResult()
    {
        $result = $this->query->compile();
        $expected = 'title: "Dr. Dre" AND body: (+"Next Episode" -"Britney Spears" "Snoop Dogg"~10)';

        $this->assertEquals($result, $expected);
    }

    public function testAddPhrase()
    {
        $this->query->mustHaveInProximity('Eminem', 'body', 7);
        $expected = 'title: "Dr. Dre" AND body: (+"Next Episode" -"Britney Spears" "Snoop Dogg"~10 +"Eminem"~7)';
        $result = $this->query->compile();

        $this->assertEquals($result, $expected);
    }

    public function tearDown()
    {
        unset($this->query);
    }
}