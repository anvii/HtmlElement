<?php

/**
 * @package HtmlElement
 * @author  Andrey Solovyev <andrewsl@yandex.ru>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

declare(strict_types=1);

namespace anvii\HtmlElement;

/**
 * Description of HtmlAttributes
 *
 * @author Andrey Solovyev
 */
trait HtmlAttributesTrait
{
    /**
     * Array to keep attribute values
     *
     * @var array
     */
    private $attributes = [];

    /**
     * Attribute is void
     *
     * @param string $attr
     *   Attribute name
     * @return bool
     *   True if $attr is a void attribute
     */
    protected function isVoidAttribute(string $attr) : bool
    {
        return \in_array($attr, self::VOID_ATTRIBUTES);
    }

    /**
     * Attribute is hidden
     * Hidden attribute's name starts with underscore '_'
     *
     * @param mixed $attr
     *   Attribute name: can be string, int...
     * @return bool
     *   True if $attr is a void attribute
     */
    protected function isHiddenAttribute($attr): bool
    {
        return is_string($attr) && strlen($attr) > 0 && $attr[0] == '_';
    }

    /**
     * Render void attribute
     *
     * @param string $name
     * @return string
     */
    private function renderVoidAttribute($name) : string
    {
        $out = '';
        if (!$this->isHiddenAttribute($name)) {
            $out .= $name;
        }
        return $out;
    }

    /**
     * Render one attribute to string
     *
     * @param string|int $name
     * @param mixed $value
     * @return string
     */
    private function renderAttribute($name, $value) : string
    {
        if (is_callable($name) && !is_string($name))
            $name = call_user_func($name, $this);

        if (is_callable($value) && !is_string($value))
            $value = call_user_func($value, $this);

        $name = (string)$name;
        $displayValue = $value;
        if (!$this->isHiddenAttribute($name))
            $displayValue = (string)$displayValue;
        $out = '';

        if (\is_numeric($name))
        {
            // In case ['readonly'] the $name in numeric
            $out .= $this->renderVoidAttribute($displayValue);
        }
        else if ($this->isVoidAttribute($name) || $value === true)
        {
            // Case ['readonly' => '1']
            if ($value) {
                $out .= $this->renderVoidAttribute ($name);
            }
        }
        else
        {
            if (!$this->isHiddenAttribute($name))
            {
                // Value can be presented by array, like class=["first", "odd", "content"]
                if (\is_array($value)) {
                    $displayValue = \implode(' ', $value);
                }
                $out .= \sprintf('%s="%s"', $name, \htmlspecialchars($displayValue));
            }
        }
        return $out;
    }

    /**
     * Make string from array of attributes
     *
     * @return string
     *   string like ' id="id1" class="class1 class2" readonly'
     */
    protected function renderAttributes() : string
    {
        $out = [];
        foreach($this->attributes as $name=>$value)
        {
            $out[] = $this->renderAttribute($name, $value);
        }
        return \implode(' ', $out);
    }

    /**
     * Get Html attributes
     *
     * @return array
     *   array of Html attributes
     */
    public function getAttributes() : array
    {
        return $this->attributes;
    }

    /**
     * Get attribute's value
     *
     * @param string $name
     *   attribute name
     * @return mixed|NULL
     *   attribute value or NULL.
     *   The attribute can be type of string, integer(eq. _weight) or any other
     */
    public function getAttribute(string $name)// : ?mixed
    {
        // Search by attribute name and return value
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }

        // Search by integer index return true
        foreach($this->attributes as $key=>$value)
        {
            // Attribute is void
            if (\is_numeric($key) && $value == $name) {
                return $name;
            }
        }

        // Not found
        return NULL;
    }

    /**
     * Set one attribute
     *
     * @param string $name
     *   attribute name
     * @param mixed $value
     *   attribute value
     * @return $this
     */
    public function setAttribute($name, $value=true)
    {
        if ($name === 'class') {
            $this->setClasses($value);
            return $this;
        }
        else if ($name === 'style') {
            $this->setStyles($value);
            return $this;
        }

        // Hidden attributes are added as is
        if ($this->isHiddenAttribute($name)) {
            $this->attributes[$name] = $value;
        }
        // Not logical (void) attributes
        else if ($value !== NULL && $value !== false) {
            $this->attributes[$name] = $value;
        }
        else {
            // Attribute with NULL value is omited
            //$this->attributes[] = $name;
            $this->removeAttribute($name);
        }

        return $this;
    }

    /**
     * Set html attributes
     *
     * @param array $attributes
     * @return $this
     */
    public function setAttributes(array $attributes)
    {
        foreach($attributes as $name => $value)
        {
            $this->setAttribute($name, $value);
        }
        return $this;
    }

    /**
     * Check if attribute is set
     *
     * @return bool
     *   TRUE if attribute is set
     */
    public function hasAttribute(string $name) : bool
    {
        // Search by attribute name and return true
        if (isset($this->attributes[$name]))
        {
            return true;
        }

        // Search by integer index return true
        foreach ($this->attributes as $key => $value)
        {
            if (\is_numeric($key) && $value === $name)
            {
                return true;
            }
        }

        // Not found
        return false;
    }

    /**
     * Remove attribute
     *
     * @param string $name
     *   attribute name
     * @return $this
     */
    public function removeAttribute(string $name)
    {
        // Search by attribute name
        if (isset($this->attributes[$name]))
        {
            unset($this->attributes[$name]);
            return $this;
        }

        // Search by integer index return true
        foreach($this->attributes as $key=>$value)
        {
            if (\is_numeric($key) && $value === $name)
            {
                unset($this->attributes[$key]);
            }
        }

        return $this;
    }
}
