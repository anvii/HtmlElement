<?php

/**
 * @package HtmlElement
 * @author  Andrey Solovyev <andrewsl@yandex.ru>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

declare(strict_types=1);

namespace anvii\HtmlElement;

/**
 * HtmlTagTrait
 *
 * @author Andrey Solovyev
 */
trait HtmlTagTrait
{
    /**
     * Tag of the element
     * The tag is used to construct HTML tag like <div></div>
     *
     * @var string
     */
    protected $tag;

    /**
     * Get tag of HtmlElement
     *
     * @return string
     *   Tag name of the element
     */
    public function getTag() : string
    {
        return $this->tag;
    }

    /**
     * Set tag of HtmlElement
     *
     * @param string $tag
     * @return $this
     */
    public function setTag(string $tag) : HtmlElement
    {
        $this->tag = $tag;
        return $this;
    }

    /**
     * Get weight of the element. This parameter is used for ordering of children elements.
     *
     * @return int
     *    weight of element
     */
    public function weight() : int
    {
        return $this->hasAttribute('_weight') ? intval($this->getAttribute('_weight')) : 0;
    }

    /**
     * Tag is self-closing
     *
     * @return bool
     *   True if tag is self-closing
     */
    protected function isSingleTag() : bool
    {
        return in_array(strtolower($this->getTag()), self::SINGLE_TAGS);
    }

    /**
     * Render HTML tag with attributes
     *
     * @param bool|NULL $selfClose
     *   Close tag. Null - by default
     * @return string
     *   rendered open tag string like <a ahref="..."> or <br />
     */
    protected function openTag(?bool $selfClose=NULL) : string
    {
        if ($selfClose === NULL)
            $selfClose = $this->isSingleTag();
        $attributes = $this->renderAttributes();
        if ($attributes)
            $attributes = ' ' . $attributes;
        return '<' . $this->getTag() . $attributes . '>';
    }

    /**
     * Close HTML tag
     *
     * @param $tag string
     *   HTML tag
     * @return string
     *   rendered close tag string like </a>
     */
    protected function closeTag()
    {
        return sprintf('</%s>', $this->getTag());
    }

}
