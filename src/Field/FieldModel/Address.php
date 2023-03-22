<?php

declare(strict_types=1);

namespace WeDevelop\ElementalWidget\UserForm\Field\FieldModel;

final class Address
{
    private string $zipCode;
    private string $houseNumber;
    private ?string $houseNumberAddition;
    private string $street;
    private string $city;

    public function __construct(
        string $zipCode,
        string $houseNumber,
        ?string $houseNumberAddition,
        string $street,
        string $city
    ) {
        $this->zipCode = $zipCode;
        $this->houseNumber = $houseNumber;
        $this->houseNumberAddition = $houseNumberAddition;
        $this->street = $street;
        $this->city = $city;
    }

    public function getZipCode(): string
    {
        return $this->zipCode;
    }

    public function getHouseNumber(): string
    {
        return $this->houseNumber;
    }

    public function getHouseNumberAddition(): ?string
    {
        return $this->houseNumberAddition;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function toArray(): array
    {
        return [
            'zipCode' => $this->zipCode,
            'houseNumber' => $this->houseNumber,
            'houseNumberAddition' => $this->houseNumberAddition,
            'street' => $this->street,
            'city' => $this->city,
        ];
    }
}
