<?php

/**
 * @package HtmlElement
 * @author  Andrey Solovyev <andrewsl@yandex.ru>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

declare(strict_types=1);

namespace anvii\HtmlElement;

/**
 * Trait HtmlFinder
 *
 * @author Andrey Solovyev
 */
trait HtmlFinderTrait
{
    /**
     * Query element to find all matches
     * 
     * @param string $query
     *   Query selector
     * @return HtmlQuery
     */
    public function query($query) : HtmlQuery
    {
        return HtmlQuery::new($this)->query($query);
    }

    /**
     * Query element to find first occurence
     * 
     * @param string $query
     *   Query selector
     * @return HtmlQuery
     */
    public function first($query) : HtmlQuery
    {
        return HtmlQuery::new($this)->first($query);
    }

    /**
     * Walk through tree of elements and invoke callback function
     *
     * @param callable $callback
     * @param $data
     */
    public function __walktree(callable $callback, &$data=NULL) : void
    {
        call_user_func($callback, $this, $data);
        foreach($this->getItems() as $item)
        {
            if (is_a($item, self::class))
                $item->__walktree($callback, $data);
        }
    }

    /**
     * Check if element matches selector
     *
     * @param string|array $selector
     *   Array of tag/values for single element or '*'
     * @return bool
     *   true on success
     */
    /*
    protected function _matchSelector($selector) : bool
    {
        if (empty($selector))
            return false;

        if ($selector == '*')
            return true;

        if (is_string($selector))
            return false;

        if (!empty($selector['tag']) && $selector['tag'] != $this->tag)
            return false;
        else if (!empty($selector['class']) && !$this->hasClass($selector['class']))
            return false;
        else if (!empty($selector['id']) && $selector['id'] != $this->getAttribute('id'))
            return false;
        else if (!empty($selector['attr']) && !$this->hasAttribute($selector['attr']))
            return false;
        else if (!empty($selector['attr']) && isset($selector['value']) && $this->getAttribute($selector['attr']) != $selector['value'])
            return false;

        return true;
    }
    */

    /**
     * Parse selector into an assotiative arrays
     *
     * @param string|array $selector
     * @return array
     *   array([tag, class, id, attr, value], ...)
     */
    protected static function _parseSelector($selector) : array
    {
        $result = [];

        $selectorArray = preg_split('|\s+|', $selector);
        foreach ($selectorArray as $sel)
        {
            //if (preg_match('/^(?P<tag>[\w\-_]+)?(\.(?P<class>[\w\-_]+))?(#(?P<id>[\w\-_]+))?(\[(?P<attr>[\w\-_]+)(=[\\\'"]?(?P<value>[\w\-_\.\/\[\]]*)[\\\'"]?)?\])?$/', $sel, $matches))
            if (preg_match('/^(?P<tag>[\w\-_]+)?(\.(?P<class>[\w\-_]+))?(#(?P<id>[\w\-_]+))?(\[(?P<attr>[\w\-_]+)(=[\\\'"]?(?P<value>.*?)[\\\'"]?)?\])?$/', $sel, $matches))
                $result[] = $matches;
            else
                $result[] = $sel;
        }

        return $result;
    }
}
