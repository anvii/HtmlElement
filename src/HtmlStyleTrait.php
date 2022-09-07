<?php

/**
 * @package HtmlElement
 * @author  Andrey Solovyev <andrewsl@yandex.ru>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

declare(strict_types=1);

namespace anvii\HtmlElement;

/**
 * Group of methods to work with attribute 'style'
 *
 * @author Andrey Solovyev
 */
trait HtmlStyleTrait
{
    /**
     * Get attribute 'style'
     * @return StringableArray|NULL
     */
    public function getStyles()
    {
        return $this->attributes['style'] ?? NULL;
    }

    /**
     * Parse 'style' statement
     *
     * Style variants:
     *   'display: block;'
     *   ' float: left'
     *   ['display: block;']
     *   ['float'=>'left']
     *
     * @param string|array $style
     * @return array
     *   array of [name, value]
     */
    private function parseStyle($style)
    {
        // Split style into $name and $value
        if (\is_string($style))
        {
            [$name, $value] = \explode(':', $style);
        }
        else if (\is_array($style))
        {
            foreach($style as $name => $value)
            {
                if (\is_numeric($name))
                {
                    [$name, $value] = $this->parseStyle($value);
                }
                break;
            }
        }
        else
        {
            throw new \RuntimeException('Style must be type of string or array');
        }

        $name = \trim($name);
        $value = \trim($value);
        // Remove trailing ';'
        if (($len=strlen($value)) > 0 && $value[$len-1] == ';')
            $value = substr($value, 0, $len-1);
        return [$name, $value];
    }

    /**
     * Callback function for ArrayStringable
     *
     * @param array $items
     * @return string
     */
    public static function formatStyles($items)
    {
        $result = [];
        foreach($items as $name => $value)
        {
            $result[] = $name . ': ' . $value . ';';
        }
        return \implode(' ', $result);
    }

    /**
     * Parse attribute 'style'
     *
     * Style variants:
     *   'display: block; float: left'
     *   ['display: block;', 'float: left']
     *   ['display'=>'block', 'float'=>'left']
     *
     * @param string|array|NULL $styles
     * @return array
     *   [['display'=>'block'], ['float'=>'left']]
     */
    private function parseStyles($styles)
    {
        $styleAttribute = [];
        if (is_string($styles)) {
            $styles = explode(';', $styles);
        }

        if (is_array($styles))
        {
            foreach ($styles as $name => $value)
            {
                if (!empty($value))
                {
                    if (\is_numeric($name))
                    {
                        [$name, $value] = $this->parseStyle($value);
                    }
                    else
                    {
                        [$name, $value] = $this->parseStyle([$name => $value]);
                    }
                    $styleAttribute[$name] = $value;
                }
            }
        }
        else if ($styles === NULL)
        {
            // Nothing to do
        }
        else
        {
            throw new \RuntimeException('Styles must be an array');
        }
        return $styleAttribute;
    }

    /**
     * Create instance of ArrayStringable for styles
     *
     * @return StringableArray
     */
    private function createStyleArray()
    {
        return new StringableArray([self::class, 'formatStyles']);
    }

    /**
     * Set attribute 'style'
     *
     * @param string|array $styles
     * @return $this
     */
    public function setStyles($styles)
    {
        $styleArray = $this->createStyleArray();
        foreach($this->parseStyles($styles) as $name => $value)
            $styleArray[$name] = $value;
        $this->attributes['style'] = $styleArray;
        return $this;
    }

    /**
     * Add or replace a CSS style of element
     *
     * Use forms:
     *   setStyle('display: block;')
     *   setStyle('display', 'block')
     *   setStyle(['display', 'block'])
     *
     * @param string:array $name
     *   Name of the CSS style
     * @value string|NULL
     *   Optional value
     * @return $this
     */
    public function addStyle($name, $value=NULL)
    {
        if (is_string($value)) {
            $style = $this->parseStyle([$name=>$value]);
        }
        else if ($value === NULL) {
            $style = $this->parseStyle($name);
        }
        else {
            throw new RuntimeException('Invalid arguments for HtmlStyleTrait::setStyle');
        }

        $styleArray = $this->attributes['style'] ?? $this->createStyleArray();
        [$name, $value] = $style;
        $styleArray[$name] = $value;
        $this->attribute['style'] = $styleArray;

        return $this;
    }

    /**
     * Check if element has a CSS style $styleName
     *
     * @param string $styleName
     *   Name of the CSS style
     *
     * @return bool
     *   TRUE on success
     */
    public function hasStyle(string $styleName) : bool
    {
        return isset($this->attributes['style'][$styleName]);
    }

    /**
     * Get style value
     * @param string $styleName
     * @return string|null
     */
    public function getStyle(string $styleName) : ?string
    {
        return $this->attributes['style'][$styleName] ?? NULL;
    }

    /**
     * Remove a CSS style from element
     *
     * @param string $styleName
     *   Name of the CSS style
     * @return $this
     */
    public function removeStyle($styleName)
    {
        if (isset($this->attributes['style'][$styleName]))
            unset($this->attributes['style'][$styleName]);
        return $this;
    }

}
