<?php
namespace formslib\Helper;

final class GenericMultiHelper
{
    /**
     * Populate an array of values for a GenericMulti field
     * @see \formslib\Field\GenericMulti
     *
     * @param array $vars Variables array to populate
     * @param string $fieldName Name of the field
     * @param array $values Values to populate with
     */
    public static function populateVars(array &$vars, $fieldName, $values)
    {
        $count = 0;
        $control = [];

        if (is_array($values))
        {
            foreach ($values as $value)
            {
                $count++;
                $vars["{$fieldName}__$count"] = $value;
                $control[] = $count;
            }
        }

        $vars["{$fieldName}__control"] = implode(',', $control);
    }
}