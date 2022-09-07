<?php

/**
 * @package HtmlElement
 * @author  Andrey Solovyev <andrewsl@yandex.ru>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

declare(strict_types=1);

namespace anvii\HtmlElement;

/**
 * Group of methods to work with attribute 'class'
 *
 * @author Andrey Solovyev
 */
trait HtmlClassTrait
{
    public function getClasses()
    {
        return $this->attributes['class'] ?? NULL;
    }

    /**
     * Create instance of ArrayStringable for classes
     *
     * @return StringableArray
     */
    private function createClassArray()
    {
        return new StringableArray();
    }

    /**
     * Parse attribute 'class'
     *
     * Class variants:
     *   'bg-light text-dark'
     *   ['bg-light', 'text-dark']
     *
     * @param string|array|NULL $classes
     * @return array
     *   ['bg-light', 'text-dark']
     */
    private function parseClasses($classes)
    {
        $classArray = [];

        switch(gettype($classes))
        {
            case 'string':
                return $this->parseClasses(\explode(' ', $classes));
            case 'array':
            case 'object':
                $classArray = $classes;
                break;
            case 'NULL':
                break;
            default:
                throw new \RuntimeException('Invalid type of parameter "classes" in parseClasses');
        }

        return $classArray;
    }

    /**
     * Set attribute 'class'
     *
     * @param string|array $classes
     * @return $this
     */
    public function setClasses($classes)
    {
        $classArray = $this->createClassArray();
        foreach($this->parseClasses($classes) as $name => $value)
            $classArray[$name] = $value;
        $this->attributes['class'] = $classArray;
        return $this;
    }

    /**
     * Add or replace a CSS class of element
     *
     * Use forms:
     *   addClass('display: block;')
     *   addClass('display', 'block')
     *   addClass(['display', 'block'])
     *
     * @param string:array $name
     *   Name of the CSS class
     * @return $this
     */
    public function addClass($name)
    {
        $classArray = $this->attributes['class'] ?? $this->createClassArray();
        foreach($this->parseClasses($name) as $class)
        {
            // Remove class if exists
            if (($index = array_search($class, $classArray->getArrayCopy())) !== false)
                $classArray->offsetUnset($index);

            // Add class
            $classArray[] = $class;
        }
        $this->attributes['class'] = $classArray;
        return $this;
    }

    /**
     * Check if element has a CSS class $className
     *
     * @param string $className
     *   Name of the CSS class
     *
     * @return bool
     *   TRUE on success
     */
    public function hasClass(string $className) : bool
    {
        return isset($this->attributes['class']) ?
            \in_array($className, $this->attributes['class']->getArrayCopy())
            : false;
    }

    /**
     * Remove a CSS class from element
     *
     * @param string $className
     *   Name of the CSS class
     * @return $this
     */
    public function removeClass($className)
    {
        if (isset($this->attributes['class']))
        {
            $index = array_search($className, $this->attributes['class']->getArrayCopy());
            if ($index !== false) {
                unset($this->attributes['class'][$index]);
            }
        }
        return $this;
    }

}
