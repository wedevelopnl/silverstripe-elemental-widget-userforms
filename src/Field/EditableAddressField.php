<?php

declare(strict_types=1);

namespace WeDevelop\ElementalWidget\UserForm\Fields;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextField;
use SilverStripe\UserForms\Model\EditableFormField;
use WeDevelop\ElementalWidget\UserForm\Field\FieldModel\Address;

class EditableAddressField extends EditableFormField
{
    /** @config */
    private static string $singular_name = 'Address Field';

    /** @config */
    private static string $plural_name = 'Address Fields';

    /** @config */
    private static bool $has_placeholder = false;

    /** @config */
    private static string $table_name = 'EditableAddressField';

    /** @config */
    private static bool $literal = true;

    public function getSetsOwnError(): bool
    {
        return true;
    }

    public function getCMSFields(): FieldList
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('Default');
        $fields->removeByName('FieldWidth');
        $fields->removeByName('RightTitle');
        $fields->removeByName('Required');

        return $fields;
    }

    public function getFormField(): FormField|CompositeField
    {
        $field = CompositeField::create([
            $zipcodeField = DutchZipcodeField::create($this->Name . '_zipcode', 'Postcode')
                ->addExtraClass('is-half address-is-zipcode')
                ->setAttribute('placeholder', '1234AB')
                ->setAttribute('minlength', 6)
                ->setAttribute('maxlength', 6),
            $numberField = NumericField::create($this->Name . '_number', 'Huisnummer')
                ->addExtraClass('is-fourth address-is-housenumber')
                ->setAttribute('placeholder', '10'),
            $numberAdditionField = TextField::create($this->Name . '_number_addition', 'Toevoeging')
                ->addExtraClass('is-fourth address-is-extension')
                ->setAttribute('placeholder', 'A'),
            $streetField = TextField::create($this->Name . '_street', 'Straat')
                ->addExtraClass('is-half address-is-street')
                ->setAttribute('readonly', 'true'),
            $cityField = TextField::create($this->Name . '_city', 'Plaats')
                ->addExtraClass('is-half address-is-city')
                ->setAttribute('readonly', 'true'),
        ])
            ->setName($this->Name)
            ->addExtraClass('address-autocomplete-wrapper')
            ->setFieldHolderTemplate(__CLASS__ . '_holder');

        $this->doUpdateFormField($field);

        $errorMessage = $this->getErrorMessage()->HTML();

        foreach ([$zipcodeField, $numberField, $streetField, $cityField] as $f) {
            $f->addExtraClass('requiredField');
            $f->setAttribute('required', 'required');
            $f->setAttribute('data-rule-required', 'true');
            $f->setAttribute('data-msg-required', $errorMessage);
        }

        return $field;
    }

    public function existsInData(array $data): bool
    {
        return isset($data[$this->Name . '_zipcode'], $data[$this->Name . '_number'], $data[$this->Name . '_street'], $data[$this->Name . '_city']);
    }

    public function getAddressObject(array $data): ?Address
    {
        if (!$this->existsInData($data)) {
            return null;
        }

        $zipcode = $data[$this->Name . '_zipcode'];
        $number = $data[$this->Name . '_number'];
        $numberAddition = array_key_exists($this->Name . '_number_addition', $data) ? $data[$this->Name . '_number_addition'] : '';
        $street = $data[$this->Name . '_street'];
        $city = $data[$this->Name . '_city'];

        return new Address($zipcode, $number, $numberAddition, $street, $city);
    }

    public function getValueFromData($data): ?string
    {
        if (!is_array($data)) {
            return null;
        }

        $address = $this->getAddressObject($data);

        if (is_null($address)) {
            return null;
        }

        return "{$address->getStreet()} {$address->getHouseNumber()} {$address->getHouseNumberAddition()}, {$address->getZipCode()} {$address->getCity()}";
    }
}
