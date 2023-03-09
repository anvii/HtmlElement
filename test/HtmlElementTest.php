<?php

/**
 * @package HtmlElement
 * @author  Andrey Solovyev <andrewsl@yandex.ru>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace anvii\HtmlElement;

require(__DIR__ . '/../../../autoload.php');

class HtmlElementTest extends \PHPUnit\Framework\TestCase
{
    public function testTag()
    {
        $n1 = new HtmlElement('p');
        $this->assertSame('<p></p>', (string)$n1);

        $n2 = HtmlElement::p();
        $this->assertSame((string)$n1, (string)$n2);

        $i1 = HtmlElement::img();
        $this->assertSame('<img>', (string)$i1);
    }

    public function testAttributes()
    {
        $div1 = HtmlElement::div(['id'=>'div1']);
        $this->assertSame('<div id="div1"></div>', (string)$div1);
        $this->assertFalse($div1->hasAttribute('width'));

        $div1->setAttribute('width', 150);
        $this->assertSame('<div id="div1" width="150"></div>', (string)$div1);
        $this->assertTrue($div1->hasAttribute('width'));

        $div1->removeAttribute('width');
        $this->assertSame('<div id="div1"></div>', (string)$div1);
        $this->assertFalse($div1->hasAttribute('width'));
    }

    public function testContent()
    {
        $div1 = HtmlElement::div([], 'Line1');
        $this->assertSame('<div>Line1</div>', (string)$div1);

        $div1->add(' ')->add('Line2');
        $this->assertSame('<div>Line1 Line2</div>', (string)$div1);

        $div1->add(' ')->add((new HtmlElement('', []))->add('Line3'));
        $this->assertSame('<div>Line1 Line2 Line3</div>', (string)$div1);

        $c0 = new HtmlElement('', ['_weight'=>-1]);
        $c0->add('Line0 ');
        $div1->add($c0);
        $this->assertSame('<div>Line0 Line1 Line2 Line3</div>', (string)$div1);

        $div1->remove('Line1');
        $this->assertSame('<div>Line0  Line2 Line3</div>', (string)$div1);

        $div1->remove($c0);
        $this->assertSame('<div> Line2 Line3</div>', (string)$div1);
    }

    public function testClass()
    {
        $div0 = HtmlElement::div(['class'=>NULL]);
        $this->assertSame('<div class=""></div>', (string)$div0);

        $div1 = HtmlElement::div(['class'=>'green wide']);
        $this->assertTrue($div1->hasClass('green'));
        $this->assertFalse($div1->hasClass('yellow'));

        $div1->removeClass('green')->addClass('yellow');
        $this->assertFalse($div1->hasClass('green'));
        $this->assertTrue($div1->hasClass('yellow'));

        $this->assertSame('<div class="wide yellow"></div>', (string)$div1);
    }

    public function testStyle()
    {
        $div0 = HtmlElement::div(['style'=>NULL]);
        $this->assertSame('<div style=""></div>', (string)$div0);
        $div0->addStyle(['margin: 0px;']);
        $this->assertSame('<div style="margin: 0px;"></div>', (string)$div0);
        $div0->addStyle(['float'=>'left']);
        $this->assertSame('<div style="margin: 0px; float: left;"></div>', (string)$div0);
        $float = $div0->getStyle('float');
        $this->assertSame('left', $float);

        $div1 = HtmlElement::div(['style'=>'display: block; width: 100%;']);
        $this->assertTrue($div1->hasStyle('display'));
        $this->assertTrue($div1->hasStyle('width'));
        $this->assertFalse($div1->hasStyle('block'));

        $div1->removeStyle('display')->addStyle('color', 'red');
        $this->assertFalse($div1->hasStyle('display'));
        $this->assertTrue($div1->hasStyle('color'));

        $this->assertSame('<div style="width: 100%; color: red;"></div>', (string)$div1);

        $div2 = HtmlElement::div(['style'=>['display'=>'block', 'width'=>'100%;']]);
        $this->assertSame('<div style="display: block; width: 100%;"></div>', (string)$div2);

    }

    public function testSort()
    {
        // forward sequence
        $div1 = HtmlElement::div();
        for($i=1; $i<=5; $i++)
        {
            $c = new HtmlElement('', ['_weight'=>$i], $i);
            $div1->add($c);
        }
        $this->assertSame('<div>12345</div>', (string)$div1);

        // backward sequence
        $div1 = HtmlElement::div();
        for($i=5; $i>0; $i--)
        {
            $c = new HtmlElement('', ['_weight'=>$i], $i);
            $div1->add($c);
        }
        $this->assertSame('<div>12345</div>', (string)$div1);

        // no sequence
        $div1 = HtmlElement::div();
        for($i=1; $i<=5; $i++)
        {
            $c = new HtmlElement('', [], $i);
            $div1->add($c);
        }
        $this->assertSame('<div>12345</div>', (string)$div1);
    }

    public static function samplePage()
    {
        $page = HtmlElement::html();
        $body = HtmlElement::body();
        $page->add($body);

        $form = HtmlElement::form();
        $body->add($form);

        $login = HtmlElement::input(['type'=>'text', 'name'=>'login', 'value'=>'user', 'id'=>'field-login']);
        $form->add($login);

        $pass = HtmlElement::input(['type'=>'password', 'name'=>'password', 'value'=>'pass', 'id'=>'field-password']);
        $form->add($pass);

        $submit = HtmlElement::input(['type'=>'submit', 'name'=>'op', 'value'=>'Submit', 'class'=>'grey']);
        $form->add($submit);

        return $page;
    }

    public function testQuery()
    {
        $page = self::samplePage();

        $inputs = $page->query('input');
        $this->assertCount(3, $inputs);

        $submits = $page->query('body input.grey[type=submit]');
        $this->assertCount(1, $submits);

        $inputs = $page->query('#field-login');
        $this->assertCount(1, $inputs);
    }

    public function testFirst()
    {
        $page = self::samplePage();

        $input = $page->first('input');
        $this->assertSame('login', $input->getAttribute('name'));

        $submit = $page->first('body input.grey[type=submit]');
        $this->assertSame('op', $submit->getAttribute('name'));

        $input = $page->first('#field-login');
        $this->assertSame('login', $input->getAttribute('name'));
    }

    public function testHiddenAttribute()
    {
        $channel = HtmlElement::channel(['_raw'], []);
        $this->assertSame('<channel></channel>', (string)$channel);
    }

    public function testSpace()
    {
        $div = HtmlElement::div([], ['A', ' ', 'B']);
        $this->assertSame('<div>A B</div>', (string)$div);
    }

    public function testReorder()
    {
        $div = HtmlElement::div([], [
            $a = new HtmlElement('', [], 'C'),
            $b = new HtmlElement('', [], 'A'),
            $c = new HtmlElement('', [], 'B'),
        ]);
        $a->setAttribute('_weight', 10);
        $b->setAttribute('_weight', -5);
        $c->setAttribute('_weight', 4);
        $div->reorder();
        $this->assertSame('<div>ABC</div>', (string)$div);

        $div->reorder(function($e1, $e2) {
            return $e2->weight() - $e1->weight();
        });
        $this->assertSame('<div>CBA</div>', (string)$div);
    }
};
