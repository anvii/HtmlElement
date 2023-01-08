<?php

/**
 * @package HtmlElement
 * @author  Andrey Solovyev <andrewsl@yandex.ru>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

declare(strict_types=1);

namespace anvii\HtmlElement;

/**
 * Base class for html elements, like menu, block etc
 * 
 * @method static HtmlElement div(array $attributes=[], $content=NULL)
 * @method static HtmlElement span(array $attributes=[], $content=NULL)
 * @method static HtmlElement pre(array $attributes=[], $content=NULL)
 * @method static HtmlElement img(array $attributes=[])
 * @method static HtmlElement i(array $attributes=[], $content=NULL)
 * @method static HtmlElement p(array $attributes=[], $content=NULL)
 * @method static HtmlElement a(array $attributes=[], $content=NULL)
 * @method static HtmlElement ul(array $attributes=[], $content=NULL)
 * @method static HtmlElement menu(array $attributes=[], $content=NULL)
 * @method static HtmlElement li(array $attributes=[], $content=NULL)
 * @method static HtmlElement h1(array $attributes=[], $content=NULL)
 * @method static HtmlElement h2(array $attributes=[], $content=NULL)
 * @method static HtmlElement h3(array $attributes=[], $content=NULL)
 * @method static HtmlElement h4(array $attributes=[], $content=NULL)
 * @method static HtmlElement h5(array $attributes=[], $content=NULL)
 * @method static HtmlElement h6(array $attributes=[], $content=NULL)
 * @method static HtmlElement header(array $attributes=[], $content=NULL)
 * @method static HtmlElement footer(array $attributes=[], $content=NULL)
 * @method static HtmlElement form(array $attributes=[], $content=NULL)
 * @method static HtmlElement button(array $attributes=[], $content=NULL)
 * @method static HtmlElement input(array $attributes=[], $content=NULL)
 * @method static HtmlElement label(array $attributes=[], $content=NULL)
 * @method static HtmlElement select(array $attributes=[], $content=NULL)
 * @method static HtmlElement option(array $attributes=[], $content=NULL)
 * @method static HtmlElement textarea(array $attributes=[], $content=NULL)
 * @method static HtmlElement table(array $attributes=[], $content=NULL)
 * @method static HtmlElement thead(array $attributes=[], $content=NULL)
 * @method static HtmlElement tfoot(array $attributes=[], $content=NULL)
 * @method static HtmlElement tbody(array $attributes=[], $content=NULL)
 * @method static HtmlElement tr(array $attributes=[], $content=NULL)
 * @method static HtmlElement td(array $attributes=[], $content=NULL)
 * @method static HtmlElement th(array $attributes=[], $content=NULL)
 * @method static HtmlElement html(array $attributes=[], $content=NULL)
 * @method static HtmlElement head(array $attributes=[], $content=NULL)
 * @method static HtmlElement body(array $attributes=[], $content=NULL)
 * @method static HtmlElement script(array $attributes=[], $content=NULL)
 * @method static HtmlElement style(array $attributes=[], $content=NULL)
 */
class HtmlElement
{
    use HtmlTagTrait;
    use HtmlAttributesTrait;
    use HtmlClassTrait;
    use HtmlStyleTrait;
    use HtmlNodeTrait;
    use HtmlFinderTrait;

    /**
     * Tags to be closed immediately
     */
    const SINGLE_TAGS = ['hr', 'img', 'meta', 'link', 'base', 'br', 'input', 'itemscope'];

    /**
     * Attributes without values
     */
    const VOID_ATTRIBUTES = ['readonly', 'disabled', 'checked', 'selected', 'required', 'autofocus', 'novalidate', 'formnovalidate', 'async'];


    /**
     * Function name or file name used to render the element
     */
    public $template;

    /**
     * Construct element object
     *
     * @param $tag string
     *   Tag of the element
     * @param $attributes array
     *   array of attributes, like ['id'=>'form1', 'enctype'=>'multipart/form-data']
     */
    public function __construct($tag='div', array $attributes=[], $content=NULL)
    {
        $this->setTag($tag);
        $this->setAttributes($attributes);
        if (!is_null($content))
            $this->add($content);
    }

    /**
     * Create an instance of HtmlElement containing output from php code
     *
     * @param string $filename
     *   filename to include
     * @param array $data
     *   parameters for included file
     * @return HtmlElement
     */
    public static function createFromFile(string $filename, array $data=[]) : HtmlElement
    {
        // Template is a file
        extract($data);
        ob_start();
        include($filename);
        $out = ob_get_contents();
        ob_end_clean();
        return new static('', ['_raw'], $out);
    }

    /**
     * Magic function to convert object to string
     *
     * @return string
     *   rendered content
     */
    public function __toString() : string
    {
        return $this->render();
    }

    /**
     * Magic method to construct a node using HtmlElement::tagname($attributes)
     *
     * @param string $tag
     *   An HTML tag
     * @param array $params
     *   Parameters [attributes]
     * @return HtmlElement
     *   Instance of HtmlElement
     */
    public static function __callstatic($tag, $params)
    {
        $attributes = isset($params[0]) ? $params[0] : [];
        $content = isset($params[1]) ? $params[1] : NULL;
        return new static($tag, $attributes, $content);
    }

    /**
     * Create an instance of HtmlElement with raw (not encoded) text
     * @param string $text
     * @return HtmlElement
     */
    public static function raw($text)
    {
        return new HtmlElement('', ['_raw'], $text);
    }

    /**
     * Create an instance of HtmlElement containing raw (not encoded) text with arguments like in printf
     * @param string $format
     * @param ...
     * @return HtmlElement
     */
    public static function rawf($format) : HtmlElement
    {
        $text = call_user_func_array('sprintf', func_get_args());
        return new HtmlElement('', ['_raw'], $text);
    }

    /**
     * Check wether a node is empty, i.e node doesn't have children
     *
     * @return bool
     */
    public function isEmpty() : bool
    {
        // Element like <img> is not empty
        if ($this->isSingleTag())
            return false;

        foreach ($this->getItems() as $item)
        {
            if ($item instanceof HtmlElement && !$item->isEmpty())
                return false;
            else if (is_string($item) && $item != '')
                return false;
        }
        return true;
    }

    /**
     * Universal renderering method  for element
     * This method can be overloaded by child class
     *
     * @return string
     */
    public function render() : string
    {
        if (!empty($this->template))
            return $this->renderTemplate($this->template);

        if ($this->hasAttribute('_hideempty') && $this->isEmpty())
            return '';

        if ($this->hasAttribute('_if')) {
            $_if = $this->getAttribute('_if');
            if (is_callable($_if) && !is_string($_if))
                $_if = call_user_func($_if, $this);
            if (!$_if)
                return '';
        }

        return $this->renderDefault();
    }

    /**
     * Default rendering method
     *
     * @return string
     */
    public function renderDefault() : string
    {
        if ($this->isSingleTag() && empty($this->items))
        {
            $out = $this->renderStart();
        }
        else
        {
            $out = $this->renderStart();
            $out .= $this->renderItems();
            $out .= $this->renderEnd();
        }
        return $out;
    }

    /**
     * Create buffer with rendered php file
     * In addition to Html::renderTemplate template has member $this
     * pointed to HtmlElement class instance
     *
     * @param $template
     *   template file or callable
     * @param array $data
     *   array of variables
     * @return string
     *   Rendered buffer
     */
    public function renderTemplate($template, $data=[]) : string
    {
        // Template is a function or a method
        if (is_callable($template))
            return call_user_func_array($template, $data);

        // Template is a file
        extract($data);
        ob_start();
        include($template);
        $out = ob_get_contents();
        ob_end_clean();
        return $out;
    }

    /**
     * Start rendering - open tag
     *
     * @return string
     */
    public function renderStart() : string
    {
        if (empty($this->getTag()))
            return '';
        $selfClose = $this->isSingleTag() && empty($this->items);
        return $this->openTag($selfClose);
    }

    /**
     * Finish rendering - close tag
     *
     * @return string
     */
    public function renderEnd() : string
    {
        return !empty($this->getTag()) ? $this->closeTag() : '';
    }

    /**
     * Generate HTML attribute 'id' and set it to the element
     * @return string Generated ID
     */
    public function createId()
    {
        static $seq=0;
        $id = $this->getAttribute('id');
        if (!$id)
        {
            $seq++;
            $base = strtolower(get_called_class());
            $p = strrpos($base, '\\');
            if ($p !== false)
                $base = substr($base, $p+1);
            $id = sprintf('%s-%d',$base, $seq);
            $this->setAttribute('id', $id);
        }
        return $id;
    }

};
