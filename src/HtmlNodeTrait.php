<?php

/**
 * @package HtmlElement
 * @author  Andrey Solovyev <andrewsl@yandex.ru>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

declare(strict_types=1);

namespace anvii\HtmlElement;

/**
 * HtmlNodeTrait
 *
 * @author Andrey Solovyev
 */
trait HtmlNodeTrait
{
    /**
     * Parent element
     *
     * @var HtmlElement
     */
    protected $parent;

    /**
     * Array of children items
     * Children items are used in constructions like <ul>.....</ul>,
     * where <li></li> can be a child
     *
     * @var array
     */
    protected $items = [];

    /**
     * A character or a string to separate items
     *
     * @var string
     */
    protected $separator = '';


    /**
     * Get parent node
     *
     * @return HtmlElement
     */
    public function getParent() : HtmlElement
    {
        return $this->parent;
    }

    /**
     * Get children items
     *
     * @return array
     *   array items
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Add child element
     *
     * @param $item
     *   child element to add
     * @return HtmlElement
     *   $this
     */
    public function add($item) : HtmlElement
    {
        if (is_array($item))
        {
            foreach($item as $it)
            {
                $this->add($it);
            }
            return $this;
        }

        if ($item !== NULL)
        {
            $weight = ($item instanceof HtmlElement) ? $item->weight() : 0;
            $i = $this->count();
            while($i > 0)
            {
                $e = $this->items[$i-1];
                $e_weight = ($e instanceof HtmlElement) ? $e->weight() : 0;
                if ($weight >= $e_weight)
                    break;
                $this->items[$i] = $this->items[$i-1];
                $i--;
            }
            $this->items[$i] = $item;
            if ($item instanceof HtmlElement)
                $item->parent = $this;
        }
        return $this;
    }

    /**
     * Remove child element
     *
     * @param $child
     *   Child element to remove
     * @return $this
     */
    public function remove($child) : HtmlElement
    {
        $key = array_search($child, $this->items);
        if ($key === FALSE)
            return $this;

        unset($this->items[$key]);

        if ($child instanceof HtmlElement)
            $child->parent = NULL;

        return $this;
    }

    /**
     * Remove self from parent
     *
     * @return $this
     */
    public function removeSelf() : HtmlElement
    {
        if ($this->parent)
            $this->parent->remove($this);
        return $this;
    }

    /**
     * Clear all content
     *
     * @return $this
     */
    public function clear() : HtmlElement
    {
        foreach($this->items as $item)
            if ($item instanceof HtmlElement)
                $item->parent = NULL;

        $this->items = [];

        return $this;
    }

    /**
     * Get count of children elements
     *
     * @return int
     *   count of items
     */
    public function count() : int
    {
        return count($this->items);
    }

    /**
     * Get separator of children items
     * 
     * @return string
     */
    public function getSeparator()
    {
        return $this->separator;
    }

    /**
     * Set separator of children items
     * 
     * @param string $separator
     * @return $this
     */
    public function setSeparator($separator)
    {
        $this->separator = $separator;
        return $this;
    }

    /**
     * Reorder elements using attribute '_weight'
     * 
     * @return $this
     */
    public function reorder($compareFunc=NULL) : HtmlElement
    {
        if ($compareFunc !== NULL)
        {
            usort($this->items, $compareFunc);
        }
        else
        {
            usort($this->items, function($e1, $e2) {
                $weight1 = ($e1 instanceof HtmlElement) ? $e1->weight() : 0;
                $weight2 = ($e2 instanceof HtmlElement) ? $e2->weight() : 0;
                return $weight1 - $weight2;
            });
        }
        return $this;
    }

    /**
     * Render items of element
     *
     * @return string
     *   rendered string
     */
    public function renderItems() : string
    {
        $out = '';
        $glue = '';
        foreach($this->getItems() as $item)
        {
            $out .= $glue;
            $glue = $this->separator;

            if (!is_string($item) && is_callable($item))
                $item = call_user_func($item);
            if (is_string($item) && !$this->getAttribute('_raw'))
                $out .= htmlspecialchars($item);
            else
                $out .= $item;
        }
        return $out;
    }

}
