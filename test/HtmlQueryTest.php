<?php

/**
 * @package HtmlElement
 * @author  Andrey Solovyev <andrewsl@yandex.ru>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace anvii\HtmlElement;

require(__DIR__ . '/../../../autoload.php');
require(__DIR__ . '/../src/HtmlQuery.php');

class HtmlQueryTest extends \PHPUnit\Framework\TestCase
{
    public static function sample1()
    {
        $html = HtmlElement::html([],
            HtmlElement::body([],
                HtmlElement::form(['id'=>'sample1_form', 'method'=>'POST'], [
                    HtmlElement::div(['class'=>'form-row'], [
                        HtmlElement::label([], 'Username'),
                        HtmlElement::input(['id'=>'field-login', 'name'=>'login', 'type'=>'text', 'value'=>'user', 'required']),
                    ]),
                    HtmlElement::div(['class'=>'form-row'], [
                        HtmlElement::label([], 'Password'),
                        HtmlElement::input(['id'=>'field-password', 'name'=>'password', 'type'=>'password', 'value'=>'pass', 'required']),
                    ]),
                    HtmlElement::div(['class'=>'form-buttons'], [
                        HtmlElement::input(['name'=>'op', 'type'=>'submit', 'value'=>'Submit', 'class'=>'grey']),
                        HtmlElement::button([], 'Cancel'),
                    ]),
                ])
            )
        );
        return $html;
    }

    public static function sample2()
    {
        $select = HtmlElement::select(['name'=>'select1'], [
            HtmlElement::option(['value'=>''], '-- Prompt --'),
            HtmlElement::option(['value'=>123], 'Title 123'),
        ]);
        return $select;
    }

    public static function sample3()
    {
        $form = HtmlElement::form(['name'=>'form1'], [
            HtmlElement::input(['type'=>'text', 'name'=>'title']),
        ]);
        return $form;
    }

    public function testParseSelector()
    {
        $selector = Selector::parseSelector('#identifier');
        $this->assertSame('identifier', $selector->id);

        $selector = Selector::parseSelector('table');
        $this->assertSame('table', $selector->tag);

        $selector = Selector::parseSelector('table.wide');
        $this->assertSame('table', $selector->tag);
        $this->assertSame('wide', $selector->class);

        $selector = Selector::parseSelector('table[width]');
        $this->assertSame('width', $selector->attr);

        $selector = Selector::parseSelector('table[width=100]');
        $this->assertSame('width', $selector->attr);
        $this->assertSame('100', $selector->value);

        $selector = Selector::parseSelector('*');
        $this->assertTrue($selector->any);
    }

    public function testParseQuery()
    {
        $query = Selector::parseQuery('#id');
        $this->assertCount(1, $query);
        $this->assertSame($query[0]->id, 'id');

        $query = Selector::parseQuery('form#id div input[type=button]');
        $this->assertCount(3, $query);

        $query = Selector::parseQuery('form#id div *');
        $this->assertCount(3, $query);
    }

    public function testMatchSelector()
    {
        $div1 = new HtmlElement('div', ['id'=>'logo', 'class'=>'narrow', 'align'=>'right']);

        $this->assertTrue(Selector::parseSelector(function($e) { return true; })->match($div1));
        $this->assertFalse(Selector::parseSelector(function($e) { return false; })->match($div1));

        $this->assertTrue(Selector::parseSelector('*')->match($div1));
        $this->assertFalse(Selector::parseSelector('-')->match($div1));

        $this->assertTrue(Selector::parseSelector('div')->match($div1));
        $this->assertFalse(Selector::parseSelector('table')->match($div1));

        $this->assertTrue(Selector::parseSelector('.narrow')->match($div1));
        $this->assertFalse(Selector::parseSelector('.wide')->match($div1));

        $this->assertTrue(Selector::parseSelector('#logo')->match($div1));
        $this->assertFalse(Selector::parseSelector('#photo')->match($div1));

        $this->assertTrue(Selector::parseSelector('[align]')->match($div1));
        $this->assertTrue(Selector::parseSelector('[align=right]')->match($div1));
        $this->assertTrue(Selector::parseSelector('[align="right"]')->match($div1));
        $this->assertFalse(Selector::parseSelector('[align=left]')->match($div1));

        $this->assertTrue(Selector::parseSelector('div#logo')->match($div1));
        $this->assertTrue(Selector::parseSelector('div.narrow[align=right]')->match($div1));
    }

    public function testQuery()
    {
        $page = self::sample1();

        $inputs = HtmlQuery::new($page)->query('input');
        $this->assertCount(3, $inputs);

        $submits = HtmlQuery::new($page)->query('body input.grey[type=submit]');
        $this->assertCount(1, $submits);

        $inputs = $page->query('#field-login');
        $this->assertCount(1, $inputs);

        $labels = HtmlQuery::new($page)->query(function($e) {
            return $e->getTag() == 'label';
        });
        $this->assertCount(2, $labels);
    }

    public function testFirst()
    {
        $page = self::sample1();

        $input = HtmlQuery::new($page)->first('input');
        $this->assertCount(1, $input);
        $this->assertSame('login', $input->getAttribute('name'));

        $submit = HtmlQuery::new($page)->first('body input.grey[type=submit]');
        $this->assertSame('op', $submit->getAttribute('name'));

        $input = HtmlQuery::new($page)->first('#field-login');
        $this->assertSame('login', $input->getAttribute('name'));

        $label = HtmlQuery::new($page)->first(function($e) {
            return $e->getTag() == 'label';
        });
        $this->assertCount(1, $label);
    }

    public function testEmpty()
    {
        $page = self::sample1();
        $this->expectNotToPerformAssertions();
        HtmlQuery::new($page)->query("none")->clear()->add("something");
    }

    public function testQueryAll()
    {
        $page = self::sample1();
        $all = $page->query('*');
        $this->assertGreaterThan(1, $all->count(), 'Wildcard');
    }

    public function testEmptyValue()
    {
        $select = self::sample2();
        $result = $select->query('[value=""]');
        $this->assertCount(1, $result, 'Empty value');
    }

    public function testNumericValue()
    {
        $select = self::sample2();
        $result = $select->query('[value="123"]', 'Numeric value');
        $this->assertCount(1, $result);
    }

    public function testNestedSearch()
    {
        $form = self::sample3();
        $result = $form->query('[name]');
        $this->assertCount(2, $result, 'Query nested elements along with found root element');
    }

};
