<?php

declare(strict_types=1);

namespace WeDevelop\ElementalWidget\UserForm\Fields;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBVarchar;
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

    /**
     * @config
     * @var array<string, string>
     */
    private static array $db = [
        'ShowZipcode' => 'Boolean',
        'ShowHouseNumber' => 'Boolean',
        'ShowHouseNumberAddition' => 'Boolean',
        'ShowStreet' => 'Boolean',
        'ShowCity' => 'Boolean',
    ];

    /**
     * @config
     * @var array<string, mixed>
     */
    private static array $defaults = [
        'ShowZipcode' => true,
        'ShowHouseNumber' => true,
        'ShowHouseNumberAddition' => true,
        'ShowStreet' => true,
        'ShowCity' => true,
    ];

    /** @config */
    private static bool $literal = true;

    public function getSetsOwnError(): bool
    {
        return true;
    }

    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function ($fields) {
            $fields->addFieldsToTab(
                'Root.Main',
                [
                    CheckboxField::create(
                        'ShowZipcode',
                        _t(__CLASS__ . 'SHOW_ZIPCODE', 'Show zipcode field'),
                    ),
                    CheckboxField::create(
                        'ShowHouseNumber',
                        _t(__CLASS__ . 'SHOW_HOUSENUMBER', 'Show housenumber field'),
                    ),
                    CheckboxField::create(
                        'ShowHouseNumberAddition',
                        _t(__CLASS__ . 'SHOW_HOUSENUMBER_ADDITION', 'Show housenumber addition field'),
                    ),
                    CheckboxField::create(
                        'ShowStreet',
                        _t(__CLASS__ . 'SHOW_STREET', 'Show street field'),
                    ),
                    CheckboxField::create(
                        'ShowCity',
                        _t(__CLASS__ . 'SHOW_CITY', 'Show city field'),
                    ),
                ]
            );

            $fields->removeByName('Default');
            $fields->removeByName('FieldWidth');
            $fields->removeByName('RightTitle');
            $fields->removeByName('Required');
        });

        return parent::getCMSFields();
    }

    public function getFormField(): FormField|CompositeField
    {
        $addressFields = [
            'Zipcode' => DutchZipcodeField::create($this->Name . '_zipcode', 'Postcode')
                ->addExtraClass('is-half address-is-zipcode')
                ->setAttribute('placeholder', '1234AB')
                ->setAttribute('minlength', 6)
                ->setAttribute('maxlength', 7),
            'HouseNumber' => NumericField::create($this->Name . '_number', 'Huisnummer')
                ->addExtraClass('is-fourth address-is-housenumber')
                ->setAttribute('placeholder', '10'),
            'HouseNumberAddition' => TextField::create($this->Name . '_number_addition', 'Toevoeging')
                ->addExtraClass('is-fourth address-is-extension')
                ->setAttribute('placeholder', 'A'),
            'Street' => TextField::create($this->Name . '_street', 'Straat')
                ->addExtraClass('is-half address-is-street'),
            'City' => TextField::create($this->Name . '_city', 'Plaats')
                ->addExtraClass('is-half address-is-city'),
        ];

        $composite = CompositeField::create()
            ->setName($this->Name)
            ->addExtraClass('address-autocomplete-wrapper')
            ->setFieldHolderTemplate(__CLASS__ . '_holder');

        foreach ($addressFields as $key => $field) {
            if (!$this->getField("Show{$key}")) {
                continue;
            }

            if ($key !== 'HouseNumberAddition') {
                $field->addExtraClass('requiredField');
                $field->setAttribute('required', 'required');
                $field->setAttribute('data-rule-required', 'true');
                $field->setAttribute('data-msg-required', $this->getErrorMessage($field)->HTML());
            }

            $composite->push($field);
        }

        $this->doUpdateFormField($composite);

        return $composite;
    }

    public function getAddressObject(array $data): Address
    {
        $zipcode = isset($data[$this->Name . '_zipcode']) ? $data[$this->Name . '_zipcode'] : null;
        $number = isset($data[$this->Name . '_number']) ? (int)$data[$this->Name . '_number'] : null;
        $numberAddition = isset($data[$this->Name . '_number_addition']) ? $data[$this->Name . '_number_addition'] : null;
        $street = isset($data[$this->Name . '_street']) ? $data[$this->Name . '_street'] : null;
        $city = isset($data[$this->Name . '_city']) ? $data[$this->Name . '_city'] : null;

        return new Address($zipcode, $number, $numberAddition, $street, $city);
    }

    public function getValueFromData($data): ?string
    {
        if (!is_array($data)) {
            return null;
        }

        $address = $this->getAddressObject($data);

        return "{$address->getStreet()} {$address->getHouseNumber()} {$address->getHouseNumberAddition()}, {$address->getZipCode()} {$address->getCity()}";
    }

    public function getErrorMessage(?FormField $field = null): DBVarchar
    {
        if (empty($field)) {
            return parent::getErrorMessage();
        }

        $title = strip_tags("'". ($field->title ? $field->title : $this->Name) . "'");
        $standard = _t(parent::class . '.FIELDISREQUIRED', '{name} is required', ['name' => $title]);

        // only use CustomErrorMessage if it has a non empty value
        $errorMessage = (!empty($this->CustomErrorMessage)) ? $this->CustomErrorMessage : $standard;

        return DBField::create_field('Varchar', $errorMessage);
    }
}
