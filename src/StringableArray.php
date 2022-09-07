<?php

/**
 * @package HtmlElement
 * @author  Andrey Solovyev <andrewsl@yandex.ru>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

declare(strict_types=1);

namespace anvii\HtmlElement;

/**
 * StringableArray
 *
 * @author Andrey Solovyev
 */
class StringableArray extends \ArrayObject
{
    /**
     * Items separator
     * @var string
     */
    private $separator = ' ';

    /**
     * Callback for formatting items to string
     * @var callable
     */
    private $format = [self::class, 'defaultFormat'];

    /**
     * Constructor
     *
     * @param callable $format
     *   Callback for formatting items to string
     */
    public function __construct(callable $format=NULL)
    {
        parent::__construct();
        if ($format !== NULL)
        {
            $this->setFormat($format);
        }
    }

    /**
     * Default callback for formatting items to string
     *
     * @param ArrayObject $items
     * @return string
     *   Formatted items
     */
    private function defaultFormat(\ArrayObject $items) : string
    {
        return \implode($this->separator, $items->getArrayCopy());
    }

    /**
     * Set new item separator
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
     * Set new callback for formatting the array
     *
     * @param callable $format
     * @return $this
     */
    public function setFormat(callable $format)
    {
        $this->format = $format;
        return $this;
    }

    /**
     * Magic function to make string form the object
     *
     * @return type
     */
    public function __toString()
    {
        return \call_user_func($this->format, $this);
    }
}
