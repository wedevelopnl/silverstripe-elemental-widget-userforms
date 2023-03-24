<?php

declare(strict_types=1);

namespace WeDevelop\ElementalWidget\UserForm\Field\FieldModel;

final class Address
{
    public function __construct(
        private readonly string $zipCode,
        private readonly string $houseNumber,
        private readonly ?string $houseNumberAddition,
        private readonly string $street,
        private readonly string $city
    ) {
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
