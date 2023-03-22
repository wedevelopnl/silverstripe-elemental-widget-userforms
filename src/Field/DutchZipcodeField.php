<?php

namespace WeDevelop\ElementalWidget\UserForm\Fields;

use SilverStripe\Forms\TextField;

class DutchZipcodeField extends TextField
{
    public function validate($validator): bool
    {
        $this->value = trim($this->value);
        $pattern = '{^[1-9][0-9]{3} ?(?!sa|sd|ss|SA|SD|SS)[A-Za-z]{2}$}';

        if ($this->value && !preg_match($pattern, $this->value)) {
            $validator->validationError(
                $this->name,
                'Dit is geen geldige postcode',
                'validation'
            );
            return false;
        }

        return true;
    }
}
