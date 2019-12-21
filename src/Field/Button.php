<?php
namespace formslib\Field;

use formslib\Utility\Security;

class Button extends Field
{
    protected $button_text = '';
    protected $fa_icon = '';

    public function getHTML()
    {
        $html = '';

        $html .= '<div class="btn-group" data-toggle="buttons">' . CRLF;
        $html .= '<button ' . $this->_class_attr() . $this->_custom_attr(). ')">';
        if ($this->fa_icon != '') $html .= '<i class="fa fa-'.$this->fa_icon.'"></i> ';
        $html .= Security::escapeHtml($this->button_text);
        $html .= '</button>';
        $html .= '</div>' . CRLF;

        return $html;
    }

    public function &setButtonText($text)
    {
        $this->button_text = $text;

        return $this;
    }

    public function &setFontAwesomeIcon($icon)
    {
        $this->fa_icon = $icon;

        return $this;
    }
}