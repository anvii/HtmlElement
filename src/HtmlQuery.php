<?php

/**
 * @package HtmlElement
 * @author  Andrey Solovyev <andrewsl@yandex.ru>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

declare(strict_types=1);

namespace anvii\HtmlElement;

// tag.class[]#id
class Selector
{
    /**
     * Callback function. Cannot be string.
     */
    public $callback;

    /**
     * Any element "*" queried
     *
     * @var bool
     */
    public $any = false;

    /**
     * Parsed tag
     *
     * @var string
     */
    public $tag;

    /**
     * Parsed class
     *
     * @var string
     */
    public $class;

    /**
     * Parsed id
     *
     * @var string
     */
    public $id;

    /**
     * Parsed attribute
     *
     * @var string
     */
    public $attr;

    /**
     * Parsed attribute's value
     *
     * @var string
     */
    public $value;

    /**
     * Parse CSS selector (simple code)
     *
     * @param string|callback $selector
     * @return Selector
     */
    public static function parseSelector($selector) : Selector
    {
        $instance = new Selector();

        if (is_callable($selector) && !is_string($selector))
        {
            $instance->callback = $selector;
        }
        else if ($selector === '*')
        {
            $instance->any = true;
        }
        else if (preg_match('/^(?P<tag>[\w\-_]+)?(\.(?P<class>[\w\-_]+))?(#(?P<id>[\w\-_]+))?(\[(?P<attr>[\w\-_]+)(=[\\\'"]?(?P<value>.*?)[\\\'"]?)?\])?$/', $selector, $matches, PREG_UNMATCHED_AS_NULL))
        {
            if (isset($matches['tag']))
                $instance->tag = $matches['tag'];
            if (isset($matches['class']))
                $instance->class = $matches['class'];
            if (isset($matches['id']))
                $instance->id = $matches['id'];
            if (isset($matches['attr']))
                $instance->attr = $matches['attr'];
            if (isset($matches['value']))
                $instance->value = $matches['value'];
        }

        return $instance;
    }

    /**
     * Parse query into array of assotiative arrays
     *
     * @param string $query
     * @return array
     *   array of Selector
     */
    public static function parseQuery($query) : array
    {
        $result = [];
        if (is_callable($query))
        {
            $instance = static::parseSelector($query);
            if ($instance)
                $result[] = $instance;
        }
        else
        {
            $selectorArray = preg_split('|\s+|', $query);
            foreach($selectorArray as $selector)
            {
                $instance = static::parseSelector($selector);
                if ($instance)
                    $result[] = $instance;
            }
        }

        return $result;
    }

    /**
     * Check if element matches query
     *
     * @param HtmlElement $e
     * @return bool
     *   true on success
     */
    public function match(HtmlElement $e) : bool
    {
        if ($this->callback)
            return call_user_func($this->callback, $e);
        if ($this->any)
            return true;
        if (isset($this->tag) && $e->getTag() != $this->tag)
            return false;
        if (isset($this->class) && !$e->hasClass($this->class))
            return false;
        if (isset($this->id) && $e->getAttribute('id') !== $this->id)
            return false;
        if (isset($this->attr) && !$e->hasAttribute($this->attr))
            return false;
        // Value can be numeric, that is why using not strong comparision
        if (isset($this->value) && $e->getAttribute($this->attr) != $this->value)
            return false;
        return true;
    }
}


/**
 * Query class like jQuery
 * 
 * Usage:
 *      $result = HtmlQuery::query($e, '.container input');
 *      HtmlQuery::query($e, '.container input')->addClass('form-input');
 *      HtmlQuery::query($e, '.container input')->each(function($e) { ... });
 * 
 * The query has forms:
 *   * - All elements
 *   [name] - has attribute 'name'
 *   [name="value"] - by attribute
 *   .class
 *   tag
 *   #id
*/
class HtmlQuery implements \Countable, \ArrayAccess, \Iterator
{
    protected $elements = [];
    private $position = 0;

    /**
     * HtmlQuery contructor
     * 
     * @param HtmlElement $e
     */
    public function __construct(HtmlElement $e=NULL)
    {
        if ($e !== NULL)
            $this->elements[] = $e;
    }

    /**
     * Create an instance of HtmlQuery
     * @return HtmlQuery
     */
    public static function new(HtmlElement $e=NULL) : HtmlQuery
    {
        return new HtmlQuery($e);
    }

    /**
     * Add array of elements
     * 
     * @param array $elements
     *   Elements to add
     */
    public function addElements(array $elements)
    {
        foreach($elements as $e)
        {
            $this->elements[] = $e;
        }
    }

    /**
     * Get an array of found items
     * 
     * @return array
     */
    public function getElements() : array
    {
        return $this->elements;
    }

    /**
     * Find elements by given criteria (query)
     *
     * @param string|array|callable $query
     *   rule to find element
     * @return HtmlQuery
     *   HtmlQuery with array of found elements or emty array
     */
    public function query($query) : HtmlQuery
    {
        $result = static::new();

        if (empty($query))
            return $result;

        if (!is_array($query))
            $query = Selector::parseQuery($query);

        foreach($this->elements as $e)
        {
            $q = $query;
            $selector = $query[0];

            // Check if element matches selector
            if ($selector->match($e))
            {
                // Next selector if not any or not callback
                if ($selector->callback || $selector->any)
                {
                    $result->addElements([$e]);
                }
                else
                {
                    array_shift($q);
                    if (count($q) == 0)
                        $result->addElements([$e]);
                }
            }

            // Check nested elements
            foreach($e->getItems() as $next)
            {
                if ($next instanceof HtmlElement)
                {
                    // Check elements with shifted query
                    $children = static::new($next)->query($q);
                    $result->addElements($children->elements);

                    // Check elements with full query
                    if (count($q) !== count($query))
                    {
                        $children = static::new($next)->query($query);
                        $result->addElements($children->elements);
                    }
                }
            }
        } // foreach($this->elements as $e)

        return $result;
    }

    /**
     * Find first element by given criteria (query)
     *
     * @param string|array $query
     *   rule to find element
     * @return HtmlQuery
     *   HtmlQuery with array of found elements or emty array
     */
    public function first($query) : HtmlQuery
    {
        $result = static::new();

        if (empty($query))
            return $result;

        if (!is_array($query))
            $query = Selector::parseQuery($query);

        // Check if element matches selector
        foreach($this->elements as $e)
        {
            $q = $query;
            $selector = $q[0];
            if ($selector->match($e))
            {
                array_shift($q);
                if (count($q) == 0)
                {
                    $result->addElements([$e]);
                    return $result;
                }
            }

            // Check nested elements
            foreach($e->getItems() as $next)
            {
                if ($next instanceof HtmlElement)
                {
                    $child = static::new($next)->first($q);
                    if ($child->count())
                    {
                        $result->addElements($child->elements);
                        return $result;
                    }
                }
            }
        } // foreach($this->elements as $e)

        return $result;
    }

    /**
     * Invoke function for each element
     */
    public function each(callable $function)
    {
        foreach($this->elements as $e)
        {
            call_user_func($function, $e);
        }
    }

    /**
     * Implement method count()
     */
    public function count(): int
    {
        return count($this->elements);
    }

    /**
     * Implement method offsetExists()
     */
    public function offsetExists($offset): bool
    {
        return in_array($offset, array_keys($this->elements), true);
    }

    /**
     * Implement method offsetGet()
     */
    public function offsetGet($offset): mixed
    {
        return $this->elements[$offset];
    }

    /**
     * Implement method offsetSet()
     */
    public function offsetSet($offset, $value): void
    {
        $this->elements[$offset] = $value;
    }

    /**
     * Implement method offsetUnset()
     */
    public function offsetUnset($offset): void
    {
        unset($this->elements[$offset]);
    }

    /**
     * Get element at index
     */
    public function get(int $index)
    {
        return array_key_exists($index, $this->elements) ? $this->elements[$index] : NULL;
    }

    /**
     * Redirect calls to children
     */
    public function __call(string $name, array $arguments=NULL)
    {
        $response = NULL;
        foreach($this->elements as $e)
        {
            $r = call_user_func_array([$e, $name], $arguments);
            if ($response === NULL)
            {
                // Return HtmlQuery if result is HtmlElement
                if ($r instanceof HtmlElement)
                    $response = new HtmlQuery($r);
                else if ($r instanceof HtmlQuery)
                    $response = HtmlQuery::new()->addElements($r->elements);
                else
                    $response = $r;
            }
            else if ($response instanceof HtmlQuery)
            {
                // Continue adding HtmlElements
                $response->addElements([$r]);
            }
            else
            {
                $response = $r;
            }
        }

        // Return empty HtmlQuery if no result
        return $response === NULL ? new HtmlQuery() : $response;
    }

    /**
     * Implement method rewind()
     */
    public function rewind() : void
    {
        $this->position = 0;
    }

    /**
     * Implement method current()
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function current()
    {
        return $this->elements[$this->position];
    }

    /**
     * Implement method key()
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function key()
    {
        return $this->position;
    }

    /**
     * Implement method next()
     */
    public function next() : void
    {
        $this->position++;
    }

    /**
     * Implement method valid()
     */
    public function valid() : bool
    {
        return isset($this->elements[$this->position]);
    }
}
